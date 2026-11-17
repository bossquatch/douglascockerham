<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Character lookup · Auction Ledger</title>
    <link rel="stylesheet" href="{{ asset('wow/ledger.css') }}">
</head>
<body>
<main>
    <nav aria-label="Dashboard"><a href="{{ route('wow.dashboard') }}">← Auction Ledger</a></nav>
    @if($error)
        <section class="panel"><h1>Character unavailable</h1><p class="loss" role="alert">{{ $error }}</p></section>
    @else
        <section class="panel">
            @include('wow.character-summary', ['profileStats' => $stats, 'profileName' => $name])
            @if($missing)
                <p class="muted">Battle.net didn’t supply {{ implode(', ', $missing) }} for this character.</p>
            @endif
            @forelse($recipes as $profession => $names)
                <details class="details-panel"><summary>{{ $profession }} · {{ count(array_unique($names)) }} known recipes</summary>
                    <ul class="recipe-list">@foreach(array_unique($names) as $recipe)<li>{{ $recipe }}</li>@endforeach</ul>
                </details>
            @empty
                <p class="muted">Battle.net supplied no learned recipe names.</p>
            @endforelse
        </section>
    @endif
    @include('wow.lookup')
</main>
</body>
</html>
