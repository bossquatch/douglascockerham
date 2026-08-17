<header class="region-header">
    <a href="{{ route('home') }}" class="region-brand" aria-label="Return to douglascockerham.com">
        <span>REGION 7</span>
        <small>Emergency Management</small>
    </a>
    <nav class="region-nav" aria-label="Instructor capability navigation">
        <a @class(['is-active' => $active === 'intake']) href="{{ route('instructors.create') }}" @if($active === 'intake') aria-current="page" @endif>Intake</a>
        <a @class(['is-active' => $active === 'administration']) href="{{ route('instructors.admin.index') }}" @if($active === 'administration') aria-current="page" @endif>Administration</a>
    </nav>
    <p>Polk · Hardee · DeSoto · Okeechobee · Highlands</p>
</header>
