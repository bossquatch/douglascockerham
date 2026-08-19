<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instructor profile submitted</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/css/instructors.css', 'resources/js/app.js'])
</head>
<body class="instructor-page instructor-page--success">
    <main class="success-card">
        <img class="success-card__brand" src="{{ asset('images/region-7-emergency-management-shield.webp') }}" alt="Florida Region 7 Emergency Management shield" width="384" height="384">
        <div class="success-mark" aria-hidden="true">✓</div>
        <h1>Instructor profile submitted</h1>
        <p>Thank you. {{ session('submitted_course_count') }} course {{ Str::plural('capability', session('submitted_course_count')) }} will be reviewed against FLEX, Florida’s Learning Exchange and current training platform, before being counted as regional delivery capacity. <a href="{{ config('region7.fdem_catalog_source') }}" target="_blank" rel="noopener noreferrer">Open FLEX</a>.</p>
        <dl><dt>Submission reference</dt><dd>{{ session('submission_reference') }}</dd></dl>
        <div class="success-actions">
            <a class="button button--primary" href="{{ route('instructors.create') }}">Submit another instructor</a>
            <a class="button" href="{{ route('home') }}">Exit to website</a>
        </div>
    </main>
</body>
</html>

