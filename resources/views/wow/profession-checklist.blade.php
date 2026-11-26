@php
    $checklist = json_decode(file_get_contents(resource_path('data/wow-profession-catalog.json')), true, flags: JSON_THROW_ON_ERROR);
    $advancedBandageTrainer = 'After Triage, check Doctor Gustaf VanHowzen on Theramore Isle, Dustwallow Marsh (67.6, 48.8).';
    $recipeTrainers = [
        10548 => 'Drakk Stonehand at Aerie Peak, The Hinterlands (Alliance).',
        10841 => $advancedBandageTrainer,
        18629 => $advancedBandageTrainer,
        18630 => $advancedBandageTrainer,
    ];
    $trainerGuides = [
        'Leatherworking' => ['Darianna or Telonis in Darnassus. For advanced recipes, check Drakk Stonehand at Aerie Peak, The Hinterlands.', 'https://www.wow-professions.com/forever/leatherworking-leveling-guide'],
        'Cooking' => ['Alegorn at the Craftsmen’s Terrace in Darnassus (49.8, 21.0).', 'https://www.wowhead.com/forever/guide/professions/cooking/overview-leveling'],
        'First Aid' => ['Dannelor in Darnassus (51.6, 12.6). For advanced bandages after Triage, check Doctor Gustaf VanHowzen on Theramore Isle (67.6, 48.8).', 'https://www.method.gg/wow-forever/first-aid-in-wow-forever-1-300-leveling-guide-and-trainers'],
    ];
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
        <details class="details-panel" data-recipe-group @if($profession === 'Leatherworking') open @endif>
            <summary>{{ $profession }} · {{ count($recipes) }} catalog entries · {{ $known->count() }} known</summary>
            @if($unmatched->isNotEmpty())
                <p class="muted">Known recipes without a matched catalog entry: {{ $unmatched->implode(', ') }}</p>
            @endif
            <div class="scroll" role="region" aria-label="{{ $profession }} recipes" tabindex="0">
                <table class="recipe-checklist-table">
                    <caption class="muted">{{ $profession }} recipes</caption>
                    <thead>
                        <tr>
                            <th scope="col">Recipe</th>
                            <th scope="col">Status</th>
                            <th scope="col">Where to get it</th>
                            <th scope="col">Requirements</th>
                            <th scope="col">Materials per craft</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recipes as $recipe)
                            @php
                                $status = $known->has(mb_strtolower(trim($recipe['name']))) ? 'known' : ($known->isEmpty() ? 'unknown' : 'unrecorded');
                                $spellUrl = 'https://www.wowhead.com/forever/spell='.$recipe['id'];
                                $source = $recipe['source'];
                                $trainer = $source !== null && str_starts_with($source, 'Trainer');
                            @endphp
                            <tr data-recipe-status="{{ $status }}">
                                <th scope="row">
                                    <a href="{{ $spellUrl }}">{{ $recipe['name'] }}</a>
                                    @if($recipe['category'])<small>{{ $recipe['category'] }}</small>@endif
                                    @if($recipe['partial'])<small>Partial client data</small>@endif
                                    @foreach($recipe['outputs'] as $item)
                                        <small>Makes {{ $item['quantity'] }}× <a href="https://www.wowhead.com/forever/item={{ $item['id'] }}">{{ $item['name'] }}</a>@if($item['item_level']) · Item level {{ $item['item_level'] }}@endif</small>
                                    @endforeach
                                </th>
                                <td @class(['gain' => $status === 'known'])>{{ ['known' => 'Known', 'unrecorded' => 'Not recorded', 'unknown' => 'Unknown'][$status] }}</td>
                                <td>
                                    @if($trainer)
                                        {{ $source }}
                                        <small>
                                            {{ $recipeTrainers[$recipe['id']] ?? 'Alliance trainers to check: '.$trainerGuides[$profession][0] }}
                                            <a href="{{ $trainerGuides[$profession][1] }}">Trainer guide</a>
                                        </small>
                                    @elseif($source)
                                        {{ $source }}
                                    @else
                                        <span class="muted">Location unverified</span>
                                    @endif
                                    @foreach($recipe['recipe_items'] as $item)
                                        <small><a href="https://www.wowhead.com/forever/item={{ $item['id'] }}">{{ $item['name'] }} · Sources</a></small>
                                    @endforeach
                                    @if(!$recipe['recipe_items'] && !$trainer)
                                        <small><a href="{{ $spellUrl }}">Check recipe sources</a></small>
                                    @endif
                                </td>
                                <td>
                                    {{ $recipe['skill'] === null ? 'Skill unverified' : 'Skill '.$recipe['skill'] }}
                                    @foreach($recipe['recipe_items'] as $item)
                                        @if($item['level'] > 1)<small>Learn at level {{ $item['level'] }}</small>@endif
                                    @endforeach
                                    @foreach($recipe['outputs'] as $item)
                                        @if($item['level'] > 1)<small>Use at level {{ $item['level'] }}</small>@endif
                                    @endforeach
                                    @if($recipe['tools'])<small>Requires {{ implode(', ', $recipe['tools']) }}</small>@endif
                                    @if($recipe['cooldown'])<small>Cooldown {{ number_format($recipe['cooldown'] / 60, 1) }} min</small>@endif
                                    @if($recipe['gray'] > 0)
                                        <small>@if($recipe['yellow'] > 0) Yellow {{ $recipe['yellow'] }} · @endif @if($recipe['green'] > 0) Green {{ $recipe['green'] }} · @endif Gray {{ $recipe['gray'] }}</small>
                                    @endif
                                </td>
                                <td>
                                    @forelse($recipe['materials'] ?? [] as $material)
                                        <span class="recipe-material">{{ $material }}</span>
                                    @empty
                                        <span class="muted">{{ $recipe['materials'] === null ? 'Materials unavailable' : 'No materials listed' }}</span>
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    @endforeach
    <p class="recipe-source muted">Catalog and acquisition references: <a href="https://forevertome.com/professions">ForeverTome</a>, <a href="https://timesaver.gg/tools/wow-forever/professions">Timesaver</a>, and <a href="https://legacyofwarcraft.com/db/forever/professions/index.html">Legacy of Warcraft</a>.</p>
</section>
