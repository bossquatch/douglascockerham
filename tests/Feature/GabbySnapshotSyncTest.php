<?php

use App\Services\GabbySourceLinkService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-07-30T12:00:00-04:00');

    $this->gabbyTestDirectory = storage_path('framework/testing/gabby-'.bin2hex(random_bytes(6)));

    File::ensureDirectoryExists($this->gabbyTestDirectory);

    config()->set('gabby.handoff.path', $this->gabbyTestDirectory.'/handoff.json');
    config()->set('gabby.handoff.state_path', $this->gabbyTestDirectory.'/state.json');
    config()->set('gabby.handoff.lock_path', $this->gabbyTestDirectory.'/sync.lock');
});

afterEach(function () {
    File::deleteDirectory($this->gabbyTestDirectory);
    CarbonImmutable::setTestNow();
});

function validGabbyHandoff(string $generatedAt = '2026-07-30T11:48:00-04:00'): array
{
    $generated = CarbonImmutable::parse($generatedAt);
    $snapshot = config('gabby.snapshot');
    $snapshot['generated_at'] = $generatedAt;
    $snapshot['status']['timestamp'] = $generated
        ->setTimezone(config('gabby.handoff.timezone'))
        ->format('F j, Y \a\t g:i A T');

    return [
        'schema' => config('gabby.handoff.schema'),
        'version' => config('gabby.handoff.version'),
        'data_mode' => 'live',
        'generated_at' => $generatedAt,
        'collection' => [
            'status' => 'succeeded',
            'complete' => true,
            'started_at' => $generated->subMinutes(4)->format(DATE_ATOM),
            'completed_at' => $generated->subMinute()->format(DATE_ATOM),
            'source_health' => [
                'enabled' => $snapshot['source_health']['enabled'],
                'processed' => $snapshot['source_health']['processed'],
                'failures' => $snapshot['source_health']['failures'],
                'with_current_items' => $snapshot['source_health']['with_current_items'],
                'without_current_items' => $snapshot['source_health']['without_current_items'],
            ],
        ],
        'privacy' => [
            'public_safe' => true,
            'contains_raw_posts' => false,
            'contains_handles' => false,
            'contains_pii' => false,
            'politically_neutral' => true,
            'contains_political_scoring' => false,
            'contains_endorsements' => false,
            'contains_donor_targeting' => false,
        ],
        'snapshot' => $snapshot,
    ];
}

function writeGabbyHandoff(string $path, array $artifact): void
{
    File::put(
        $path,
        json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    );
}

function validGabbyDukeUtility(): array
{
    return [
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
    ];
}

test('a valid current handoff is promoted and a repeat is idempotent', function () {
    writeGabbyHandoff(config('gabby.handoff.path'), validGabbyHandoff());

    $this->artisan('gabby:sync-snapshot')
        ->expectsOutput('Gabby snapshot updated (2026-07-30T11:48:00-04:00).')
        ->assertSuccessful();

    $statePath = config('gabby.handoff.state_path');
    $acceptedHash = hash_file('sha256', $statePath);

    $this->get('/gabby')
        ->assertOk()
        ->assertSee('datetime="2026-07-30T11:48:00-04:00"', false)
        ->assertSeeText('July 30, 2026 at 11:48 AM EDT')
        ->assertSeeText('Flood Advisory for Polk and Hillsborough counties')
        ->assertSeeText('Always rely on official sources for important decisions.');

    $this->artisan('gabby:sync-snapshot')
        ->expectsOutput('Gabby snapshot is already current (2026-07-30T11:48:00-04:00).')
        ->assertSuccessful();

    expect(hash_file('sha256', $statePath))->toBe($acceptedHash);
});

test('a failed collection fixture cannot replace the last known good snapshot', function () {
    writeGabbyHandoff(config('gabby.handoff.path'), validGabbyHandoff());
    $this->artisan('gabby:sync-snapshot')->assertSuccessful();

    $statePath = config('gabby.handoff.state_path');
    $acceptedHash = hash_file('sha256', $statePath);

    File::copy(
        base_path('tests/Fixtures/Gabby/failed-collection.json'),
        config('gabby.handoff.path'),
    );

    $this->artisan('gabby:sync-snapshot')
        ->expectsOutputToContain('collection.status has an invalid value')
        ->assertFailed();

    expect(hash_file('sha256', $statePath))->toBe($acceptedHash);

    $this->get('/gabby')
        ->assertOk()
        ->assertSeeText('July 30, 2026 at 11:48 AM EDT');
});

