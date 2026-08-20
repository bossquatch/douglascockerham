<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="auth-light">
    <head>
        @include('partials.head', ['forceLight' => true])
    </head>
    <body class="auth-page min-h-screen antialiased">
        <main class="auth-shell">
            <div class="auth-column">
                <a href="{{ route('home') }}" class="auth-brand" wire:navigate>
                    <img src="{{ asset('images/nextgenem-logo.png') }}" alt="NextGenEM" class="auth-brand__logo">
                </a>
                <section class="auth-panel" aria-label="Account access">
                    {{ $slot }}
                </section>
            </div>
        </main>
        @fluxScripts
    </body>
</html>
