@php
    $money = static function ($amount) {
        if ($amount === null) return '—';
        $copper = abs((int) $amount);
        $parts = [];
        if ($copper >= 10000) $parts[] = intdiv($copper, 10000).'g';
        if ($copper >= 100) $parts[] = (intdiv($copper, 100) % 100).'s';
        if ($copper % 100 || !$parts) $parts[] = ($copper % 100).'c';
        return ($amount < 0 ? '−' : '').implode(' ', $parts);
    };
    $characterStats = collect($stats)->except(['professions', 'equipment']);
    $best = $ranked->first(fn ($row) => max($row['auction_profit'] ?? 0, $row['vendor_profit'] ?? 0) > 0);
    $vendorBest = $ranked->filter(fn ($row) => ($row['vendor_profit'] ?? 0) > 0)->sortByDesc('vendor_profit')->first();
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WoW Forever · Auction Ledger</title>
    <style>
        :root{color-scheme:dark;--bg:#111518;--surface:#1a2024;--line:#343e43;--text:#f3f1e7;--muted:#b1bec2;--gold:#edc886;--gain:#9de2b3;--loss:#ffb1aa}
        *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font:15px/1.55 system-ui,sans-serif}
        main{max-width:1240px;margin:auto;padding:clamp(18px,4vw,52px)}header{border-bottom:1px solid var(--line);padding-bottom:22px}
        h1,h2,h3,p{margin:0}h1{font:600 clamp(34px,5vw,54px)/1.15 Georgia,serif}h2{font-size:21px}h3{font-size:15px}
        header p,.muted,small{color:var(--muted)}.gold{color:var(--gold)}.gain{color:var(--gain)}.loss{color:var(--loss)}
        form,.cards,.columns{display:flex;gap:14px;flex-wrap:wrap}form{margin-top:20px;align-items:end}
        label{display:grid;gap:4px;color:var(--muted)}select,button{font:inherit;padding:8px 11px;border:1px solid var(--line);border-radius:7px;background:var(--surface);color:var(--text)}
        button{color:#171710;background:var(--gold);border-color:var(--gold);font-weight:700;cursor:pointer}
        .cards{margin:22px 0}.card,section{background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:17px}
        .card{flex:1 1 210px}.card strong{display:block;font-size:23px;margin-top:3px}.columns>section{flex:1 1 350px}
        section{margin-top:16px}section>p{margin:3px 0 12px}.scroll{overflow:auto}
        table{width:100%;border-collapse:collapse;text-align:left;font-variant-numeric:tabular-nums}
        th,td{border-bottom:1px solid var(--line);padding:9px 10px;vertical-align:top}th{color:var(--muted);font-size:13px}
        tr:last-child td{border-bottom:0}.num{white-space:nowrap;text-align:right}details{margin-top:10px}summary{cursor:pointer;color:var(--gold)}
        .stats{display:grid;grid-template-columns:1fr 1fr;gap:3px 12px}dd{margin:0;overflow-wrap:anywhere}
        a{color:var(--gold)}:focus-visible{outline:2px solid var(--gold);outline-offset:3px}
    </style>
</head>
<body>
<main>
    <header>
        <p class="gold">World of Warcraft: Forever</p>
        <h1>Auction Ledger</h1>
        <p>Market prices, learned recipes, and gold-making opportunities.</p>
        @if($markets->isNotEmpty() || $characters->isNotEmpty())
            <form method="get" action="{{ route('wow.dashboard') }}">
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

    <section>
        <h2>Craft for profit</h2>
        <p class="muted">Ranked by the better sale method. Costs use the cheapest priced source for each ingredient, including conversions you know. Auction estimates deduct {{ $cut }}% and exclude lost deposits.</p>
        <div class="scroll"><table>
            <thead><tr><th>Recipe and materials</th><th class="num">Cost</th><th class="num">Vendor pays</th><th class="num">Vendor profit</th><th class="num">AH buyout</th><th class="num">AH profit</th></tr></thead>
            <tbody>
            @forelse($ranked as $row)
                <tr>
                    <td><strong>{{ $row['name'] }}</strong><details><summary>Materials</summary>
                        @foreach($row['inputs'] as $input)
                            <div>{{ $input['quantity'] }} × {{ $input['name'] }} · {{ $input['price'] ? $input['price']['method'].' at '.$money($input['price']['cost']).' each' : 'price unavailable' }}</div>
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

    <section>
        <h2>Buy and resell</h2>
        <p class="muted">The resale target is the median of at least two earlier days of recorded asking prices. Profit is per item after the auction cut. Check live price, quantity, and whether the item actually sells before buying.</p>
        <div class="scroll"><table>
            <thead><tr><th>Item</th><th class="num">Saved buyout</th><th class="num">Resale target</th><th class="num">Estimated profit</th><th class="num">Prior days</th></tr></thead>
            <tbody>
            @forelse($resales as $row)
                <tr><td>@if(ctype_digit((string) $row['key']))<a href="https://www.wowhead.com/forever/item={{ $row['key'] }}">{{ $row['item'] }}</a>@else{{ $row['item'] }}@endif</td><td class="num">{{ $money($row['buy']) }}</td><td class="num">{{ $money($row['target']) }}</td><td class="num gain">{{ $money($row['profit']) }}</td><td class="num">{{ $row['days'] }}</td></tr>
            @empty
                <tr><td colspan="5">No candidates meet the price and history requirements in this snapshot.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </section>

    <details><summary>Tracked material prices</summary>
        <div class="scroll"><table><thead><tr><th>Material</th><th class="num">Saved unit buyout</th></tr></thead>
            <tbody>@foreach($tracked as $id => $name)<tr><td>{{ $name }}</td><td class="num">{{ $money($prices[$id] ?? null) }}</td></tr>@endforeach</tbody>
        </table></div>
    </details>

    <div class="columns">
        <section>
            <h2>Materials for profitable crafts</h2>
            <p class="muted">Each buy cap is per unit and assumes the other ingredients stay at the modeled prices.</p>
            @forelse($materials as $name => $uses)
                <p><strong>{{ $name }}</strong><br>{{ $uses->map(fn ($use) => $use['recipe'].' (×'.$use['quantity'].', up to '.$money($use['cap']).' each)')->implode(', ') }}</p>
            @empty
                <p>No profitable crafts with complete pricing in this snapshot.</p>
            @endforelse
        </section>
        <section>
            <h2>Character and market data</h2>
            <p class="muted">{{ $learned->count() }} learned Leatherworking recipes · {{ $ranked->count() }} with verified craft inputs</p>
            @if($characterStats->isNotEmpty())
                <dl class="stats">@foreach($characterStats as $label => $value)<dt>{{ str_replace('_',' ',ucfirst($label)) }}</dt><dd>{{ is_scalar($value) ? (is_bool($value) ? ($value ? 'Yes' : 'No') : $value) : json_encode($value) }}</dd>@endforeach</dl>
            @else
                <p>No character stats imported yet.</p>
            @endif
            @foreach(['professions' => 'Profession ranks', 'equipment' => 'Equipped items'] as $group => $heading)
                @if(!empty($stats[$group]))
                    <details><summary>{{ $heading }}</summary>
                        <dl class="stats">
                            @foreach($stats[$group] as $label => $value)
                                <dt>{{ $label }}</dt><dd>{{ is_scalar($value) ? $value : json_encode($value) }}</dd>
                            @endforeach
                        </dl>
                    </details>
                @endif
            @endforeach
            @if($learned->isNotEmpty())<details><summary>Learned recipes</summary><ul>@foreach($learned as $name)<li>{{ $name }}</li>@endforeach</ul></details>@endif
            @if($snapshots->isNotEmpty())<details><summary>Recent imports</summary><ul>@foreach($snapshots as $value)<li>{{ $value->captured_at }} UTC · {{ number_format($value->item_count) }} items</li>@endforeach</ul></details>@endif
        </section>
    </div>
</main>
</body>
</html>
