<?php

namespace App\Services;

class GabbySourceLinkService
{
    /**
     * Add presentation-only, validated link metadata to a public snapshot.
     *
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    public function present(array $snapshot): array
    {
        $snapshot['priority'] = $this->presentItem($snapshot['priority']);
        $snapshot['briefing'] = array_map(
            fn (array $item): array => $this->presentItem($item),
            $snapshot['briefing'],
        );

        if (isset($snapshot['utilities']['items']) && is_array($snapshot['utilities']['items'])) {
            $snapshot['utilities']['items'] = array_map(
                fn (array $item): array => $this->presentItem($item),
                $snapshot['utilities']['items'],
            );
        }

        $snapshot['_official_links'] = $this->officialLinks();

        return $snapshot;
    }

    public function safeUrl(mixed $url): ?string
    {
        if (! is_string($url) || strlen($url) > 2048 || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($url);

        if (! is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || (isset($parts['port']) && $parts['port'] !== 443)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $allowedHosts = config('gabby.links.allowed_hosts', []);

        if (! is_array($allowedHosts) || ! in_array($host, $allowedHosts, true)) {
            return null;
        }

        return $url;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function presentItem(array $item): array
    {
        $item['_source_url'] = $this->sourceUrl($item);
        $item['_summary_parts'] = $this->summaryParts((string) ($item['summary'] ?? ''));

        return $item;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function sourceUrl(array $item): ?string
    {
        $explicit = $this->safeUrl($item['source_url'] ?? null);

        if ($explicit !== null) {
            return $explicit;
        }

        $sources = config('gabby.links.sources', []);
        $mapped = is_array($sources) ? ($sources[$item['source'] ?? ''] ?? null) : null;

        return $this->safeUrl($mapped);
    }

    /**
     * @return array<int, array{type: 'link'|'text', label?: string, url?: string, value?: string}>
     */
    private function summaryParts(string $summary): array
    {
        $tokens = [];
        $pattern = '~https://[^\s<>"\']+~iu';
        $offset = 0;

        preg_match_all($pattern, $summary, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as [$matchedUrl, $position]) {
            $url = rtrim($matchedUrl, '.,;:)');
            $trailing = substr($matchedUrl, strlen($url));

            if ($position > $offset) {
                $tokens[] = [
                    'type' => 'text',
                    'value' => substr($summary, $offset, $position - $offset),
                ];
            }

            $safeUrl = $this->safeUrl($url);

            if ($safeUrl !== null) {
                $tokens[] = [
                    'type' => 'link',
                    'label' => $this->urlLabel($safeUrl),
                    'url' => $safeUrl,
                ];
            }

            if ($trailing !== '') {
                $tokens[] = [
                    'type' => 'text',
                    'value' => $trailing,
                ];
            }

            $offset = $position + strlen($matchedUrl);
        }

        if ($offset < strlen($summary)) {
            $tokens[] = [
                'type' => 'text',
                'value' => substr($summary, $offset),
            ];
        }

        if ($tokens === []) {
            $tokens[] = [
                'type' => 'text',
                'value' => $summary,
            ];
        }

        return $this->linkApprovedBareDomains($tokens);
    }

    /**
     * @param  array<int, array{type: 'link'|'text', label?: string, url?: string, value?: string}>  $tokens
     * @return array<int, array{type: 'link'|'text', label?: string, url?: string, value?: string}>
     */
    private function linkApprovedBareDomains(array $tokens): array
    {
        $bareDomains = config('gabby.links.bare_domains', []);

        if (! is_array($bareDomains)) {
            return $tokens;
        }

        foreach ($bareDomains as $domain => $link) {
            if (! is_array($link) || ($safeUrl = $this->safeUrl($link['url'] ?? null)) === null) {
                continue;
            }

            $next = [];

            foreach ($tokens as $token) {
                if ($token['type'] !== 'text' || ! isset($token['value'])) {
                    $next[] = $token;

                    continue;
                }

                $parts = preg_split(
                    '/('.preg_quote((string) $domain, '/').')/i',
                    $token['value'],
                    -1,
                    PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY,
                );

                foreach ($parts ?: [] as $part) {
                    $next[] = strcasecmp($part, (string) $domain) === 0
                        ? [
                            'type' => 'link',
                            'label' => (string) ($link['label'] ?? $domain),
                            'url' => $safeUrl,
                        ]
                        : [
                            'type' => 'text',
                            'value' => $part,
                        ];
                }
            }

            $tokens = $next;
        }

        return $tokens;
    }

    private function urlLabel(string $url): string
    {
        $labels = config('gabby.links.url_labels', []);

        if (is_array($labels)) {
            foreach ($labels as $prefix => $label) {
                if (str_starts_with($url, (string) $prefix) && is_string($label)) {
                    return $label;
                }
            }
        }

        return 'Open official source';
    }

    /**
     * @return array<string, array{label: string, url: string}>
     */
    private function officialLinks(): array
    {
        $configured = config('gabby.links.official', []);
        $links = [];

        if (! is_array($configured)) {
            return $links;
        }

        foreach ($configured as $key => $link) {
            if (! is_array($link)
                || ! is_string($link['label'] ?? null)
                || ($safeUrl = $this->safeUrl($link['url'] ?? null)) === null) {
                continue;
            }

            $links[$key] = [
                'label' => $link['label'],
                'url' => $safeUrl,
            ];
        }

        return $links;
    }
}
