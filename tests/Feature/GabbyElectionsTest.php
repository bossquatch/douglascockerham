<?php

use App\Services\GabbyElectionsService;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    config()->set(
        'gabby.handoff.state_path',
        storage_path('framework/testing/missing-gabby-dashboard-state-'.bin2hex(random_bytes(6)).'.json'),
    );
});

test('the public elections watch shows neutral official records and aggregate-only coverage', function () {
    $response = $this->get('/gabby/elections');

    $response
        ->assertOk()
        ->assertSeeText('Polk County Elections Watch')
        ->assertSeeText('Neutral public-information digest')
        ->assertSeeText('Gabby does not rank or endorse candidates')
        ->assertSeeText('2026 Primary Election')
        ->assertSeeText('August 18, 2026')
        ->assertSeeText('Early voting')
        ->assertSeeText('7')
        ->assertSeeText('races')
        ->assertSeeText('15')
        ->assertSeeText('official candidate records')
        ->assertSeeText('Candidate statement')
        ->assertSeeText('Not yet verified')
        ->assertSeeText('Filed finance aggregate')
        ->assertSeeText('Filed contributions')
        ->assertSeeText('In-kind')
        ->assertSeeText('Expenditures')
        ->assertSeeText('Period through')
        ->assertSeeText('No individual donor rows are displayed')
        ->assertSee('data-election-race-filter', false)
        ->assertSee('data-election-platform-filter="available"', false)
        ->assertSee('data-election-platform-filter="gap"', false)
        ->assertSee('data-election-candidate', false)
        ->assertSee('href="https://www.kellyforpolk.com/about"', false)
        ->assertSee('href="https://www.voterfocus.com/CampaignFinance/', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false)
        ->assertSee('href="'.route('gabby.elections').'"', false)
        ->assertSee('aria-current="page"', false)
        ->assertDontSee('donors', false)
        ->assertDontSee('service_address', false)
        ->assertDontSee('email_address', false)
        ->assertDontSee('phone_number', false);
});

test('the elections service reports the current coverage limitations', function () {
    $elections = app(GabbyElectionsService::class)->current();

    expect($elections['schema'])->toBe('gabby.elections-local')
        ->and($elections['version'])->toBe(1)
        ->and($elections['stats'])->toMatchArray([
            'races' => 7,
            'candidates' => 15,
            'platform_available' => 2,
            'platform_gaps' => 13,
            'finance_available' => 4,
            'unique_sources' => 10,
        ])
        ->and(collect($elections['races'])->flatMap(fn (array $race): array => $race['candidates']))
        ->toHaveCount(15);
});

test('the elections validator rejects extra donor data and preserves the supported contract', function () {
    $payload = json_decode(
        file_get_contents(resource_path('data/gabby/elections_local_2026.json')),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    $payload['races'][0]['candidates'][0]['finance']['donors'] = [
        ['name' => 'Not permitted'],
    ];
    $path = storage_path('framework/testing/invalid-gabby-elections-'.bin2hex(random_bytes(6)).'.json');
    File::put($path, json_encode($payload, JSON_THROW_ON_ERROR));
    config()->set('gabby.elections.path', $path);

    try {
        expect(fn () => app(GabbyElectionsService::class)->current())
            ->toThrow(RuntimeException::class, 'missing or unsupported fields');
    } finally {
        File::delete($path);
    }
});
