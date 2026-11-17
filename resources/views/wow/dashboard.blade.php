@php
    $money = static function ($amount, bool $plain = false) {
        if ($amount === null) return '—';
        $copper = abs((int) $amount);
        $coins = ['gold' => intdiv($copper, 10000), 'silver' => intdiv($copper, 100) % 100, 'copper' => $copper % 100];
        $coins = array_filter($coins) ?: ['copper' => 0];
        $negative = $amount < 0;
        $label = ($negative ? 'minus ' : '').collect($coins)->map(fn ($count, $metal) => $count.' '.$metal)->implode(' ');
        if ($plain) return $label;

        $content = $negative ? '−' : '';
        foreach ($coins as $metal => $count) {
            $content .= '<span class="coin-unit">'.$count.'<span class="coin coin--'.$metal.'"></span></span>';
        }

        return new \Illuminate\Support\HtmlString('<span class="coin-amount" role="img" aria-label="'.e($label).'"><span aria-hidden="true">'.$content.'</span></span>');
    };
    $best = $ranked->first(fn ($row) => max($row['auction_profit'] ?? 0, $row['vendor_profit'] ?? 0) > 0);
    $vendorBest = $ranked->filter(fn ($row) => ($row['vendor_profit'] ?? 0) > 0)->sortByDesc('vendor_profit')->first();
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WoW Forever · Auction Ledger</title>
    <link rel="stylesheet" href="{{ asset('auction-ledger-assets/ledger.css') }}">
    <script src="{{ asset('auction-ledger-assets/ledger.js') }}" defer></script>
