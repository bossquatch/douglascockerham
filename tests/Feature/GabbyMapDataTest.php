<?php

use App\Services\GabbyCriticalFacilitiesService;

function gabbyMapData(string $path): array
{
    $contents = file_get_contents(resource_path($path));

    expect($contents)->not->toBeFalse();

    return json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
}

function gabbyGeometryRings(array $geometry): array
{
    return match ($geometry['type']) {
        'Polygon' => $geometry['coordinates'],
        'MultiPolygon' => array_merge(...$geometry['coordinates']),
        default => [],
    };
}

test('the loaded Polk boundary is a closed county polygon with expected identity', function () {
    $geojson = gabbyMapData('data/gabby/polk_county.json');
    $feature = $geojson['features'][0];
    $rings = gabbyGeometryRings($feature['geometry']);
    $coordinates = collect($rings)->flatten(1);

    expect($geojson['type'])
        ->toBe('FeatureCollection')
        ->and($geojson['features'])->toHaveCount(1)
        ->and($feature['geometry']['type'])->toBeIn(['Polygon', 'MultiPolygon'])
        ->and($feature['properties'])
        ->toMatchArray([
            'GEO_ID' => '0500000US12105',
            'STATE' => '12',
            'COUNTY' => '105',
            'NAME' => 'Polk',
        ])
        ->and($rings)->not->toBeEmpty()
        ->and(collect($rings)->every(fn (array $ring): bool => $ring[0] === $ring[array_key_last($ring)]))
        ->toBeTrue()
        ->and($coordinates->every(
            fn (array $position): bool => count($position) >= 2
                && is_numeric($position[0])
                && is_numeric($position[1])
                && $position[0] >= -180
                && $position[0] <= 180
                && $position[1] >= -90
                && $position[1] <= 90,
        ))
        ->toBeTrue();
});

test('the dormant county reference is valid and includes all 67 Florida counties', function () {
    $geojson = gabbyMapData('data/gabby/reference/counties.json');
    $features = collect($geojson['features']);
    $florida = $features->where('properties.STATE', '12');

    expect($geojson['type'])
        ->toBe('FeatureCollection')
        ->and($features)->toHaveCount(3221)
        ->and($features->every(
            fn (array $feature): bool => in_array(
                $feature['geometry']['type'] ?? null,
                ['Polygon', 'MultiPolygon'],
                true,
            ),
        ))
        ->toBeTrue()
        ->and($features->every(
            fn (array $feature): bool => collect(gabbyGeometryRings($feature['geometry']))
                ->every(fn (array $ring): bool => $ring[0] === $ring[array_key_last($ring)]),
        ))
        ->toBeTrue()
        ->and($florida)->toHaveCount(67)
        ->and($florida->where('properties.COUNTY', '105')->pluck('properties.NAME')->all())
        ->toBe(['Polk']);
});

test('the minimized critical facilities asset contains only approved map fields', function () {
    config()->set('gabby.critical_facilities.enabled', true);

    $facilities = app(GabbyCriticalFacilitiesService::class)->current();
    $features = collect($facilities['features']);

    expect($facilities['enabled'])->toBeTrue()
        ->and($facilities['count'])->toBe(2339)
        ->and($facilities['categories'])->toHaveCount(9)
        ->and(collect($facilities['categories'])->pluck('count')->sum())->toBe(2339)
        ->and($features)->toHaveCount(2339)
        ->and($features->every(
            fn (array $feature): bool => array_keys($feature) === ['type', 'id', 'properties', 'geometry']
                && array_keys($feature['properties']) === ['id', 'label', 'category', 'type']
                && array_keys($feature['geometry']) === ['type', 'coordinates']
                && $feature['geometry']['type'] === 'Point'
                && count($feature['geometry']['coordinates']) === 2,
        ))->toBeTrue()
        ->and(json_encode($facilities, JSON_THROW_ON_ERROR))
        ->not->toContain('Address')
        ->not->toContain('DESCRIPTION')
        ->not->toContain('PARCELID')
        ->not->toContain('CONTACT')
        ->not->toContain('FIRST_FLOOR_HEIGHT');
});

test('the critical facilities service returns no payload when the deployment gate is off', function () {
    config()->set('gabby.critical_facilities.enabled', false);

    expect(app(GabbyCriticalFacilitiesService::class)->current())
        ->toMatchArray([
            'enabled' => false,
            'count' => 0,
            'categories' => [],
            'features' => [],
        ]);
});
