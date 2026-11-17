<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

class WowAuctionPull extends Command
{
    private const ALLIANCE_HOUSES = [110, 111, 112, 119, 123];

    protected $signature = 'wow:pull-auctions {--compare : Compare US Normal Alliance houses without saving}';

    protected $description = 'Import a US Normal Alliance auction snapshot from Battle.net';

    public function handle(): int
    {
        $compare = $this->option('compare');
        $house = (int) config('wow.battle_net.auction_house');
        if (! $compare && ! in_array($house, self::ALLIANCE_HOUSES, true)) {
            $this->error('Set WOW_AUCTION_HOUSE_ID to your verified Alliance house. Use --compare first.');

            return self::FAILURE;
        }

        $client = config('wow.battle_net.client_id');
        $secret = config('wow.battle_net.client_secret');
        if (! $client || ! $secret) {
            $this->error('Battle.net credentials are missing.');

            return self::FAILURE;
        }

        try {
            $auth = Http::asForm()->acceptJson()->withBasicAuth($client, $secret)
                ->withoutRedirecting()->connectTimeout(5)->timeout(15)
                ->post('https://oauth.battle.net/token', ['grant_type' => 'client_credentials']);
            $token = $auth->json('access_token');
            if (! $auth->successful() || ! is_string($token) || $token === '') {
                throw new UnexpectedValueException("Battle.net authentication failed (HTTP {$auth->status()}).");
            }

            $api = Http::baseUrl('https://us.api.blizzard.com')->withToken($token)->acceptJson()
                ->withoutRedirecting()->connectTimeout(5)->timeout(35);
            if ($compare) {
                return $this->compare($api);
            }

            $items = $this->fetch($api, $house);
            if ($items === []) {
                throw new UnexpectedValueException('No priced listings were returned. The previous snapshot remains active.');
            }

            $this->save($house, $items);

            return self::SUCCESS;
        } catch (ConnectionException) {
            $this->error('Could not reach Blizzard. No auction snapshot was written.');
        } catch (UnexpectedValueException $error) {
            $this->error($error->getMessage());
        }

        return self::FAILURE;
    }

    private function compare(PendingRequest $api): int
    {
        $rows = [];
        foreach (self::ALLIANCE_HOUSES as $house) {
            try {
                $items = $this->fetch($api, $house);
                $row = [$house, count($items)];
                foreach ([2318, 2319, 4234, 4304] as $id) {
                    $row[] = isset($items[$id]) ? "{$items[$id]['m']}c / {$items[$id]['at_min']}" : '—';
                }
                $rows[] = $row;
            } catch (UnexpectedValueException $error) {
                $this->warn("House {$house}: {$error->getMessage()}");
            } catch (ConnectionException) {
                $this->warn("House {$house}: connection timed out.");
            }
        }

        $this->table(['House ID', 'Priced items', 'Light Leather', 'Medium Leather', 'Heavy Leather', 'Thick Leather'], $rows);
        $this->line('Prices are copper per unit / units available at that price. Compare with a fresh in-game scan.');

        return $rows ? self::SUCCESS : self::FAILURE;
    }

    private function fetch(PendingRequest $api, int $house): array
    {
        $path = "/data/wow/ruleset/2/auctions/{$house}";
        $query = ['namespace' => 'dynamic-forever-us', 'locale' => 'en_US'];
        $items = [];

        foreach ([$path, $path.'/commodities'] as $endpoint) {
            $response = $api->get($endpoint, $query);
            $data = $response->json();
            if (! $response->successful() || ! is_array($data) || ! is_array($data['auctions'] ?? null)) {
                throw new UnexpectedValueException("Auction listing response unavailable (HTTP {$response->status()}).");
            }
            foreach ($data['auctions'] as $auction) {
                $item = $auction['item'] ?? [];
                $id = $item['id'] ?? null;
                $quantity = $auction['quantity'] ?? 0;
                $price = $auction['unit_price'] ?? null;
                if (! is_int($id) || $id < 1 || ! is_int($quantity) || $quantity < 1) {
                    continue;
                }

                // Avoid comparing random suffixes or bonus variants to the base item.
                if (! empty($item['modifiers']) || ! empty($item['bonus_lists']) || ! empty($item['rand'])) {
                    continue;
                }
                if ($price === null && is_int($auction['buyout'] ?? null) && $auction['buyout'] > 0) {
                    $price = intdiv($auction['buyout'] - 1, $quantity) + 1;
                }
                if (! is_int($price) || $price < 1) {
                    continue;
                }

                $entry = $items[$id] ?? ['m' => $price, 'at_min' => 0, 'quantity' => 0];
                if ($price < $entry['m']) {
                    $entry['m'] = $price;
                    $entry['at_min'] = 0;
                }
                if ($price === $entry['m']) {
                    $entry['at_min'] += $quantity;
                }
                $entry['quantity'] += $quantity;
                $items[$id] = $entry;
            }
        }

        ksort($items, SORT_NUMERIC);

        return $items;
    }

    private function save(int $house, array $items): void
    {
        $market = "US Normal Alliance · {$house}";
        $now = now()->utc();
        $day = (int) $now->format('Ymd');
        $fingerprint = hash('sha256', json_encode($items, JSON_THROW_ON_ERROR));

        DB::transaction(function () use ($market, $now, $day, $fingerprint, $items) {
            $last = DB::table('wow_auction_prices')->where('market', $market)->max('import_id');
            $previous = $last ? DB::table('wow_auction_prices')->where('market', $market)
                ->where('import_id', $last)->pluck('auctionator_data', 'item_key') : collect();
            $first = json_decode($previous->first() ?? '{}', true);
            if (($first['fingerprint'] ?? null) === $fingerprint && isset($first['h'][$day])) {
                $this->info('No listing changes today. Kept the current snapshot.');

                return;
            }

            $hash = hash('sha256', "{$market}:{$last}:{$day}:{$fingerprint}");
            $inserted = DB::table('wow_imports')->insertOrIgnore([
                'sha256' => $hash,
                'source' => 'battle-net',
                'captured_at' => $now,
                'imported_at' => $now,
            ]);
            if (! $inserted) {
                $this->info('This snapshot was already imported.');

                return;
            }

            $import = DB::table('wow_imports')->where('sha256', $hash)->value('id');
            foreach (array_chunk($items, 200, true) as $chunk) {
                $rows = [];
                foreach ($chunk as $id => $item) {
                    $old = json_decode($previous[$id] ?? '{}', true);
                    $history = $old['h'] ?? [];
                    $history[$day] = $item['m'];
                    krsort($history, SORT_NUMERIC);
                    $rows[] = [
                        'import_id' => $import,
                        'market' => $market,
                        'item_key' => (string) $id,
                        'buyout_copper' => $item['m'],
                        'auctionator_data' => json_encode([
                            ...$item, 'h' => array_slice($history, 0, 30, true), 'fingerprint' => $fingerprint,
                        ], JSON_THROW_ON_ERROR),
                    ];
                }
                DB::table('wow_auction_prices')->insert($rows);
            }

            $this->info('Imported '.count($items)." priced items for {$market}.");
        });
    }
}
