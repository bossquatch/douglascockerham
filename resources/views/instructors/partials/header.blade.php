<header class="region-header">
    <div class="region-brand">
        <a class="region-brand__logo-link" href="{{ asset('images/region-7-emergency-management-shield-full.webp') }}" target="_blank" rel="noopener noreferrer" aria-label="View the full-size Florida Region 7 Emergency Management logo (opens in a new tab)">
            <img class="region-brand__mark" src="{{ asset('images/region-7-emergency-management-shield.webp') }}" alt="Florida Region 7 Emergency Management shield" width="384" height="384">
        </a>
        <a href="{{ route('instructors.create') }}" class="region-brand__copy" aria-label="Region 7 Instructor Capability Intake">
            <span>REGION 7</span>
            <small>Emergency Management</small>
        </a>
    </div>
    <nav class="region-nav" aria-label="Instructor capability navigation">
        <a @class(['is-active' => $active === 'intake']) href="{{ route('instructors.create') }}" @if($active === 'intake') aria-current="page" @endif>Intake</a>
        <a @class(['is-active' => $active === 'administration']) href="{{ route('instructors.admin.index') }}" @if($active === 'administration') aria-current="page" @endif>Administration</a>
    </nav>
    @auth
        <details class="region-profile-menu">
            <summary aria-label="Open profile menu for {{ auth()->user()->name }}">
                <span class="region-profile-menu__initials" aria-hidden="true">{{ auth()->user()->initials() }}</span>
                <span class="region-profile-menu__identity"><strong>{{ auth()->user()->name }}</strong><small>Profile</small></span>
                <span class="region-profile-menu__chevron" aria-hidden="true">⌄</span>
            </summary>
            <div class="region-profile-menu__panel">
                <div class="region-profile-menu__account"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></div>
                <a href="{{ route('settings.profile') }}">Profile</a>
                <a href="{{ route('settings.password') }}">Password</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </div>
        </details>
    @else
        <p>Polk · Hardee · DeSoto · Okeechobee · Highlands</p>
    @endauth
</header>
