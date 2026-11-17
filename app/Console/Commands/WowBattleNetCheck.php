<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class WowBattleNetCheck extends Command
{
    protected $signature = 'wow:battle-net-check
        {--character=darvor-nightstorm : Forever character slug}
        {--ruleset=2 : Forever ruleset ID}';

    protected $description = 'Check Battle.net credentials and discover Forever character data and auction houses';

    public function handle(): int
    {
        $id = config('wow.battle_net.client_id');
        $secret = config('wow.battle_net.client_secret');
        if (! is_string($id) || trim($id) === '' || ! is_string($secret) || trim($secret) === '') {
            $this->error('Set BLIZZARD_CLIENT_ID and BLIZZARD_CLIENT_SECRET in the site environment.');

            return self::FAILURE;
        }

        $character = $this->option('character');
        $ruleset = $this->option('ruleset');
        if (! preg_match('/\A[a-z0-9]+-[a-z0-9]+\z/', $character) || ! preg_match('/\A[1-9][0-9]*\z/', $ruleset)) {
            $this->error('Use a lowercase character slug and a positive numeric ruleset ID.');

            return self::FAILURE;
        }

        try {
            $tokenResponse = Http::asForm()->acceptJson()->withBasicAuth($id, $secret)
                ->withoutRedirecting()->connectTimeout(5)->timeout(15)
                ->post('https://oauth.battle.net/token', ['grant_type' => 'client_credentials']);
            $token = $tokenResponse->json('access_token');

            if (! $tokenResponse->successful() || ! is_string($token) || $token === '') {
                $this->error("Battle.net token request failed (HTTP {$tokenResponse->status()}).");

                return self::FAILURE;
            }

            $this->info('Battle.net authentication succeeded.');
            $api = Http::baseUrl('https://us.api.blizzard.com')->withToken($token)->acceptJson()
                ->withoutRedirecting()->connectTimeout(5)->timeout(20);

            $profilePath = "/profile/wow/character/{$character}";
            $profileQuery = ['namespace' => 'profile-forever-us', 'locale' => 'en_US'];
            $ok = true;
            foreach (['Profile' => '', 'Statistics' => '/statistics', 'Professions' => '/professions'] as $label => $suffix) {
                $response = $api->get($profilePath.$suffix, $profileQuery);
                $this->line("{$label}: HTTP {$response->status()}");
                $ok = $response->successful() && $ok;
                if ($label === 'Profile' && $response->successful()) {
                    $this->line('Reported ruleset ID: '.($response->json('ruleset.id') ?? 'unavailable'));
                }
            }

            $houses = $api->get("/data/wow/ruleset/{$ruleset}/auctions/", [
                'namespace' => 'dynamic-forever-us', 'locale' => 'en_US',
            ]);
            $this->line("Auction house index: HTTP {$houses->status()}");
            $rows = collect($houses->json('auctions') ?? [])->filter(fn ($house) =>
                is_array($house) && isset($house['id'], $house['name']) && is_scalar($house['id']) && is_scalar($house['name'])
            )->map(fn ($house) => [$house['id'], $house['name']])->values()->all();
            if (! $houses->successful() || $rows === []) {
                $this->error('No usable auction house entries returned.');

                return self::FAILURE;
            }

            $this->table(['House ID', 'Name'], $rows);

            return $ok ? self::SUCCESS : self::FAILURE;
        } catch (ConnectionException) {
            $this->error('Could not connect to Battle.net. Check the server network and retry.');

            return self::FAILURE;
        }
    }
}
