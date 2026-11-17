<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WowItemMetadata
{
    // Pinned Forever metadata for items already shown by the imported market.
    private const SEED = [
        810 => ['name' => 'Hammer of the Northern Wind', 'quality' => 'epic'],
        871 => ['name' => 'Flurry Axe', 'quality' => 'epic'],
        2246 => ['name' => "Myrmidon's Signet", 'quality' => 'epic'],
        12527 => ['name' => 'Ribsplitter', 'quality' => 'rare'],
        14513 => ['name' => 'Pattern: Robe of the Archmage', 'quality' => 'epic'],
        14514 => ['name' => 'Pattern: Robe of the Void', 'quality' => 'epic'],
        14551 => ['name' => "Edgemaster's Handguards", 'quality' => 'epic'],
        15772 => ['name' => 'Pattern: Devilsaur Leggings', 'quality' => 'rare'],
        19268 => ['name' => 'Ace of Elementals', 'quality' => 'rare'],
        13015 => ['name' => 'Serathil', 'quality' => null],
        13077 => ['name' => 'Girdle of Uther', 'quality' => null],
        13118 => ['name' => 'Serpentine Sash', 'quality' => null],
        15781 => ['name' => 'Pattern: Black Dragonscale Leggings', 'quality' => null],
        264226 => ['name' => 'Schematic: EZ-Thro Thorium Grenade', 'quality' => null],
        252938 => ['name' => 'Pattern: Blue Dragonscale Helm', 'quality' => 'epic'],
        833 => ['name' => 'Lifestone', 'quality' => 'epic'],
        8498 => ['name' => 'Tiny Emerald Whelpling', 'quality' => 'common'],
        21548 => ['name' => 'Pattern: Stormshroud Gloves', 'quality' => 'rare'],
        23199 => ['name' => 'Totem of the Storm', 'quality' => 'rare'],
        287034 => ['name' => "Grove Subjugator's Blade", 'quality' => 'rare'],
    ];

    public static function quality(mixed $quality): ?string
    {
        $quality = is_string($quality) ? strtolower($quality) : null;

        return in_array($quality, ['poor', 'common', 'uncommon', 'rare', 'epic', 'legendary', 'artifact', 'heirloom'], true)
            ? $quality : null;
    }

    public function forKeys(iterable $keys): array
    {
        $items = [];
        $missing = [];
        foreach ($keys as $key) {
            $id = explode(':', (string) $key, 2)[0];
            if (! ctype_digit($id) || (int) $id < 1) {
                continue;
            }

            $cached = Cache::get("wow:forever-item:{$id}");
            $items[$id] = $cached ?: (self::SEED[$id] ?? []);
            if ($cached === null && ! isset(self::SEED[$id]['quality'])) {
                $missing[$id] = $id;
            }
        }

        $client = config('wow.battle_net.client_id');
        $secret = config('wow.battle_net.client_secret');
        if (! $missing || ! $client || ! $secret) {
            return $items;
        }

        try {
            $auth = Http::asForm()->acceptJson()->withBasicAuth($client, $secret)
                ->withoutRedirecting()->connectTimeout(2)->timeout(4)
                ->post('https://oauth.battle.net/token', ['grant_type' => 'client_credentials']);
            $token = $auth->json('access_token');
            if (! $auth->successful() || ! is_string($token) || $token === '') {
                return $items;
            }

            $responses = Http::pool(fn (Pool $pool) => array_map(fn ($id) =>
                $pool->as((string) $id)->withToken($token)->acceptJson()
                    ->withoutRedirecting()->connectTimeout(2)->timeout(4)
                    ->get("https://us.api.blizzard.com/data/wow/item/{$id}", [
                        'namespace' => 'static-forever-us', 'locale' => 'en_US',
                    ]), array_values($missing)));

            foreach ($responses as $id => $response) {
                if (! $response instanceof Response) {
                    continue;
                }
                if ($response->notFound()) {
                    Cache::put("wow:forever-item:{$id}", [], now()->addHours(12));
                    continue;
                }
                $name = $response->json('name');
                if ($response->successful() && is_string($name) && trim($name) !== '') {
                    $items[$id] = ['name' => $name, 'quality' => self::quality($response->json('quality.type'))];
                    Cache::put("wow:forever-item:{$id}", $items[$id], now()->addDays(7));
                }
            }
        } catch (ConnectionException) {
            // The cached prices remain usable when item metadata cannot be fetched.
        }

        return $items;
    }
}
