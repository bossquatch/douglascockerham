<?php

namespace App\Services;

use Illuminate\Support\Collection;

class GabbyMapService
{
    public function __construct(
        private GabbyBriefingService $briefings,
    ) {}

    /**
     * Build presentation-only map records from the normalized public snapshot.
     * Geometry is deliberately generalized to the public operational scale
     * described on each record; it is not authoritative GIS geometry.
     *
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    public function fromSnapshot(array $snapshot): array
    {
        $briefing = $this->briefings->fromSnapshot($snapshot);
        $items = collect($briefing['items']);
        $records = collect([
            $this->record(
                $items,
                'weather-advisory',
                'active',
                'weather',
                'Polk County advisory area',
                'County-area advisory shown without point precision',
                'The Polk County portion of the verified advisory is shown at county-area level. The locator does not reproduce an official warning polygon.',
                'weather',
                [
                    'type' => 'generalized_bounds',
                    'bounds' => [
                        [27.55, -82.08],
                        [28.26, -81.14],
                    ],
                    'focus' => [27.91, -81.65],
                ],
            ),
            $this->record(
                $items,
                'power-outage-aggregate',
                'active',
                'utilities',
                'Polk County provider aggregate',
                'County aggregate only · no outage locations plotted',
                null,
                'power',
                [
                    'type' => 'county_aggregate',
                ],
            ),
            $this->record(
                $items,
                'sr60-closure',
                'active',
                'roads',
                'SR 60 corridor',
                'Between Bartow and Lake Wales · approximate',
                'The verified closure is represented as an approximate SR 60 corridor between Bartow and Lake Wales. Confirm current detours before traveling.',
                'roads',
                [
                    'type' => 'approximate_corridor',
                    'path' => [
                        [27.8964, -81.8431],
                        [27.9014, -81.5859],
                    ],
                    'focus' => [27.899, -81.7145],
                ],
            ),
            $this->record(
                $items,
                'winter-haven-resolved',
                'resolved',
                'utilities',
                'Winter Haven service area',
                'City-level resolved notice · no individual locations plotted',
                'The precautionary boil-water notice was rescinded for the affected Winter Haven service area. The locator does not plot customer addresses.',
                'utilities',
                [
                    'type' => 'city_point',
                    'point' => [28.0222, -81.7329],
                ],
                'Resolved Winter Haven precautionary boil-water notice',
            ),
        ])->filter()->values()->all();

        return [
            'records' => $records,
            'categories' => [
                'all' => 'All categories',
                'weather' => 'Weather',
                'roads' => 'Roads',
                'utilities' => 'Utilities',
            ],
            'community' => [
                'label' => $snapshot['community']['label'],
                'summary' => 'Community awareness remains unverified and is shown in the legend only. No social account, post, person, or location is plotted.',
                'plotted' => false,
            ],
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return array<string, mixed>|null
     */
    private function record(
        Collection $items,
        string $id,
        string $status,
        string $category,
        string $scope,
        string $locationContext,
        ?string $mapSummary,
        string $visual,
        array $geometry,
        ?string $publicTitle = null,
    ): ?array {
        $item = $items->first(
            fn (array $candidate): bool => $candidate['status'] === $status
                && $candidate['category'] === $category,
        );

        if (! is_array($item)) {
            return null;
        }

        return [
            'id' => $id,
            'status' => $status,
            'status_label' => $status === 'active' ? 'Active' : 'Resolved',
            'category' => $category,
            'scope' => $scope,
            'location_context' => $locationContext,
            'map_summary' => $mapSummary ?? $item['summary'],
            'visual' => $visual,
            'geometry' => $geometry,
            'title' => $publicTitle ?? $item['title'],
            'source' => $item['source'],
            'source_url' => $item['_source_url'],
            'time_context' => $item['time_context'],
            'confidence_labels' => $item['confidence_labels'],
        ];
    }
}
