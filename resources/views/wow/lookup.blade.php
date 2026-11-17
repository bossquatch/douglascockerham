<section class="panel" id="lookup">
    <div class="section-head">
        <p class="eyebrow">Battle.net</p>
        <h2>Character lookup</h2>
        <p>Find a public WoW Forever character by first name, surname and region.</p>
    </div>
    <form class="lookup-form" action="{{ route('wow.lookup') }}#lookup" method="get">
        <label>First name <input name="first_name" required maxlength="36" placeholder="Darvor" value="{{ $parameters['first_name'] ?? old('first_name') }}"></label>
        <label>Surname <input name="surname" required maxlength="36" placeholder="Nightstorm" value="{{ $parameters['surname'] ?? old('surname') }}"></label>
        <label>Region
            <select name="region">
                @foreach(['us' => 'Americas', 'eu' => 'Europe', 'kr' => 'Korea', 'tw' => 'Taiwan'] as $code => $regionName)
                    <option value="{{ $code }}" @selected(($parameters['region'] ?? old('region', 'us')) === $code)>{{ $regionName }}</option>
                @endforeach
            </select>
        </label>
        <label>Gear priorities
            <select name="build">
                <option value="auto">Use active talents</option>
                <option value="balance" @selected(($parameters['build'] ?? old('build')) === 'balance')>Balance Druid</option>
            </select>
        </label>
        <button>Look up character</button>
    </form>
    @if($errors->any())
        <ul class="loss" role="alert">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
    @endif
</section>
