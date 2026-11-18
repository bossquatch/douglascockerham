<section class="details-panel learned-recipes">
    <details>
        <summary><span class="icon-label">@include('wow.icon', ['icon' => 'craft']) Learned recipes ({{ $learned->count() }})</span></summary>
        <div class="filters" data-filter hidden>
            <label>Find a recipe or material <input type="search" placeholder="e.g. Heavy Leather"></label>
            <small role="status"></small>
        </div>
        <div class="recipe-catalog">
            @foreach($learned as $name)
                @php($recipe = $recipeCatalog[$name] ?? null)
                <article class="recipe-card" data-entry>
                    <h3 class="rarity-{{ $recipe['quality'] ?? 'unknown' }}">
                        @if(isset($recipe['icon']))
                            <img class="wow-icon" src="{{ asset('auction-ledger-assets/recipe-icons/'.$recipe['icon'].'.jpg') }}" alt="" width="24" height="24" loading="lazy">
                        @else
                            @include('wow.icon', ['icon' => 'craft'])
                        @endif
                        {{ $name }}
                    </h3>
                    @if($recipe)
                        <ul class="recipe-labels">
                            @if(isset($recipe['requires_level']))<li>Requires level {{ $recipe['requires_level'] }}</li>@endif
                            <li>Item level {{ $recipe['item_level'] }}</li>
                            @if(isset($recipe['skill']))<li>Learn at skill {{ $recipe['skill'] }}</li>@endif
                            <li>Yellow / gray at {{ $recipe['skill_up'] }}</li>
                        </ul>
                        @if(isset($recipe['details']))
                            <p class="muted">{{ implode(' · ', $recipe['details']) }}</p>
                        @endif
                        <h4>Materials per craft</h4>
                        <ul class="recipe-materials">
                            @foreach($recipe['materials'] as $material)
                                <li>{{ $material }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="muted">Recipe details are unavailable.</p>
                    @endif
                </article>
            @endforeach
        </div>
        <p class="recipe-source muted">Recipe data from <a href="https://wowforeverbuilder.com/professions/leatherworking/">Forever Builder</a>.</p>
    </details>
</section>
