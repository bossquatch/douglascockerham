<header class="region-header">
    <a href="{{ route('home') }}" class="region-brand" aria-label="Return to douglascockerham.com">
        <img class="region-brand__mark" src="{{ asset('images/region-7-emergency-management-shield.webp') }}" alt="Florida Region 7 Emergency Management shield" width="384" height="384">
        <span class="region-brand__copy">
            <span>REGION 7</span>
            <small>Emergency Management</small>
        </span>
    </a>
    <nav class="region-nav" aria-label="Instructor capability navigation">
        <a @class(['is-active' => $active === 'intake']) href="{{ route('instructors.create') }}" @if($active === 'intake') aria-current="page" @endif>Intake</a>
        <a @class(['is-active' => $active === 'administration']) href="{{ route('instructors.admin.index') }}" @if($active === 'administration') aria-current="page" @endif>Administration</a>
    </nav>
    <p>Polk · Hardee · DeSoto · Okeechobee · Highlands</p>
</header>
