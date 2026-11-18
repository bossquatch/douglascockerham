@php
    $identity = collect([
        isset($profileStats['level']) ? 'Level '.$profileStats['level'] : null,
        $profileStats['race'] ?? null, $profileStats['class'] ?? null,
        $profileStats['specialization'] ?? null, $profileStats['faction'] ?? null,
    ])->filter()->implode(' · ');
    $groups = [
        'Vitals' => ['health', 'power', 'armor'],
        'Attributes' => ['strength', 'agility', 'stamina', 'intellect', 'spirit'],
        'Offense' => ['attack_power', 'spell_power', 'spell_penetration', 'melee_crit', 'ranged_crit', 'spell_crit', 'melee_haste'],
        'Defense' => ['dodge', 'parry', 'block'],
    ];
    $knownKeys = collect($groups)->flatten()->merge(['level', 'race', 'class', 'specialization', 'faction', 'equipment', 'professions', 'attribute_base']);
    $groups['Other stats'] = array_keys(array_diff_key($profileStats, array_fill_keys($knownKeys->all(), true)));
    $percent = ['dodge', 'parry', 'block', 'melee_crit', 'ranged_crit', 'spell_crit', 'melee_haste'];
    $displayGearValue = static function ($value, $field) {
        if (! is_numeric($value)) return $value;
        $number = (float) $value;
        if ($number == 0 || ($field !== 'dps' && floor($number) === $number)) return $value;

        $precision = 1 - (int) floor(log10(abs($number)));
        return number_format(round($number, $precision), max(0, $precision), '.', '');
    };
    $display = static function ($value, $key) use ($percent) {
        if (is_numeric($value)) {
            if (in_array($key, $percent, true)) return number_format((float) $value, 2).'%';
            $number = (float) $value;
            return number_format($number, floor($number) === $number ? 0 : 2);
        }

        return is_bool($value) ? ($value ? 'Yes' : 'No') : (is_scalar($value) ? $value : json_encode($value));
    };
@endphp
<div class="identity">
    <span class="emblem" aria-hidden="true">@include('wow.icon', ['icon' => strtolower($profileStats['class'] ?? '') === 'druid' ? 'druid' : 'character'])</span>
    <div><h2>{{ $profileName }}</h2>@if($identity)<p class="muted">{{ $identity }}</p>@endif</div>
