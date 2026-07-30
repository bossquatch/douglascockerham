<?php

namespace App\Services;

use Illuminate\Support\Str;

class GabbyBriefingService
{
    public function __construct(
        private GabbyUtilityStatusService $utilities,
    ) {}

    /**
     * Build the public, filterable operational briefing from the same normalized
     * snapshot used by the Overview. Presentation-only fields remain private to
     * the Laravel view layer and never alter the accepted handoff artifact.
     *
     * @param  array<string, mixed>  $snapshot
     * @return array{items: array<int, array<string, mixed>>, categories: array<string, string>}
     */
    public function fromSnapshot(array $snapshot): array
    {
        $priority = $this->priorityItem($snapshot['priority'], $snapshot['status']['timestamp']);
        $items = [
            $priority,
            ...$this->utilities->fromSnapshot($snapshot),
        ];

        foreach ($snapshot['briefing'] as $item) {
            $presented = $this->briefingItem($item, $snapshot['status']['timestamp']);

            if (! $this->duplicatesPriority($presented, $priority)) {
                $items[] = $presented;
            }
        }

        $items[] = $this->communityItem($snapshot['community'], $snapshot['status']['timestamp']);

        $order = ['active' => 0, 'resolved' => 1, 'informational' => 2];

        usort(
            $items,
            fn (array $left, array $right): int => $order[$left['status']] <=> $order[$right['status']],
        );

        $categories = [
            'all' => 'All categories',
            'weather' => 'Weather',
            'roads' => 'Roads',
            'utilities' => 'Utilities',
            'elections' => 'Elections',
            'community' => 'Community',
        ];

        return compact('items', 'categories');
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function priorityItem(array $item, string $snapshotTime): array
    {
        return [
            ...$item,
            'status' => 'active',
            'status_label' => 'Active priority',
            'category' => $this->category($item),
            'confidence_labels' => ['Verified', 'Official record'],
            'time_context' => $this->priorityTime($item),
            'snapshot_context' => "Included in snapshot {$snapshotTime}",
            'tone' => 'urgent',
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function briefingItem(array $item, string $snapshotTime): array
    {
        $status = match ($item['tone'] ?? 'official') {
            'urgent' => 'active',
            'resolved' => 'resolved',
            default => 'informational',
        };

        $confidence = match ($status) {
            'active' => ['Verified', 'Official record'],
            'resolved' => ['Official record'],
            default => ['Official record'],
        };

        return [
            ...$item,
            'status' => $status,
            'status_label' => match ($status) {
                'active' => 'Active',
                'resolved' => 'Resolved',
                default => 'Informational',
            },
            'category' => $this->category($item),
            'confidence_labels' => $confidence,
            'time_context' => (string) ($item['time'] ?? 'Snapshot item'),
            'snapshot_context' => "Included in snapshot {$snapshotTime}",
        ];
    }

    /**
     * @param  array<string, mixed>  $community
     * @return array<string, mixed>
     */
    private function communityItem(array $community, string $snapshotTime): array
    {
        return [
            'status' => 'informational',
            'status_label' => 'Informational',
            'category' => 'community',
            'confidence_labels' => ['Unverified community signal', 'Reported coverage'],
            'time_context' => 'Bounded community review',
            'snapshot_context' => "Included in snapshot {$snapshotTime}",
            'tone' => 'community',
            'title' => $community['title'],
            'summary' => $community['summary'],
            '_summary_parts' => [
                ['type' => 'text', 'value' => $community['summary']],
            ],
            'source' => $community['coverage'],
            '_source_url' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function category(array $item): string
    {
        $text = Str::lower(implode(' ', [
            $item['title'] ?? '',
            $item['summary'] ?? '',
            $item['source'] ?? '',
        ]));

        return match (true) {
            Str::contains($text, ['election', 'voting', 'vote-by-mail', 'ballot']) => 'elections',
            Str::contains($text, ['boil water', 'utilities', 'utility', 'outage', 'without power', 'duke energy', 'electric']) => 'utilities',
            Str::contains($text, ['flood', 'weather', 'rain', 'tropical', 'cyclone', 'nws', 'nhc']) => 'weather',
            Str::contains($text, ['sr 60', 'road', 'traffic', 'detour', 'fl511', 'crossing', 'closure']) => 'roads',
            Str::contains($text, 'hurricane') => 'weather',
            default => 'community',
        };
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function priorityTime(array $item): string
    {
        $parts = explode('·', (string) ($item['level'] ?? ''), 2);

        return trim($parts[1] ?? 'Current');
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $priority
     */
    private function duplicatesPriority(array $item, array $priority): bool
    {
        $itemTitle = Str::lower(trim((string) $item['title']));
        $priorityTitle = Str::lower(trim((string) $priority['title']));

        if ($itemTitle === $priorityTitle) {
            return true;
        }

        return $item['category'] === $priority['category']
            && Str::contains($itemTitle, 'flood advisory')
            && Str::contains($priorityTitle, 'flood advisory');
    }
}