test('stale partial sample and structurally incomplete handoffs are rejected', function () {
    $handoffPath = config('gabby.handoff.path');

    $stale = validGabbyHandoff('2026-07-30T08:00:00-04:00');
    writeGabbyHandoff($handoffPath, $stale);
    $this->artisan('gabby:sync-snapshot')
        ->expectsOutputToContain('handoff is stale')
        ->assertFailed();

    $partial = validGabbyHandoff();
    $partial['collection']['complete'] = false;
    writeGabbyHandoff($handoffPath, $partial);
    $this->artisan('gabby:sync-snapshot')
        ->expectsOutputToContain('collection.complete has an invalid value')
        ->assertFailed();

    $sample = validGabbyHandoff();
    $sample['data_mode'] = 'sample';
    writeGabbyHandoff($handoffPath, $sample);
    $this->artisan('gabby:sync-snapshot')
        ->expectsOutputToContain('data_mode has an invalid value')
        ->assertFailed();

    $failedHealth = validGabbyHandoff();
    $failedHealth['collection']['source_health']['failures'] = 1;
    $failedHealth['snapshot']['source_health']['failures'] = 1;
    $failedHealth['snapshot']['source_health']['items'][3]['status'] = '1';
    writeGabbyHandoff($handoffPath, $failedHealth);
    $this->artisan('gabby:sync-snapshot')
        ->expectsOutputToContain('reports collection failures')
        ->assertFailed();

    $incomplete = validGabbyHandoff();
    unset($incomplete['snapshot']['priority']);
    writeGabbyHandoff($handoffPath, $incomplete);
    $this->artisan('gabby:sync-snapshot')
        ->expectsOutputToContain('snapshot.priority must be an object')
        ->assertFailed();

    expect(File::exists(config('gabby.handoff.state_path')))->toBeFalse();
});

test('privacy attestations and defensive content checks are enforced', function () {
    $handoffPath = config('gabby.handoff.path');
    $unsafe = validGabbyHandoff();
    $unsafe['privacy']['contains_handles'] = true;
    writeGabbyHandoff($handoffPath, $unsafe);

    $this->artisan('gabby:sync-snapshot')
        ->expectsOutputToContain('privacy.contains_handles has an invalid value')
        ->assertFailed();

    $unsafe = validGabbyHandoff();
    $unsafe['snapshot']['community']['summary'] .= ' Contact @example_handle.';
    writeGabbyHandoff($handoffPath, $unsafe);

    $this->artisan('gabby:sync-snapshot')
        ->expectsOutputToContain('contains disallowed identifying data')
        ->assertFailed();

    $unsafe = validGabbyHandoff();
    $unsafe['snapshot']['priority']['source_url'] = 'https://unapproved.example/source';
    writeGabbyHandoff($handoffPath, $unsafe);

    $this->artisan('gabby:sync-snapshot')
        ->expectsOutputToContain('not an approved HTTPS source URL')
        ->assertFailed();
});

test('only approved official URLs are prepared for public display', function () {
    $snapshot = config('gabby.snapshot');
    $snapshot['priority']['summary'] = 'Guidance: https://www.weather.gov/safety/flood Unsafe: https://unapproved.example/post';

    $presented = app(GabbySourceLinkService::class)->present($snapshot);
    $parts = $presented['priority']['_summary_parts'];
    $renderedText = collect($parts)->pluck('value')->filter()->implode('');
    $links = collect($parts)->where('type', 'link')->pluck('url')->all();

    expect($links)
        ->toBe(['https://www.weather.gov/safety/flood'])
        ->and($renderedText)->not->toContain('unapproved.example');
});

test('an overlapping sync cannot replace dashboard state', function () {
    writeGabbyHandoff(config('gabby.handoff.path'), validGabbyHandoff());

    $lock = fopen(config('gabby.handoff.lock_path'), 'c');
    flock($lock, LOCK_EX);

    try {
        $this->artisan('gabby:sync-snapshot')
            ->expectsOutputToContain('Another Gabby snapshot sync is already running')
            ->assertFailed();

        expect(File::exists(config('gabby.handoff.state_path')))->toBeFalse();
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
});

test('a valid provider outage aggregate is promoted and unsafe provider detail is rejected', function () {
    CarbonImmutable::setTestNow('2026-07-30T13:30:00-04:00');

    $handoff = validGabbyHandoff('2026-07-30T13:25:00-04:00');
    $handoff['snapshot']['utilities'] = [
        'label' => 'Provider-reported aggregate',
        'items' => [validGabbyDukeUtility()],
    ];
    writeGabbyHandoff(config('gabby.handoff.path'), $handoff);

    $this->artisan('gabby:sync-snapshot')
        ->expectsOutput('Gabby snapshot updated (2026-07-30T13:25:00-04:00).')
        ->assertSuccessful();

    foreach (['/gabby', '/gabby/briefing', '/gabby/map'] as $route) {
        $this->get($route)
            ->assertOk()
            ->assertSeeText('Duke Energy Florida reports 1,161 customers without power in Polk County')
            ->assertSeeText('Provider-reported aggregate');
    }

    $unsafe = validGabbyHandoff('2026-07-30T13:26:00-04:00');
    $unsafeUtility = validGabbyDukeUtility();
    $unsafeUtility['service_address'] = 'not allowed';
    $unsafe['snapshot']['utilities'] = [
        'label' => 'Provider-reported aggregate',
        'items' => [$unsafeUtility],
    ];
    writeGabbyHandoff(config('gabby.handoff.path'), $unsafe);

    $this->artisan('gabby:sync-snapshot')
        ->expectsOutputToContain('contains unsupported provider fields')
        ->assertFailed();

    $mismatched = validGabbyHandoff('2026-07-30T13:26:00-04:00');
    $mismatchedUtility = validGabbyDukeUtility();
    $mismatchedUtility['percent_without_power'] = 12.34;
    $mismatched['snapshot']['utilities'] = [
        'label' => 'Provider-reported aggregate',
        'items' => [$mismatchedUtility],
    ];
    writeGabbyHandoff(config('gabby.handoff.path'), $mismatched);

    $this->artisan('gabby:sync-snapshot')
        ->expectsOutputToContain('does not match the provider totals')
        ->assertFailed();
});