</div>
@if($profileStats)
    <div class="stat-groups">
        @foreach($groups as $title => $keys)
            @php
                $values = array_intersect_key($profileStats, array_fill_keys($keys, true));
            @endphp
            @if($values)
                <div class="stat-group"><h3>{{ $title }}</h3>
                    @if($title === 'Attributes')
                        <table class="attributes-table">
                            <thead><tr><th scope="col">Stat</th><th scope="col">Base</th><th scope="col">Bonus</th><th scope="col">Total</th></tr></thead>
                            <tbody>
                                @foreach($values as $key => $total)
                                    @php
                                        $base = $profileStats['attribute_base'][$key] ?? null;
                                        $bonus = is_numeric($base) && is_numeric($total) ? $total - $base : null;
                                    @endphp
                                    <tr>
                                        <th scope="row">{{ ucfirst($key) }}</th>
                                        <td>{{ $base === null ? '—' : $display($base, $key) }}</td>
                                        <td class="{{ $bonus > 0 ? 'gain' : ($bonus < 0 ? 'loss' : '') }}">{{ $bonus === null ? '—' : ($bonus > 0 ? '+' : '').$display($bonus, $key) }}</td>
                                        <td>{{ $display($total, $key) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if(empty($profileStats['attribute_base']))
                            <small>Base and bonus are unavailable for this snapshot.</small>
                        @else
                            <small>Bonus is the net change reported by Battle.net.</small>
                        @endif
                    @else
                    <dl class="stats">
                        @foreach($keys as $key)
                            @if(array_key_exists($key, $values))
                                <dt>{{ ucfirst(str_replace('_', ' ', $key)) }}</dt><dd>{{ $display($values[$key], $key) }}</dd>
                            @endif
                        @endforeach
                    </dl>
                    @endif
                </div>
            @endif
        @endforeach
    </div>
@else
    <p class="muted">No character stats available yet.</p>
@endif
@isset($talentGroups)
    <details class="details-panel" open><summary>Talents</summary>
        @forelse($talentGroups as $group)
            <p class="muted">{{ $group['active'] ? 'Active build' : (count($talentGroups) === 1 ? 'Reported build' : 'Alternate build') }}</p>
            <div class="progress-list">
                @foreach($group['trees'] as $tree)
                    <div class="profession">
                        <h3>{{ $tree['name'] }} · {{ $tree['points'] }} points</h3>
                        @if($tree['talents'])
                            <ul>
                                @foreach($tree['talents'] as $talent)
                                    <li>{{ $talent['name'] }}@if($talent['rank'] !== null) · rank {{ $talent['rank'] }} @endif</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        @empty
            <p class="muted">Battle.net reported no talent allocation.</p>
        @endforelse
    </details>
@endisset
@if(!empty($profileStats['professions']))
    <details class="details-panel" open><summary>Profession ranks</summary>
        <div class="progress-list">
            @foreach($profileStats['professions'] as $profession => $rank)
                @php
                    $hasProgress = is_string($rank) && preg_match('/^([0-9]+) \/ ([1-9][0-9]*)$/', $rank, $parts)
                        && (int) $parts[1] <= (int) $parts[2];
                @endphp
                <div class="profession"><h3>@include('wow.icon', ['icon' => match (strtolower($profession)) {
                    'leatherworking' => 'craft', 'skinning' => 'materials', 'cooking' => 'cooking',
                    'fishing' => 'fishing', 'first aid' => 'first-aid', 'herbalism' => 'herbalism',
                    'alchemy' => 'alchemy', default => 'craft',
                }]) {{ $profession }}</h3><p class="muted profession-rank">
                    <span>{{ $rank }}</span>
                    @if($hasProgress)
                        <strong>{{ rtrim(rtrim(number_format($parts[1] / $parts[2] * 100, 1), '0'), '.') }}%</strong>
                    @endif
                </p>
                    @if($hasProgress)
                        <progress aria-label="{{ $profession }} rank" value="{{ $parts[1] }}" max="{{ $parts[2] }}"></progress>
                    @endif
                </div>
            @endforeach
        </div>
    </details>
@endif
@if(!empty($profileStats['equipment']))
    <details class="details-panel"><summary>Equipped items</summary>
        @isset($craftOffers)
            <p class="muted">Compared with Darvor’s learned recipes that have verified equipment stats. Green shows gains, red shows losses.</p>
            @if($balance ?? false)
                <p class="muted"><strong>Balance Druid{{ $buildOverride ? ' · selected manually' : ' · from talents' }}</strong>. Gear is ranked for caster leveling, with spell power, spell hit and intellect ahead of melee stats. Stat trade-offs still apply.</p>
            @else
                <p class="muted">Using general stat comparisons.</p>
            @endif
            @if($craftOffers === [])
                <p class="muted">No craftable improvements found in the verified recipes for these equipped items.</p>
            @endif
        @endisset
        <div class="gear-list">
            @foreach($profileStats['equipment'] as $slot => $item)
                @php
                    $gear = is_array($item) ? $item : ['name' => $item];
                @endphp
                <article class="stat-group gear">
                    <p class="eyebrow">@include('wow.icon', ['icon' => match (strtolower($slot)) {
                        'feet' => 'boots', 'hands' => 'gloves', 'legs' => 'legs', 'back' => 'back',
                        'main hand', 'off hand', 'ranged' => 'weapon',
                        'ring 1', 'ring 2', 'trinket 1', 'trinket 2', 'neck' => 'accessory', default => 'gear',
                    }]) {{ $slot }}</p>
                    <h3 class="rarity-{{ $gear['quality'] ?? 'unknown' }}">
                        @if(!empty($gear['id']) && ctype_digit((string) $gear['id']))
                            <a href="https://www.wowhead.com/forever/item={{ $gear['id'] }}">{{ $gear['name'] }}</a>
                        @else
                            {{ $gear['name'] }}
                        @endif
                    </h3>
                    <ul>
                        @foreach(['level' => 'Item level', 'armor' => 'Armor', 'damage' => 'Damage', 'speed' => 'Speed', 'dps' => 'DPS'] as $field => $label)
                            @if(isset($gear[$field]))
                                <li>{{ $label }}: {{ $displayGearValue($gear[$field], $field) }}</li>
                            @endif
                        @endforeach
                        @foreach($gear['lines'] ?? [] as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                    @foreach($craftOffers[$slot] ?? [] as $offer)
                        <div class="craft-offer">
                            <small>{{ $offer['level_needed'] ? 'At level '.$offer['level_needed'] : ($offer['balance_score'] !== null ? ($offer['balance_score'] > 0 ? ($offer['tradeoff'] ? 'Balance gain with trade-offs' : 'Balance improvement') : 'Lower priority for Balance') : ($offer['tradeoff'] ? 'Craftable trade-off' : 'Craftable improvement')) }}</small>
                            <h4><a href="https://www.wowhead.com/forever/item={{ $offer['id'] }}">{{ $offer['name'] }}</a></h4>
                            <ul class="craft-deltas">
                                @foreach($offer['deltas'] as $stat => $delta)
                                    <li class="{{ $delta > 0 ? 'gain' : 'loss' }}">{{ $delta > 0 ? '+' : '−' }}{{ abs($delta) }} {{ $stat }}</li>
                                @endforeach
                            </ul>
                            @if($offer['special_effect'])<small>Review the equipped item’s special effects before replacing it.</small>@endif
                        </div>
                    @endforeach
                </article>
            @endforeach
        </div>
    </details>
@endif
