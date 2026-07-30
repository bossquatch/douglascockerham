<?php

use App\Services\GabbyBriefingService;
use App\Services\GabbyMapService;
use App\Services\GabbySourceLinkService;

beforeEach(function () {
    config()->set(
        'gabby.handoff.state_path',
        storage_path('framework/testing/missing-gabby-dashboard-state-'.bin2hex(random_bytes(6)).'.json'),
    );
});

function gabbySnapshotWithDukeAggregate(): array
{
    $snapshot = config('gabby.snapshot');
    $snapshot['generated_at'] = '2026-07-30T13:25:00-04:00';
    $snapshot['status']['timestamp'] = 'July 30, 2026 at 1:25 PM EDT';
    $snapshot['utilities'] = [
        'label' => 'Provider-reported aggregate',
        'items' => [
            [
                'kind' => 'county_outage_aggregate',
                'provider' => 'Duke Energy Florida',
                'scope' => 'Polk County',
                'customers_without_power' => 1161,
                'customers_served' => 152464,
                'percent_without_power' => 0.76,
                'updated_at' => '2026-07-30T17:21:48.305Z',
                'published_etr' => '2026-07-30 17:15:00',
                'source' => 'Official provider aggregate · Duke Energy Florida',
                'source_url' => 'https://www.duke-energy.com/outagemaps',
            ],
        ],
    ];

    return $snapshot;
}

test('the gabby dashboard is public and shows its snapshot boundaries', function () {
    $response = $this->get('/gabby');

    $response
        ->assertOk()
        ->assertSeeText('Gabby')
        ->assertSeeText('Polk County Situational Awareness')
        ->assertSeeText('July 30, 2026 at 11:47 AM EDT')
        ->assertSee('datetime="2026-07-30T11:47:00-04:00"', false)
        ->assertSeeText('Flood Advisory for Polk and Hillsborough counties')
        ->assertSeeText('Valid through 1:30 PM EDT')
        ->assertSeeText('Avoid flooded roads')
        ->assertSeeText('SR 60 fully closed at the CSX crossing between Lake Wales and Bartow')
        ->assertSeeText('No Atlantic tropical-cyclone formation is expected during the next seven days')
        ->assertSeeText('354 source results')
        ->assertSeeText('15 public sources')
        ->assertSeeText('19 public sources')
        ->assertSeeText('Collection failures')
        ->assertSeeText('34')
        ->assertSeeText('enabled public sources')
        ->assertSeeText('Not live tracking')
        ->assertSeeText('Compact configured-snapshot map preview')
        ->assertSeeText('Unverified community signal')
        ->assertSeeText('not continuous monitoring')
        ->assertSeeText('Dark mode')
        ->assertSeeText('Light mode')
        ->assertSee('data-theme-toggle', false)
        ->assertSee('window.Flux.applyAppearance', false)
        ->assertSee('data-briefing-scroll', false)
        ->assertSee('tabindex="0"', false)
        ->assertSeeText('Use the arrow keys, Page Up, or Page Down')
        ->assertSee('href="https://www.weather.gov/tbw/"', false)
        ->assertSeeText('Florida 511 alerts')
        ->assertSeeText('Polk County Emergency Management')
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertSeeText('Always rely on official sources for important decisions.');
});

test('the compass beacon brand is accessible and consistent across public gabby pages', function () {
    foreach (['/gabby', '/gabby/briefing', '/gabby/map', '/gabby/elections'] as $route) {
        $this->get($route)
            ->assertOk()
            ->assertSee('href="/gabby-compass-beacon.svg"', false)
            ->assertSee('data-gabby-compass-beacon', false)
            ->assertSee('aria-label="Gabby Compass Beacon brand mark"', false)
            ->assertSeeText('Polk County Situational Awareness')
            ->assertSee('gabby-brand-lockup--sidebar', false)
            ->assertSee('gabby-brand-lockup--header', false);
    }

    $this->get('/gabby')
        ->assertSee('data-election-summary-icon', false)
        ->assertSee('summary-panel__icon--elections', false)
        ->assertSee('election-icon__ballot', false)
        ->assertSee('election-icon__check', false);

    $brandSvg = file_get_contents(public_path('gabby-compass-beacon.svg'));

    expect($brandSvg)->not->toBeFalse()
        ->and(simplexml_load_string($brandSvg))->not->toBeFalse()
        ->and($brandSvg)
        ->toContain('#0D1B2A')
        ->toContain('#1E88E5')
        ->toContain('#009AA6')
        ->toContain('#E6F2FF')
        ->not->toContain('linearGradient');
});

