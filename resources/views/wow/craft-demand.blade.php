<section class="panel" id="craft-demand">
    <div class="section-head">
        <h2>@include('wow.icon', ['icon' => 'craft']) Fresh-server craft priorities</h2>
        <p class="muted">Quest turn-ins, gear upgrades, and crafting uses suggest demand. A priority craft also needs a positive margin at your selected Auction House prices.</p>
    </div>
    @php
        $craftsByName = $ranked->keyBy('name');
    @endphp
    @foreach(config('wow.craft_demand') as $stage => $candidates)
        <div class="details-panel">
            <h3>{{ $stage }}</h3>
            @php
                $ordered = collect($candidates)->sortByDesc(fn ($reason, $name) => ($craftsByName->get($name)['auction_profit'] ?? 0) > 0);
            @endphp
            <div class="scroll"><table>
                <thead><tr><th scope="col">Craft</th><th scope="col">Why players buy it</th><th scope="col" class="num">AH profit per craft</th></tr></thead>
                <tbody>
                    @foreach($ordered as $name => $reason)
                        @php
                            $profit = $craftsByName->get($name)['auction_profit'] ?? null;
                        @endphp
                        <tr>
                            <th scope="row">{{ $name }}</th>
                            <td>{{ $reason }}</td>
                            <td class="num {{ $profit > 0 ? 'gain' : ($profit < 0 ? 'loss' : '') }}">
                                @if($profit !== null)
                                    @if($profit > 0)<small>Priority</small> @endif
                                    {{ $money($profit) }}
                                @elseif($craftsByName->has($name))
                                    <span class="muted">Missing prices</span>
                                @else
                                    <span class="muted">Watchlist</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        </div>
    @endforeach
</section>
