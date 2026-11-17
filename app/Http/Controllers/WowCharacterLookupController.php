<?php

namespace App\Http\Controllers;

use App\Services\WowCharacterSync;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class WowCharacterLookupController extends Controller
{
    private const LOCALES = ['us' => 'en_US', 'eu' => 'en_GB', 'kr' => 'ko_KR', 'tw' => 'zh_TW'];

    public function __invoke(Request $request, WowCharacterSync $characterSync)
    {
        $owner = config('wow.owner_email');
        abort_unless(is_string($owner) && $owner !== '' && strcasecmp($request->user()->email, $owner) === 0, 403);

        $parameters = $request->validate([
            'first_name' => ['required', 'string', 'max:36', 'regex:/\A\p{L}[\p{L}\p{M}]*\z/u'],
            'surname' => ['required', 'string', 'max:36', 'regex:/\A\p{L}[\p{L}\p{M}]*\z/u'],
            'region' => ['required', Rule::in(array_keys(self::LOCALES))],
        ]);
        $region = $parameters['region'];
        $name = $parameters['first_name'].' '.$parameters['surname'];
        $stats = [];
        $recipes = [];
        $error = null;
        $missing = [];

        try {
            $id = config('wow.battle_net.client_id');
            $secret = config('wow.battle_net.client_secret');
            if (! is_string($id) || trim($id) === '' || ! is_string($secret) || trim($secret) === '') {
                $error = 'Battle.net is not configured on this server.';
            } else {
                $credentials = Http::asForm()->acceptJson()->withBasicAuth($id, $secret)
                    ->withoutRedirecting()->connectTimeout(4)->timeout(8)
                    ->post('https://oauth.battle.net/token', ['grant_type' => 'client_credentials']);
                $token = $credentials->json('access_token');

                if (! $credentials->successful() || ! is_string($token) || $token === '') {
                    $error = 'Battle.net authentication failed. Check the API client in Forge.';
                } else {
                    $slug = rawurlencode(mb_strtolower($parameters['first_name'].'-'.$parameters['surname']));
                    $api = Http::baseUrl("https://{$region}.api.blizzard.com")->withToken($token)
                        ->acceptJson()->withoutRedirecting()->connectTimeout(4)->timeout(8);
                    $query = ['namespace' => "profile-forever-{$region}", 'locale' => self::LOCALES[$region]];
                    foreach (['profile' => '', 'statistics' => '/statistics', 'professions' => '/professions', 'equipment' => '/equipment'] as $key => $suffix) {
                        try {
                            $result = $api->get("/profile/wow/character/{$slug}{$suffix}", $query);
                            $data[$key] = $result->successful() && is_array($result->json()) ? $result->json() : [];
                        } catch (ConnectionException) {
                            $data[$key] = [];
                        }

                        if ($key === 'profile' && $data[$key] === []) {
                            $error = isset($result) && $result->notFound()
                                ? 'Battle.net didn’t find that character. Check the name and region.'
                                : 'Battle.net could not return this profile. Try again in a moment.';
                            break;
                        }
                        if ($data[$key] === []) {
                            $missing[] = $key;
                        }
                    }

                    if ($error === null) {
                        [$stats, $recipes] = $characterSync->summarize($data);
                    }
                }
            }
        } catch (ConnectionException) {
            $error = 'The server could not reach Battle.net. Try again in a moment.';
        }

        return response()->view('wow.lookup-result', compact('parameters', 'name', 'stats', 'recipes', 'error', 'missing'))
            ->header('Cache-Control', 'private, no-store');
    }
}