test('the gabby dashboard includes privacy safe community reporting', function () {
    $response = $this->get('/gabby');

    $response
        ->assertOk()
        ->assertSeeText('No raw posts, identities, handles, or personal data are displayed')
        ->assertSeeText('No new official X Polk operational emergency')
        ->assertSeeText('Bluesky had no current eligible item')
        ->assertSeeText('No recurring general or election misinformation pattern found.');
});

test('a verified county outage aggregate renders safely across overview briefing and map', function () {
    config()->set('gabby.snapshot', gabbySnapshotWithDukeAggregate());

    foreach (['/gabby', '/gabby/briefing', '/gabby/map'] as $route) {
        $this->get($route)
            ->assertOk()
            ->assertSeeText('Duke Energy Florida reports 1,161 customers without power in Polk County')
            ->assertSeeText('1,161 of 152,464 served customers without power (0.76%)')
            ->assertSeeText('Provider-published ETR: July 30, 2026 at 17:15')
            ->assertSeeText('timezone not supplied')
            ->assertSeeText('Confirm current status and restoration estimates with Duke Energy Florida before acting')
            ->assertSee('href="https://www.duke-energy.com/outagemaps"', false)
            ->assertDontSee('outage_id', false)
            ->assertDontSee('service_address', false)
            ->assertDontSee('latitude', false)
            ->assertDontSee('longitude', false);
    }

    $this->get('/gabby')
        ->assertSeeText('Compact configured-snapshot map preview')
        ->assertSee('data-overview-leaflet-map', false)
        ->assertSeeText('View full map')
        ->assertSee('href="'.route('gabby.map').'"', false)
        ->assertSeeText('County aggregate only · no outage locations plotted');

    $this->get('/gabby/briefing')
        ->assertSeeText('High operational utility')
        ->assertSeeText('Provider-reported aggregate')
        ->assertSee('data-briefing-category="utilities"', false);

    $this->get('/gabby/map')
        ->assertSee('data-map-item-control="power-outage-aggregate"', false)
        ->assertSee('&quot;type&quot;:&quot;county_aggregate&quot;', false)
        ->assertSeeText('Provider outage aggregate')
        ->assertSeeText('County total · locations not plotted');
});

