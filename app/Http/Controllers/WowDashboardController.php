<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class WowDashboardController extends Controller
{
    public function refreshAuctions(Request $request): RedirectResponse
    {
        $this->authorizeOwner($request);

        try {
            $refreshed = Artisan::call('wow:pull-auctions') === 0;
            $unchanged = $refreshed && str_contains(Artisan::output(), 'Imported ') === false;
        } catch (\Exception $exception) {
            report($exception);
            $refreshed = false;
        }

        if (! $refreshed) {
            return redirect()->route('wow.dashboard')
                ->with('auction_error', 'Could not refresh the Auction House. Check the configured house and try again.');
        }

        return redirect()->route('wow.dashboard')->with('auction_status', $unchanged
            ? 'Auction House checked. The latest snapshot is already saved.'
            : 'Auction House refreshed. A new snapshot was saved.');
    }

    public function refreshCharacter(Request $request): RedirectResponse
    {
        $this->authorizeOwner($request);

        try {
            $refreshed = Artisan::call('wow:battle-net-check', [
                '--character' => 'darvor-nightstorm',
                '--sync-character' => true,
            ]) === 0;
        } catch (\Exception $exception) {
            report($exception);
            $refreshed = false;
        }

        $snapshot = DB::table('wow_character_snapshots')
            ->whereRaw('LOWER(realm) = ?', ['nightstorm'])
            ->whereRaw('LOWER('.DB::connection()->getQueryGrammar()->wrap('character').') = ?', ['darvor'])
            ->max('id');

        $parameters = array_filter(['character' => $snapshot]);
        $market = $request->input('market');
        if (is_string($market) && DB::table('wow_auction_prices')->where('market', $market)->exists()) {
            $parameters['market'] = $market;
        }

        return redirect()->to(route('wow.dashboard', $parameters).'#character')->with(
            $refreshed ? 'character_status' : 'character_error',
            $refreshed ? 'Darvor’s character data was refreshed.' : 'Could not refresh Darvor’s character data. Try again shortly.',
        );
    }

    private function authorizeOwner(Request $request): void
    {
        $owner = config('wow.owner_email');
        abort_unless(is_string($owner) && $owner !== '' && strcasecmp($request->user()->email, $owner) === 0, 403);
    }

    public function __invoke(Request $request)
    {
        $this->authorizeOwner($request);

        $markets = DB::table('wow_auction_prices')->distinct()->orderBy('market')->pluck('market');
        $configuredMarket = 'US Normal Alliance · '.(int) config('wow.battle_net.auction_house');
        $market = $request->query('market', $markets->containsStrict($configuredMarket) ? $configuredMarket : $markets->first());
        abort_unless($market === null || $markets->containsStrict($market), 404);

        $characters = DB::table('wow_character_snapshots')
            ->select('realm', 'character')->selectRaw('MAX(id) AS id')
            ->groupBy('realm', 'character')->orderBy('character')->get();
        $selected = $request->query('character', $characters->first()?->id);
        abort_unless($selected === null || is_scalar($selected), 404);
        $character = $characters->first(fn ($row) => (string) $row->id === (string) $selected);
        abort_unless($selected === null || $character !== null, 404);

        $stats = [];
        $characterCapturedAt = null;
        $learned = collect();
        if ($character) {
            $characterSnapshot = DB::table('wow_character_snapshots as snapshots')
                ->join('wow_imports as imports', 'snapshots.import_id', '=', 'imports.id')
                ->where('snapshots.id', $character->id)
                ->first(['snapshots.stats', 'imports.captured_at']);
            $stats = json_decode($characterSnapshot->stats ?? '{}', true) ?? [];
            $characterCapturedAt = $characterSnapshot?->captured_at;
            $learned = DB::table('wow_learned_recipes')->where('snapshot_id', $character->id)
                ->where('profession', 'Leatherworking')->orderBy('name')->pluck('name');
        }

        $snapshots = DB::table('wow_auction_prices as prices')->join('wow_imports as imports', 'prices.import_id', '=', 'imports.id')
            ->where('prices.market', $market)
            ->selectRaw('imports.id, imports.captured_at, COUNT(*) AS item_count')
            ->groupBy('imports.id', 'imports.captured_at')->orderByDesc('imports.captured_at')->orderByDesc('imports.id')
            ->limit(8)->get();
        $snapshot = $snapshots->first();
        $catalog = collect(config('wow.recipes'))->filter(fn ($recipe) => $learned->containsStrict($recipe[0]));
        $tracked = collect(config('wow.items'));
        $ids = $catalog->flatMap(fn ($recipe) => [$recipe[1], ...array_keys($recipe[3])])
            ->merge($tracked->keys())->unique()->map(fn ($id) => (string) $id)->values();
        $prices = $snapshot ? DB::table('wow_auction_prices')->where('import_id', $snapshot->id)
            ->where('market', $market)->whereIn('item_key', $ids)->pluck('buyout_copper', 'item_key') : collect();

        $vendorInputs = config('wow.vendor_inputs');
        $craftable = $catalog->keyBy(1);
        $costOf = function (int $item, array $visited = []) use (&$costOf, $prices, $vendorInputs, $craftable): ?array {
            $options = [];
            if (isset($prices[$item]) && $prices[$item] > 0) {
                $options[] = ['cost' => (int) $prices[$item], 'method' => 'buy on AH'];
            }
            if (isset($vendorInputs[$item])) {
                $options[] = ['cost' => $vendorInputs[$item], 'method' => 'buy from vendor'];
            }
            if (! isset($visited[$item]) && isset($craftable[$item])) {
                $visited[$item] = true;
                $total = 0;
                foreach ($craftable[$item][3] as $reagent => $quantity) {
                    $input = $costOf($reagent, $visited);
                    if ($input === null) {
                        $total = null;
                        break;
                    }
                    $total += $input['cost'] * $quantity;
                }
                if ($total !== null) {
                    $options[] = ['cost' => $total, 'method' => 'craft from reagents'];
                }
            }

            return collect($options)->sortBy('cost')->first();
        };

        $cut = config('wow.auction_cut_percent');
        $opportunities = $catalog->map(function ($recipe) use ($costOf, $prices, $tracked, $cut) {
            [$name, $item, $vendor, $reagents] = $recipe;
            $inputs = [];
            $cost = 0;
            foreach ($reagents as $reagent => $quantity) {
                $input = $costOf($reagent, [$item => true]);
                $inputs[] = ['name' => $tracked[$reagent] ?? "Item {$reagent}", 'quantity' => $quantity, 'price' => $input];
                $cost = $cost === null || $input === null ? null : $cost + $input['cost'] * $quantity;
            }
            $auction = isset($prices[$item]) && $prices[$item] > 0 ? (int) $prices[$item] : null;
            $net = $auction === null ? null : intdiv($auction * (100 - $cut), 100);

            return [
                'name' => $name, 'inputs' => $inputs, 'cost' => $cost, 'vendor' => $vendor, 'auction' => $auction,
                'net_auction' => $net,
                'vendor_profit' => $cost === null ? null : $vendor - $cost,
                'auction_profit' => $cost === null || $net === null ? null : $net - $cost,
            ];
        })->values();

        $budgetInput = $request->query('max_buyout');
        $budgetGold = is_string($budgetInput) ? trim($budgetInput) : '';
        $invalidBudget = $budgetInput !== null && (! is_string($budgetInput)
            || ($budgetGold !== '' && ! preg_match('/\A(?:[0-9]{1,8}(?:\.[0-9]{0,4})?|\.[0-9]{1,4})\z/', $budgetGold)));
        $maxBuyout = null;
        if ($budgetGold !== '' && ! $invalidBudget) {
            [$gold, $fraction] = array_pad(explode('.', $budgetGold, 2), 2, '');
            $maxBuyout = (int) $gold * 10_000 + (int) str_pad($fraction, 4, '0');
        }

        $resales = collect();
        if ($snapshot && ! $invalidBudget) {
            $resales = DB::table('wow_auction_prices')->where('import_id', $snapshot->id)
                ->where('market', $market)->where('buyout_copper', '>', 0)
                ->when($maxBuyout !== null, fn ($query) => $query->where('buyout_copper', '<=', $maxBuyout))
                ->cursor()->map(function ($row) use ($tracked, $cut) {
                    $history = json_decode($row->auctionator_data, true)['h'] ?? [];
                    if (! is_array($history)) {
                        return null;
                    }

                    krsort($history, SORT_NUMERIC);
                    $prior = collect(array_slice($history, 1))
                        ->filter(fn ($price) => is_int($price) && $price > 0);

                    // One earlier observation alone is too weak to rank.
                    if ($prior->count() < 2) {
                        return null;
                    }

                    $target = (int) $prior->median();
                    $profit = intdiv($target * (100 - $cut), 100) - $row->buyout_copper;
                    if ($profit <= 0) {
                        return null;
                    }

                    return [
                        'item' => $tracked[$row->item_key] ?? "Item {$row->item_key}",
                        'key' => $row->item_key,
                        'buy' => $row->buyout_copper,
                        'target' => $target,
                        'profit' => $profit,
                        'days' => $prior->count(),
                        'history' => collect($history)->filter(fn ($price) => is_int($price) && $price > 0)
                            ->take(7)->reverse()->values()->all(),
                    ];
                })->filter()->sortByDesc('profit')->take(20)->values()->collect();
        }

        $metadata = app(\App\Services\WowItemMetadata::class)->forKeys($resales->pluck('key'));
        $resales = $resales->map(function ($row) use ($metadata) {
            $id = explode(':', (string) $row['key'], 2)[0];

            return [
                ...$row,
                'item' => $metadata[$id]['name'] ?? $row['item'],
                'quality' => $metadata[$id]['quality'] ?? null,
                'item_id' => ctype_digit($id) ? $id : null,
            ];
        });

        $ranked = $opportunities->sortByDesc(fn ($row) => max(
            $row['vendor_profit'] ?? PHP_INT_MIN, $row['auction_profit'] ?? PHP_INT_MIN
        ))->values();

        $materials = $ranked->filter(fn ($row) => max(
            $row['vendor_profit'] ?? 0, $row['auction_profit'] ?? 0
        ) > 0)->flatMap(function ($row) {
            $proceeds = max($row['vendor'], $row['net_auction'] ?? 0);

            return collect($row['inputs'])->map(function ($input) use ($row, $proceeds) {
                $otherCosts = $row['cost'] - $input['price']['cost'] * $input['quantity'];

                return [
                    ...$input,
                    'recipe' => $row['name'],
                    'cap' => intdiv($proceeds - $otherCosts - 1, $input['quantity']),
                ];
            });
        })->groupBy('name')->sortKeys();

        return response()->view('wow.dashboard', compact(
            'markets', 'market', 'characters', 'character', 'stats', 'characterCapturedAt', 'learned', 'snapshots', 'snapshot',
            'tracked', 'prices', 'ranked', 'materials', 'resales', 'cut', 'budgetGold', 'maxBuyout', 'invalidBudget'
        ))->header('Cache-Control', 'private, no-store');
    }
}
