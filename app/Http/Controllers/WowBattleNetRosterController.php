<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WowBattleNetRosterController extends Controller
{
    public function connect(Request $request): RedirectResponse
    {
        $this->authorizeOwner($request);

        if (! config('wow.battle_net.client_id') || ! config('wow.battle_net.client_secret')) {
            return $this->backToRoster('Battle.net is not configured on this server.');
        }

        $state = Str::random(64);
        $request->session()->put('wow_roster_oauth', [
            'state' => $state,
            'user' => $request->user()->getAuthIdentifier(),
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        return redirect()->away('https://oauth.battle.net/authorize?'.http_build_query([
            'client_id' => config('wow.battle_net.client_id'),
            'redirect_uri' => $this->callbackUrl(),
            'response_type' => 'code',
            'scope' => 'wow.profile',
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986));
    }

    public function callback(Request $request): RedirectResponse
    {
        $this->authorizeOwner($request);

        $pending = $request->session()->pull('wow_roster_oauth');
        $state = $request->query('state');
        if (! $pending || ! is_string($state) || ! hash_equals($pending['state'], $state)
            || $pending['user'] !== $request->user()->getAuthIdentifier() || $pending['expires_at'] <= now()->timestamp) {
            return $this->backToRoster('The Battle.net sign-in expired. Select Connect Battle.net to try again.');
        }

        $code = $request->query('code');
        if (! is_string($code) || $code === '') {
            return $this->backToRoster('Battle.net sign-in was not completed.');
        }

        try {
            $tokenResponse = Http::asForm()->acceptJson()->withoutRedirecting()->connectTimeout(4)->timeout(8)
                ->withBasicAuth(config('wow.battle_net.client_id'), config('wow.battle_net.client_secret'))
                ->post('https://oauth.battle.net/token', [
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $this->callbackUrl(),
                    'code' => $code,
                ]);
            $token = $tokenResponse->json('access_token');

            if (! $tokenResponse->successful() || ! is_string($token) || $token === '') {
                return $this->backToRoster('Battle.net could not complete sign-in. Try connecting again.');
            }

            $response = Http::withToken($token)->acceptJson()->withoutRedirecting()->connectTimeout(4)->timeout(8)
                ->get('https://us.api.blizzard.com/profile/user/wow', [
                    'namespace' => 'profile-forever-us',
                    'locale' => 'en_US',
                ]);
        } catch (ConnectionException) {
            return $this->backToRoster('Battle.net is temporarily unreachable. Try connecting again.');
        }

        $accounts = $response->json('wow_accounts');
        if (! $response->successful() || ! is_array($accounts)) {
            return $this->backToRoster('Battle.net did not return a WoW Forever roster. Try connecting again.');
        }

        $characters = collect($accounts)->flatMap(fn ($account) => data_get($account, 'characters', []))
            ->filter(fn ($character) => is_string(data_get($character, 'name')) && trim($character['name']) !== '')
            ->map(function ($character) {
                $profile = data_get($character, 'character.href', data_get($character, 'key.href', ''));
                $parts = explode('-', rawurldecode(basename((string) parse_url(is_string($profile) ? $profile : '', PHP_URL_PATH))), 2);
                $name = explode(' ', trim($character['name']), 2);
                $surname = data_get($character, 'surname');
                $surname = is_string($surname) && $surname !== '' ? $surname : ($name[1] ?? $parts[1] ?? '');

                return [
                    'first_name' => $name[0],
                    'surname' => $surname,
                    'level' => (int) data_get($character, 'level', 0),
                ];
            })
            ->unique(fn ($character) => mb_strtolower($character['first_name'].' '.$character['surname']))
            ->sortBy(['first_name', 'surname'])->values()->all();

        $request->session()->put('wow_battle_net_roster', [
            'user' => $request->user()->getAuthIdentifier(),
            'characters' => $characters,
            'imported_at' => now()->toIso8601String(),
        ]);

        return $this->backToRoster();
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $this->authorizeOwner($request);
        $request->session()->forget(['wow_battle_net_roster', 'wow_roster_oauth']);

        return $this->backToRoster();
    }

    private function authorizeOwner(Request $request): void
    {
        $owner = config('wow.owner_email');
        abort_unless(is_string($owner) && $owner !== '' && strcasecmp($request->user()->email, $owner) === 0, 403);
    }

    private function callbackUrl(): string
    {
        return rtrim(config('app.url'), '/').route('wow.roster.callback', absolute: false);
    }

    private function backToRoster(?string $error = null): RedirectResponse
    {
        $redirect = redirect()->to(route('wow.dashboard').'#my-characters');

        return $error === null ? $redirect : $redirect->with('wow_roster_error', $error);
    }
}
