<?php

namespace App\Services;

use RuntimeException;

class GabbyCriticalFacilitiesService
{
    /**
     * Load the deliberately minimized local-review dataset only when the
     * explicit feature flag permits public presentation.
     *
     * @return array<string, mixed>
     */
    public function current(): array
    {
        if (! config('gabby.critical_facilities.enabled', false)) {
            return $this->disabled();
        }

        $path = config('gabby.critical_facilities.path');

        if (! is_string($path) || ! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('Gabby critical-facilities data is unavailable.');
        }

        $contents = file_get_contents($path);

        if ($contents === false || strlen($contents) > 2_500_000) {
            throw new RuntimeException('Gabby critical-facilities data failed size validation.');
        }

        $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        if (
            ! is_array($payload)
            || ($payload['schema'] ?? null) !== 'gabby.critical-facilities'
            || ($payload['version'] ?? null) !== 1
            || ($payload['type'] ?? null) !== 'FeatureCollection'
            || ! is_array($payload['features'] ?? null)
            || ! is_array($payload['metadata'] ?? null)
        ) {
            throw new RuntimeException('Gabby critical-facilities schema is invalid.');
        }

        $categories = config('gabby.critical_facilities.categories', []);
        $seen = [];
        $counts = array_fill_keys(array_keys($categories), 0);
        $features = [];

        foreach ($payload['features'] as $feature) {
            $normalized = $this->validateFeature($feature, $categories);
            $id = $normalized['properties']['id'];

            if (isset($seen[$id])) {
                throw new RuntimeException('Gabby critical-facilities IDs must be unique.');
            }

            $seen[$id] = true;
            $counts[$normalized['properties']['category']]++;
            $features[] = $normalized;
        }

        if (
            count($features) !== ($payload['metadata']['count'] ?? null)
            || array_filter($counts) !== ($payload['metadata']['category_counts'] ?? null)
        ) {
            throw new RuntimeException('Gabby critical-facilities totals do not reconcile.');
        }

        return [
            'enabled' => true,
            'count' => count($features),
            'categories' => collect($categories)
                ->filter(fn (array $category, string $key): bool => $counts[$key] > 0)
                ->map(fn (array $category, string $key): array => [
                    ...$category,
                    'count' => $counts[$key],
                ])
                ->all(),
            'features' => $features,
            'source' => [
                'label' => 'User-supplied Polk County critical-facilities KMZ',
                'coordinate_reference' => $payload['metadata']['coordinate_reference'] ?? null,
                'field_policy' => $payload['metadata']['field_policy'] ?? null,
            ],
        ];
    }

    /**
     * @param  array<string, array<string, string>>  $categories
     * @return array<string, mixed>
     */
    private function validateFeature(mixed $feature, array $categories): array
    {
        if (
            ! is_array($feature)
            || array_keys($feature) !== ['type', 'id', 'properties', 'geometry']
            || $feature['type'] !== 'Feature'
            || ! is_array($feature['properties'])
            || array_keys($feature['properties']) !== ['id', 'label', 'category', 'type']
            || ! is_array($feature['geometry'])
            || array_keys($feature['geometry']) !== ['type', 'coordinates']
            || $feature['geometry']['type'] !== 'Point'
        ) {
            throw new RuntimeException('A critical-facilities feature has an unsupported shape.');
        }

        $properties = $feature['properties'];
        $coordinates = $feature['geometry']['coordinates'];

        if (
            ! is_string($feature['id'])
            || $feature['id'] !== ($properties['id'] ?? null)
            || ! preg_match('/^facility-\d{4}$/', $feature['id'])
            || ! $this->safeText($properties['label'] ?? null, 120)
            || ! $this->safeText($properties['type'] ?? null, 80)
            || ! is_string($properties['category'] ?? null)
            || ! isset($categories[$properties['category']])
            || ! is_array($coordinates)
            || count($coordinates) !== 2
            || ! is_numeric($coordinates[0])
            || ! is_numeric($coordinates[1])
        ) {
            throw new RuntimeException('A critical-facilities feature failed public-field validation.');
        }

        $longitude = (float) $coordinates[0];
        $latitude = (float) $coordinates[1];

        if ($longitude < -82.2 || $longitude > -81.0 || $latitude < 27.5 || $latitude > 28.5) {
            throw new RuntimeException('A critical-facilities point is outside the Polk County review envelope.');
        }

        return $feature;
    }

    private function safeText(mixed $value, int $maxLength): bool
    {
        return is_string($value)
            && $value !== ''
            && mb_check_encoding($value, 'UTF-8')
            && mb_strlen($value, 'UTF-8') <= $maxLength
            && ! preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $value);
    }

    /**
     * @return array<string, mixed>
     */
    private function disabled(): array
    {
        return [
            'enabled' => false,
            'count' => 0,
            'categories' => [],
            'features' => [],
            'source' => null,
        ];
    }
}
