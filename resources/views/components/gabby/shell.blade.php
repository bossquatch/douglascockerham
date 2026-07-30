@props([
    'snapshot',
    'active' => 'overview',
    'title' => 'Polk County Situational Awareness',
    'pageTitle' => 'Gabby | Polk County Situational Awareness',
])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Gabby is a configured public-source situational-awareness digest for Polk County, Florida.">

    <title>{{ $pageTitle }}</title>

    <link rel="icon" href="/gabby-compass-beacon.svg" type="image/svg+xml">
    @fluxAppearance
    @vite(['resources/css/app.css', 'resources/css/gabby.css', 'resources/js/app.js', 'resources/js/gabby.js'])
</head>
<body class="gabby-page">
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <div class="gabby-shell" data-gabby-shell>
        <aside class="gabby-sidebar" id="gabby-navigation" aria-label="Gabby sections">
            <div class="gabby-brand">
                <x-gabby.brand descriptor="Polk County Situational Awareness" />
            </div>

            <nav class="gabby-nav">
                <a href="{{ route('gabby') }}" @class(['is-active' => $active === 'overview']) @if ($active === 'overview') aria-current="page" @endif>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5M5.5 10v9.5h13V10M9.5 19.5v-6h5v6"/></svg>
                    <span>Overview</span>
                </a>
                <a href="{{ route('gabby.briefing') }}" @class(['is-active' => $active === 'briefing']) @if ($active === 'briefing') aria-current="page" @endif>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 3.5h12v17H6zM9 8h6M9 12h6M9 16h4"/></svg>
                    <span>Briefing</span>
                </a>
                <a href="{{ route('gabby.map') }}" @class(['is-active' => $active === 'map']) @if ($active === 'map') aria-current="page" @endif>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m3.5 6 5-2.5 7 3 5-2.5v14l-5 2.5-7-3-5 2.5zM8.5 3.5v14M15.5 6.5v14"/></svg>
                    <span>Map</span>
                </a>
                <a href="{{ route('gabby') }}#sources">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="7.5" ry="2.5"/><path d="M4.5 5v6c0 1.4 3.4 2.5 7.5 2.5s7.5-1.1 7.5-2.5V5M4.5 11v6c0 1.4 3.4 2.5 7.5 2.5s7.5-1.1 7.5-2.5v-6"/></svg>
                    <span>Sources</span>
                </a>
                <a href="{{ route('gabby') }}#community">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3.5 19c.4-3.5 2.2-5.5 5.5-5.5s5.1 2 5.5 5.5M14 14c3.7-.7 6.1 1.1 6.5 4"/></svg>
                    <span>Community Signals</span>
                </a>
                <a href="{{ route('gabby.elections') }}" @class(['is-active' => $active === 'elections']) @if ($active === 'elections') aria-current="page" @endif>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 10.5h14l1 9H4zM8 10.5 9 4h6l1 6.5M8.5 7h7M9 15h6"/></svg>
                    <span>Elections</span>
                </a>
            </nav>

            <div class="gabby-sidebar__note">
                <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 10v6M12 7h.01"/></svg>
                <p>Public information digest. Important actions require confirmation from official agencies.</p>
            </div>
        </aside>

        <div class="gabby-workspace">
            <header class="gabby-header">
                <button class="nav-toggle" type="button" aria-controls="gabby-navigation" aria-expanded="false" data-nav-toggle>
                    <span class="sr-only">Open dashboard navigation</span>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>

                <div class="gabby-title">
                    <x-gabby.brand context="header" />
                    <span class="gabby-title__divider" aria-hidden="true"></span>
                    <h1>{{ $title }}</h1>
                </div>

                <div class="gabby-header__actions">
                    <button class="theme-toggle" type="button" data-theme-toggle>
                        <svg class="theme-toggle__icon theme-toggle__icon--dark" aria-hidden="true" viewBox="0 0 24 24">
                            <path d="M20.4 15.2A8.5 8.5 0 0 1 8.8 3.6 8.5 8.5 0 1 0 20.4 15.2Z"/>
                        </svg>
                        <svg class="theme-toggle__icon theme-toggle__icon--light" aria-hidden="true" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="4"/>
                            <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
                        </svg>
                        <span class="theme-toggle__label theme-toggle__label--dark">Dark mode</span>
                        <span class="theme-toggle__label theme-toggle__label--light">Light mode</span>
                    </button>

                    <div class="snapshot-status" aria-label="{{ $snapshot['status']['label'] }}">
                        <span class="snapshot-status__indicator" aria-hidden="true"></span>
                        <div>
                            <strong>{{ $snapshot['status']['label'] }}</strong>
                            <span>{{ $snapshot['status']['state'] }}</span>
                        </div>
                        <time datetime="{{ $snapshot['generated_at'] }}">{{ $snapshot['status']['timestamp'] }}</time>
                    </div>
                </div>
            </header>

            <main class="gabby-main" id="main-content">
                <p class="snapshot-notice">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 10v6M12 7h.01"/></svg>
                    <span><strong>{{ $snapshot['status']['coverage'] }}.</strong> Gabby summarizes selected public sources and does not claim full real-time coverage.</span>
                </p>

                {{ $slot }}

                <footer class="official-reminder">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3 5 6v5c0 4.6 2.9 8 7 10 4.1-2 7-5.4 7-10V6zM9 12l2 2 4-4"/></svg>
                    <div>
                        <strong>Always rely on official sources for important decisions.</strong>
                        <span>
                            For life-safety emergencies, call 911. Confirm operational details with
                            <x-gabby.source-link class="gabby-inline-link" :href="$snapshot['_official_links']['public_safety']['url']" :label="$snapshot['_official_links']['public_safety']['label']" />
                            or the responsible city, state, or federal agency.
                        </span>
                    </div>
                </footer>
            </main>
        </div>
    </div>
</body>
</html>