test('the public gabby briefing presents ordered operational items and filter controls', function () {
    $response = $this->get('/gabby/briefing');

    $response
        ->assertOk()
        ->assertSeeText('Polk County Operational Briefing')
        ->assertSeeText('Operational briefing')
        ->assertSeeText('Active verified priorities appear first')
        ->assertSeeInOrder([
            'Active priority',
            'SR 60 fully closed',
            'Resolved',
            'No active tropical cyclone',
            'Unverified community signal',
        ])
        ->assertSeeText('Verified')
        ->assertSeeText('Official record')
        ->assertSeeText('Reported coverage')
        ->assertSeeText('All categories')
        ->assertSeeText('Weather')
        ->assertSeeText('Roads')
        ->assertSeeText('Utilities')
        ->assertSeeText('Elections')
        ->assertSeeText('Community')
        ->assertSee('data-briefing-status-filter="active"', false)
        ->assertSee('data-briefing-category-filter', false)
        ->assertSee('data-briefing-item', false)
        ->assertSee('aria-pressed="true"', false)
        ->assertSee('href="'.route('gabby.briefing').'"', false)
        ->assertSee('aria-current="page"', false)
        ->assertSee('href="https://www.weather.gov/tbw/"', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertSeeText('Important actions require confirmation')
        ->assertDontSee('unapproved.example', false);
});

test('the briefing presenter derives stable statuses and categories from a snapshot', function () {
    $snapshot = app(GabbySourceLinkService::class)->present(config('gabby.snapshot'));
    $briefing = app(GabbyBriefingService::class)->fromSnapshot($snapshot);

    expect(collect($briefing['items'])->pluck('status')->all())
        ->toBe(['active', 'active', 'resolved', 'informational', 'informational', 'informational'])
        ->and(collect($briefing['items'])->pluck('category')->all())
        ->toBe(['weather', 'roads', 'utilities', 'weather', 'elections', 'community'])
        ->and(collect($briefing['items'])->where('category', 'community')->first()['_source_url'])
        ->toBeNull();
});

test('the public gabby map presents a safe geographic locator with working control hooks', function () {
    $response = $this->get('/gabby/map');

    $response
        ->assertOk()
        ->assertSeeText('Polk County Operational Map')
        ->assertSeeText('Configured snapshot map — not live tracking')
        ->assertSeeText('not official GIS warning polygons')
        ->assertSeeText('Polk County advisory area')
        ->assertSeeText('SR 60 corridor')
        ->assertSeeText('Winter Haven service area')
        ->assertSeeText('Active road closure')
        ->assertSeeText('Resolved utility notice')
        ->assertSeeText('Unverified community signal · Not plotted')
        ->assertSeeText('No social account, post, person, or location is plotted')
        ->assertSeeText('Accessible list alternative')
        ->assertSeeText('Polk County boundary')
        ->assertSeeText('Census GEO_ID 0500000US12105')
        ->assertSeeText('the supplied file contains no source URL')
        ->assertSeeText('OpenStreetMap data and attribution')
        ->assertSeeText('not the production traffic-map plan')
        ->assertSee('data-map-status-filter="active"', false)
        ->assertSee('data-map-category-filter', false)
        ->assertSee('data-leaflet-map', false)
        ->assertSee('data-tile-url="https://tile.openstreetmap.org/{z}/{x}/{y}.png"', false)
        ->assertSee('data-map-record="weather-advisory"', false)
        ->assertSee('data-map-record="sr60-closure"', false)
        ->assertSee('data-map-record="winter-haven-resolved"', false)
        ->assertSee('&quot;type&quot;:&quot;generalized_bounds&quot;', false)
        ->assertSee('&quot;type&quot;:&quot;approximate_corridor&quot;', false)
        ->assertSee('&quot;type&quot;:&quot;city_point&quot;', false)
        ->assertSee('aria-pressed="true"', false)
        ->assertSee('href="'.route('gabby.map').'"', false)
        ->assertSee('aria-current="page"', false)
        ->assertSee('href="https://www.weather.gov/tbw/"', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertDontSee('1800–1830', false)
        ->assertDontSee('unapproved.example', false);
});

test('critical facilities are gated and provide a safe local review layer on both maps', function () {
    config()->set('gabby.critical_facilities.enabled', false);

    foreach (['/gabby', '/gabby/map'] as $route) {
        $this->get($route)
            ->assertOk()
            ->assertDontSee('data-facility-data', false)
            ->assertDontSee('data-overview-facility-data', false)
            ->assertDontSeeText('Local review layer');
    }

    config()->set('gabby.critical_facilities.enabled', true);

    $this->get('/gabby')
        ->assertOk()
        ->assertSeeText('2,339 supplied critical-facility points')
        ->assertSeeText('Reference locations only')
        ->assertSee('data-overview-facility-data', false)
        ->assertDontSee('PARCELID', false)
        ->assertDontSee('FIRST_FLOOR_HEIGHT', false);

    $this->get('/gabby/map')
        ->assertOk()
        ->assertSeeText('Polk County critical facilities')
        ->assertSeeText('2,339 supplied point locations')
        ->assertSeeText('Critical-facility list alternative')
        ->assertSeeText('All facility categories')
        ->assertSeeText('Emergency response (149)')
        ->assertSeeText('Transportation and communications (606)')
        ->assertSeeText('No addresses, contacts, notes, identifiers, or hidden KMZ fields are included')
        ->assertSee('data-facility-layer-toggle', false)
        ->assertSee('data-facility-category-filter', false)
        ->assertSee('data-facility-search', false)
        ->assertSee('data-facility-data', false)
        ->assertDontSee('PARCELID', false)
        ->assertDontSee('FIRST_FLOOR_HEIGHT', false);
});

test('the map presenter derives only supported public scale records from the snapshot', function () {
    $snapshot = app(GabbySourceLinkService::class)->present(config('gabby.snapshot'));
    $map = app(GabbyMapService::class)->fromSnapshot($snapshot);

    expect(collect($map['records'])->pluck('id')->all())
        ->toBe(['weather-advisory', 'sr60-closure', 'winter-haven-resolved'])
        ->and(collect($map['records'])->pluck('status')->all())
        ->toBe(['active', 'active', 'resolved'])
        ->and(collect($map['records'])->pluck('category')->all())
        ->toBe(['weather', 'roads', 'utilities'])
        ->and(collect($map['records'])->pluck('geometry.type')->all())
        ->toBe(['generalized_bounds', 'approximate_corridor', 'city_point'])
        ->and($map['community']['plotted'])
        ->toBeFalse()
        ->and(collect($map['records'])->pluck('map_summary')->implode(' '))
        ->not->toContain('1800–1830');
});
