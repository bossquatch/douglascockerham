@php
    $checklist = json_decode(file_get_contents(resource_path('data/wow-profession-catalog.json')), true, flags: JSON_THROW_ON_ERROR);
@endphp

<section class="panel" id="recipe-checklist">
    <h2>Recipe checklist</h2>
    <p class="muted">
        {{ $character ? $character->character.' '.$character->realm : 'No character selected' }} · Client build {{ $checklist['build'] }}
        @if($characterCapturedAt)
            · Character data {{ \Illuminate\Support\Carbon::parse($characterCapturedAt, 'UTC')->format('M j, H:i') }} UTC
        @endif
    </p>
    <p class="muted">Use <a href="#character">Refresh Darvor</a> to update the character snapshot.</p>
    <p class="muted">“Not recorded” means the recipe is absent from the saved character data. Some catalog entries may be historical or unavailable in live WoW Forever.</p>
    <div class="filters" data-recipe-filters hidden>
        <label>Search recipes <input type="search" placeholder="Recipe, material, or source"></label>
        <label>Show
            <select>
                <option value="">All recipes</option>
                <option value="known">Known</option>
                <option value="unrecorded">Not recorded</option>
                <option value="unknown">Status unknown</option>
            </select>
        </label>
        <span role="status"></span>
    </div>

    @foreach($checklist['professions'] as $profession => $recipes)
        @php
            $names = $knownRecipes->get($profession, collect());
            $known = $names->map(fn ($name) => mb_strtolower(trim($name)))->flip();
            $catalogNames = collect($recipes)->pluck('name')->map(fn ($name) => mb_strtolower(trim($name)))->flip();
            $unmatched = $names->filter(fn ($name) => ! $catalogNames->has(mb_strtolower(trim($name))));
        @endphp
        <details class="details-panel" data-recipe-group>
            <summary>{{ $profession }} · {{ count($recipes) }} catalog entries · {{ $known->count() }} known</summary>
            @if($unmatched->isNotEmpty())
                <p class="muted">Known recipes without a matched catalog entry: {{ $unmatched->implode(', ') }}</p>
            @endif
            <div class="recipe-catalog">
                @foreach($recipes as $recipe)
                    @php
                        $status = $known->has(mb_strtolower(trim($recipe['name']))) ? 'known' : ($known->isEmpty() ? 'unknown' : 'unrecorded');
                        $spellUrl = 'https://www.wowhead.com/forever/spell='.$recipe['id'];
                    @endphp
                    <article class="recipe-card" data-recipe-status="{{ $status }}">
                        <h3><a href="{{ $spellUrl }}">{{ $recipe['name'] }}</a></h3>
                        <ul class="recipe-labels">
                            <li @class(['gain' => $status === 'known'])>{{ ['known' => 'Known', 'unrecorded' => 'Not recorded', 'unknown' => 'Status unknown'][$status] }}</li>
                            @if($recipe['category'])<li>{{ $recipe['category'] }}</li>@endif
                            <li>{{ $recipe['skill'] === null ? 'Learning skill unverified' : 'Skill '.$recipe['skill'] }}</li>
                            @if($recipe['partial'])<li>Partial client data</li>@endif
                        </ul>
                        <h4>Materials per craft</h4>
                        @if($recipe['materials'])
                            <ul class="recipe-materials">@foreach($recipe['materials'] as $material)<li>{{ $material }}</li>@endforeach</ul>
                        @else
                            <p class="muted">{{ $recipe['materials'] === null ? 'Materials unavailable' : 'No materials listed' }}</p>
                        @endif
                        <h4>Where to learn it</h4>
                        <p>{{ $recipe['source'] ?? 'Acquisition source unverified' }}</p>
                        @foreach($recipe['recipe_items'] as $item)
                            <p><a href="https://www.wowhead.com/forever/item={{ $item['id'] }}">{{ $item['name'] }}</a>@if($item['level'] > 1) · Requires level {{ $item['level'] }}@endif</p>
                        @endforeach
                        @foreach($recipe['outputs'] as $item)
                            <p>Makes {{ $item['quantity'] }}× <a href="https://www.wowhead.com/forever/item={{ $item['id'] }}">{{ $item['name'] }}</a>@if($item['level'] > 1) · Use level {{ $item['level'] }}@endif @if($item['item_level']) · Item level {{ $item['item_level'] }}@endif</p>
                        @endforeach
                        @if($recipe['tools'])<p>Requires {{ implode(', ', $recipe['tools']) }}</p>@endif
                        @if($recipe['cooldown'])<p>Cooldown {{ number_format($recipe['cooldown'] / 60, 1) }} min</p>@endif
                        @if($recipe['gray'] > 0)
                            <p class="muted">Skill-ups: @if($recipe['yellow'] > 0) Yellow {{ $recipe['yellow'] }} · @endif @if($recipe['green'] > 0) Green {{ $recipe['green'] }} · @endif Gray {{ $recipe['gray'] }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </details>
    @endforeach
    <p class="recipe-source muted">Catalog and acquisition references: <a href="https://forevertome.com/professions">ForeverTome</a>, <a href="https://timesaver.gg/tools/wow-forever/professions">Timesaver</a>, and <a href="https://legacyofwarcraft.com/db/forever/professions/index.html">Legacy of Warcraft</a>.</p>
</section>