</head>
<body>
<main>
    <header class="hero">
        <p class="eyebrow">World of Warcraft: Forever</p>
        <h1>Auction Ledger</h1>
        <p>Market prices, learned recipes, and gold-making opportunities.</p>
        @if($markets->isNotEmpty() || $characters->isNotEmpty())
            <form method="get" action="{{ route('wow.dashboard') }}">
                @if($maxBuyout !== null)
                    <input type="hidden" name="max_buyout" value="{{ $budgetGold }}">
                @endif
                @if($markets->isNotEmpty())
                    <label>Market <select name="market">@foreach($markets as $value)<option @selected($market === $value)>{{ $value }}</option>@endforeach</select></label>
                @endif
                @if($characters->isNotEmpty())
                    <label>Character <select name="character">@foreach($characters as $value)<option value="{{ $value->id }}" @selected($character?->id === $value->id)>{{ $value->character }}–{{ $value->realm }}</option>@endforeach</select></label>
                @endif
                <button>Apply</button>
            </form>
        @endif
    </header>
    <nav aria-label="Dashboard sections">
        <a href="#crafts">Craft for profit</a><a href="#resale">Buy &amp; resell</a>
        <a href="#materials">Materials</a><a href="#character">My character</a>
        <a href="#lookup">Character lookup</a>
    </nav>
    <div class="cards">
        <div class="card"><span class="muted">Best modeled craft</span><strong class="gain">{{ $best['name'] ?? 'No positive margin' }}</strong>
            @if($best){{ $money(max($best['vendor_profit'] ?? 0, $best['auction_profit'] ?? 0)) }} per craft · {{ ($best['auction_profit'] ?? 0) > ($best['vendor_profit'] ?? 0) ? 'auction house' : 'vendor' }}@endif
        </div>
        <div class="card"><span class="muted">Best vendor sale</span><strong>{{ $vendorBest['name'] ?? 'No positive margin' }}</strong>
            @if($vendorBest){{ $money($vendorBest['vendor_profit']) }} per craft @endif
        </div>
        <div class="card"><span class="muted">Market snapshot</span><strong>{{ number_format($snapshot->item_count ?? 0) }} items</strong>
            {{ $snapshot ? $snapshot->captured_at.' UTC' : 'Awaiting first import' }}
        </div>
    </div>

    <div class="columns">
        @foreach([
            'Profitable crafts' => $ranked->map(fn ($row) => ['name' => $row['name'], 'value' => max($row['auction_profit'] ?? 0, $row['vendor_profit'] ?? 0)])->filter(fn ($row) => $row['value'] > 0)->take(5),
            'Resale estimates' => $resales->take(5)->map(fn ($row) => ['name' => $row['item'], 'value' => $row['profit'], 'quality' => $row['quality']]),
        ] as $heading => $bars)
            <section class="panel">
                <h2>{{ $heading }}</h2>
                <p class="muted">Estimated profit per item from saved prices</p>
                @if($bars->isNotEmpty())
                    <ul class="chart">
                        @foreach($bars as $bar)
                            <li><span class="chart-label rarity-{{ $bar['quality'] ?? 'unknown' }}">{{ $bar['name'] }}</span>
                                <progress class="{{ $heading === 'Resale estimates' ? 'resale' : '' }}" aria-label="{{ $bar['name'] }}: {{ $money($bar['value'], true) }}" value="{{ $bar['value'] }}" max="{{ $bars->max('value') }}"></progress>
                                <strong class="gain chart-value">{{ $money($bar['value']) }}</strong>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="muted">No positive estimates in this snapshot.</p>
                @endif
            </section>
        @endforeach
    </div>

    <section class="panel" id="crafts">
        <div class="section-head"><h2>Craft for profit</h2>
        <p class="muted">Ranked by the better sale method. Costs use the cheapest priced source for each ingredient, including conversions you know. Auction estimates deduct {{ $cut }}% and exclude lost deposits.</p></div>
        <div class="filters" data-filter hidden>
            <label>Find a recipe or material <input type="search" placeholder="e.g. Light Leather"></label>
            <label><input type="checkbox"> Profitable only</label><small role="status"></small>
        </div>
        <div class="scroll"><table>
            <thead><tr><th>Recipe and materials</th><th class="num">Cost</th><th class="num">Vendor pays</th><th class="num">Vendor profit</th><th class="num">AH buyout</th><th class="num">AH profit</th></tr></thead>
            <tbody>
            @forelse($ranked as $row)
                <tr data-entry data-profitable="{{ max($row['vendor_profit'] ?? 0, $row['auction_profit'] ?? 0) > 0 ? 'true' : 'false' }}">
                    <td><strong>{{ $row['name'] }}</strong><details><summary>Materials</summary>
                        @foreach($row['inputs'] as $input)
                            <div>{{ $input['quantity'] }} × {{ $input['name'] }} · @if($input['price']){{ $input['price']['method'] }} at {{ $money($input['price']['cost']) }} each @else price unavailable @endif</div>
                        @endforeach
                    </details></td>
                    <td class="num">{{ $money($row['cost']) }}</td>
                    <td class="num">{{ $money($row['vendor']) }}</td>
                    <td class="num {{ $row['vendor_profit'] > 0 ? 'gain' : ($row['vendor_profit'] < 0 ? 'loss' : '') }}">{{ $money($row['vendor_profit']) }}</td>
                    <td class="num">{{ $money($row['auction']) }}</td>
                    <td class="num {{ $row['auction_profit'] > 0 ? 'gain' : ($row['auction_profit'] < 0 ? 'loss' : '') }}">{{ $money($row['auction_profit']) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No modeled recipes for this character yet.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </section>

    <section class="panel" id="resale">
        <div class="section-head"><h2>Buy and resell</h2>
        <p class="muted">The resale target is the median of at least two earlier days of recorded asking prices. Profit is per item after the auction cut. Check live price, quantity, and whether the item actually sells before buying.</p></div>
        <form class="filters" method="get" action="{{ route('wow.dashboard') }}#resale">
            @if($market !== null)
                <input type="hidden" name="market" value="{{ $market }}">
            @endif
            @if($character)
                <input type="hidden" name="character" value="{{ $character->id }}">
            @endif
            <label>Maximum buyout per item (gold)
                <input name="max_buyout" type="number" min="0" max="99999999.9999" step="0.0001"
                    inputmode="decimal" placeholder="e.g. 5" value="{{ $invalidBudget ? '' : $budgetGold }}">
            </label>
            <button>Apply budget</button>
            @if($maxBuyout !== null || $invalidBudget)
                <a href="{{ route('wow.dashboard', array_filter(['market' => $market, 'character' => $character?->id])) }}#resale">Clear budget</a>
            @endif
        </form>
        @if($invalidBudget)
            <p class="loss" role="alert">Enter a nonnegative gold amount with up to four decimal places.</p>
        @elseif($maxBuyout !== null)
            <p class="muted">Showing buyouts up to {{ $money($maxBuyout) }} per item.</p>
        @endif
        <div class="filters" data-filter hidden>
            <label>Find an item <input type="search" placeholder="Item name or ID"></label><small role="status"></small>
        </div>
        <div class="scroll"><table>
            <thead><tr><th>Item</th><th class="num">Saved buyout</th><th class="num">Resale target</th><th class="num">Estimated profit</th><th class="num">Prior days</th></tr></thead>
            <tbody>
            @forelse($resales as $row)
                <tr data-entry data-key="{{ $row['key'] }}" data-buyout="{{ $row['buy'] }}"><td>@if($row['item_id'])<a class="rarity-{{ $row['quality'] ?? 'unknown' }}" href="https://www.wowhead.com/forever/item={{ $row['item_id'] }}">{{ $row['item'] }}</a>@else{{ $row['item'] }}@endif
                    <details><summary>Price history</summary>
                        <small>Saved daily asking prices, oldest first</small>
                        <ul class="chart item-history">
                            @foreach($row['history'] as $price)
                                <li><progress aria-label="{{ $money($price, true) }}" value="{{ $price }}" max="{{ max($row['history']) }}"></progress><span class="chart-value">{{ $money($price) }}</span></li>
                            @endforeach
                        </ul>
                    </details>
                </td><td class="num">{{ $money($row['buy']) }}</td><td class="num">{{ $money($row['target']) }}</td><td class="num gain">{{ $money($row['profit']) }}</td><td class="num">{{ $row['days'] }}</td></tr>
            @empty
                <tr><td colspan="5">{{ $invalidBudget ? 'Apply a valid budget to see resale candidates.' : 'No candidates meet the price and history requirements in this snapshot.' }}</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </section>

    <section class="panel" id="materials"><h2>Materials</h2>
    <details><summary>Tracked material prices</summary>
        <div class="scroll"><table><thead><tr><th>Material</th><th class="num">Saved unit buyout</th></tr></thead>
            <tbody>@foreach($tracked as $id => $name)<tr><td>{{ $name }}</td><td class="num">{{ $money($prices[$id] ?? null) }}</td></tr>@endforeach</tbody>
        </table></div>
    </details>

    <div class="details-panel">
            <h3>Materials for profitable crafts</h3>
            <p class="muted">Each buy cap is per unit and assumes the other ingredients stay at the modeled prices.</p>
            @forelse($materials as $name => $uses)
                <p><strong>{{ $name }}</strong></p>
                <ul class="material-caps">
                    @foreach($uses as $use)
                        <li>{{ $use['recipe'] }} · {{ $use['quantity'] }} needed · max {{ $money($use['cap']) }} each</li>
                    @endforeach
                </ul>
            @empty
                <p>No profitable crafts with complete pricing in this snapshot.</p>
            @endforelse
    </div></section>
    <section class="panel" id="character">
        @include('wow.character-summary', ['profileStats' => $stats, 'profileName' => $character ? $character->character.' '.$character->realm : 'My character'])
        <p class="muted">{{ $learned->count() }} learned Leatherworking recipes · {{ $ranked->count() }} with verified craft inputs</p>
        @if($learned->isNotEmpty())
            <details class="details-panel"><summary>Learned recipes</summary>
                <ul class="recipe-list">@foreach($learned as $name)<li>{{ $name }}</li>@endforeach</ul>
            </details>
        @endif
    </section>
    @include('wow.lookup')
    @if($snapshots->isNotEmpty())
        <section class="panel">
            <h2>Market coverage</h2>
            <p class="muted">Items captured in each import · newest first</p>
            <ul class="chart coverage-chart">
                @foreach($snapshots as $value)
                    <li><time class="chart-label" datetime="{{ \Illuminate\Support\Carbon::parse($value->captured_at, 'UTC')->toIso8601String() }}">{{ \Illuminate\Support\Carbon::parse($value->captured_at, 'UTC')->format('M j, H:i') }} UTC</time>
                        <progress aria-label="{{ number_format($value->item_count) }} items" value="{{ $value->item_count }}" max="{{ $snapshots->max('item_count') }}"></progress>
                        <strong class="chart-value">{{ number_format($value->item_count) }}</strong>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</main>
</body>
</html>
