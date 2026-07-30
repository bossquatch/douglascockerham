<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class GabbyUtilityStatusService
{
    /**
     * Present validated provider aggregates without exposing provider internals
     * or inventing outage locations.
     *
     * @param  array<string, mixed>  $snapshot
     * @return array<int, array<string, mixed>>
     */
    public function fromSnapshot(array $snapshot): array
    {
        $items = $snapshot['utilities']['items'] ?? [];

        if (! is_array($items)) {
            return [];
        }

        return collect($items)
            ->filter(
                fn (mixed $item): bool => is_array($item)
                    && ($item['kind'] ?? null) === 'county_outage_aggregate',
            )
            ->map(fn (array $item): array => $this->present($item, $snapshot['status']['timestamp']))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function present(array $item, string $snapshotTime): array
    {
        $withoutPower = number_format((int) $item['customers_without_power']);
        $served = number_format((int) $item['customers_served']);
        $percentage = number_format((float) $item['percent_without_power'], 2);
        $updatedAt = CarbonImmutable::parse($item['updated_at'])
            ->setTimezone((string) config('gabby.handoff.timezone', 'America/New_York'))
            ->format('F j, Y \a\t g:i A T');
        $etr = $this->etr((string) $item['published_etr']);
        $title = "{$item['provider']} reports {$withoutPower} customers without power in {$item['scope']}";
        $summary = "Provider-reported county aggregate: {$withoutPower} of {$served} served customers without power ({$percentage}%). {$etr} No customer, address, outage-map internal, or inferred location is displayed. Confirm current status and restoration estimates with {$item['provider']} before acting.";

        return [
            ...$item,
            'status' => 'active',
            'status_label' => 'High operational utility',
            'category' => 'utilities',
            'confidence_labels' => ['Verified', 'Provider-reported aggregate'],
            'time_context' => "Provider updated {$updatedAt}",
            'snapshot_context' => "Included in snapshot {$snapshotTime}",
            'tone' => 'urgent',
            'label' => 'Provider-reported aggregate',
            'time' => "Updated {$updatedAt}",
            'title' => $title,
            'summary' => $summary,
            '_summary_parts' => [
                ['type' => 'text', 'value' => $summary],
            ],
        ];
    }

    private function etr(string $value): string
    {
        $etr = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $value, 'UTC');

        return 'Provider-published ETR: '.$etr->format('F j, Y \a\t H:i')
            .' (timezone not supplied in the provider aggregate).';
    }
}
