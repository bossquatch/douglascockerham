<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('wow:import {file : Path to a price or character JSON export}', function () {
    $payload = file_get_contents($this->argument('file'));
    if ($payload === false) {
        $this->error('Unable to read the import file.');

        return 1;
    }

    $data = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
    $source = $data['source'] ?? null;
    if (! in_array($source, ['auctionator', 'character'], true) || empty($data['captured_at'])) {
        $this->error('Expected an auctionator or character export with captured_at.');

        return 1;
    }

    $captured = \Illuminate\Support\Carbon::parse($data['captured_at'])->utc();
    $hash = hash('sha256', $payload);

    $count = \Illuminate\Support\Facades\DB::transaction(function () use ($captured, $data, $hash, $source) {
        $db = \Illuminate\Support\Facades\DB::connection();
        $inserted = $db->table('wow_imports')->insertOrIgnore([
            'sha256' => $hash,
            'source' => $source,
            'captured_at' => $captured,
            'imported_at' => now()->utc(),
        ]);
        if (! $inserted) {
            return null;
        }

        $id = $db->table('wow_imports')->where('sha256', $hash)->value('id');

        if ($source === 'auctionator') {
            $rows = array_map(fn (array $price) => [
                'import_id' => $id,
                'market' => $price['market'],
                'item_key' => $price['item_key'],
                'buyout_copper' => $price['data']['m'] ?? null,
                'auctionator_data' => json_encode($price['data'], JSON_THROW_ON_ERROR),
            ], $data['prices']);

            foreach (array_chunk($rows, 100) as $chunk) {
                $db->table('wow_auction_prices')->insert($chunk);
            }

            return count($rows);
        }

        $snapshot = $db->table('wow_character_snapshots')->insertGetId([
            'import_id' => $id,
            'realm' => $data['realm'],
            'character' => $data['character'],
            'stats' => json_encode((object) ($data['stats'] ?? []), JSON_THROW_ON_ERROR),
        ]);
        $count = 0;
        foreach ($data['recipes'] ?? [] as $profession => $recipes) {
            foreach (array_unique($recipes) as $recipe) {
                $db->table('wow_learned_recipes')->insert([
                    'snapshot_id' => $snapshot,
                    'profession' => $profession,
                    'name' => $recipe,
                ]);
                $count++;
            }
        }

        return $count;
    });

    $this->info($count === null ? 'This export is already imported.' : "Imported {$count} records from {$source}.");

    return 0;
})->purpose('Import a private WoW price or character snapshot');

\Illuminate\Support\Facades\Schedule::command('wow:pull-auctions')
    ->hourlyAt(15)->withoutOverlapping(10)
    ->when(fn () => filled(config('wow.battle_net.auction_house')));

\Illuminate\Support\Facades\Schedule::command('wow:battle-net-check', [
    '--character' => 'darvor-nightstorm',
    '--sync-character' => true,
])->hourlyAt(25)->withoutOverlapping();
