@php
    $identity = collect([
        isset($profileStats['level']) ? 'Level '.$profileStats['level'] : null,
        $profileStats['race'] ?? null, $profileStats['class'] ?? null,
        $profileStats['specialization'] ?? null, $profileStats['faction'] ?? null,
    ])->filter()->implode(' · ');
    $groups = [
        'Vitals' => ['health', 'power', 'armor'],
        'Attributes' => ['strength', 'agility', 'stamina', 'intellect'],
        'Offense' => ['attack_power', 'spell_power', 'spell_penetration', 'melee_crit', 'ranged_crit', 'spell_crit', 'melee_haste'],
        'Defense' => ['dodge', 'parry', 'block'],
    ];
    $knownKeys = collect($groups)->flatten()->merge(['level', 'race', 'class', 'specialization', 'faction', 'equipment', 'professions']);
    $groups['Other stats'] = array_keys(array_diff_key($profileStats, array_fill_keys($knownKeys->all(), true)));
    $percent = ['dodge', 'parry', 'block', 'melee_crit', 'ranged_crit', 'spell_crit', 'melee_haste'];
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
    <span class="emblem" aria-hidden="true">✦</span>
    <div><h2>{{ $profileName }}</h2>@if($identity)<p class="muted">{{ $identity }}</p>@endif</div>
</div>
@if($profileStats)
    <div class="stat-groups">
        @foreach($groups as $title => $keys)
            @php($values = array_intersect_key($profileStats, array_fill_keys($keys, true)))
            @if($values)
                <div class="stat-group"><h3>{{ $title }}</h3>
                    <dl class="stats">
                        @foreach($keys as $key)
                            @if(array_key_exists($key, $values))
                                <dt>{{ ucfirst(str_replace('_', ' ', $key)) }}</dt><dd>{{ $display($values[$key], $key) }}</dd>
                            @endif
                        @endforeach
                    </dl>
                </div>
            @endif
        @endforeach
    </div>
@else
    <p class="muted">No character stats available yet.</p>
@endif
@if(!empty($profileStats['professions']))
    <details class="details-panel" open><summary>Profession ranks</summary>
        <div class="progress-list">
            @foreach($profileStats['professions'] as $profession => $rank)
                <div class="profession"><h3>{{ $profession }}</h3><p class="muted">{{ $rank }}</p>
                    @if(is_string($rank) && preg_match('/^([0-9]+) \/ ([1-9][0-9]*)$/', $rank, $parts) && (int) $parts[1] <= (int) $parts[2])
                        <progress aria-label="{{ $profession }} rank" value="{{ $parts[1] }}" max="{{ $parts[2] }}"></progress>
                    @endif
                </div>
            @endforeach
        </div>
    </details>
@endif
@if(!empty($profileStats['equipment']))
    <details class="details-panel"><summary>Equipped items</summary>
        <dl class="stats">@foreach($profileStats['equipment'] as $slot => $item)<dt>{{ $slot }}</dt><dd>{{ $item }}</dd>@endforeach</dl>
    </details>
@endif
