<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instructor capability review | Region 7</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/css/instructors.css', 'resources/css/instructors-admin.css', 'resources/js/app.js', 'resources/js/instructors.js'])
</head>
<body class="instructor-admin-page">
    @include('instructors.partials.header', ['active' => 'administration'])
    <div class="admin-capability">
        <div class="admin-heading">
            <div><h1>Instructor capability review</h1><p>Verify course-level capacity, identify development needs, and export the regional matrix.</p></div>
            <div class="admin-actions"><a href="{{ route('instructors.create') }}" class="admin-button admin-button--secondary">Open public intake</a><a href="{{ route('instructors.admin.export', request()->query()) }}" class="admin-button">Export Excel</a></div>
        </div>

        @if(session('status'))<div class="admin-notice">{{ session('status') }}</div>@endif
        @if($errors->any())
            <div class="admin-error" role="alert"><strong>The record was not saved.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <section class="summary-strip" aria-label="Capability summary">
            <div><span>Matching instructors</span><strong data-summary-instructors>{{ number_format($summary['instructors']) }}</strong></div>
            <div><span>Verified capabilities</span><strong data-summary-verified>{{ number_format($summary['verified']) }}</strong></div>
            <div><span>Pending verification</span><strong data-summary-pending>{{ number_format($summary['pending']) }}</strong></div>
            <div><span>Identified gaps</span><strong data-summary-gaps>{{ number_format($summary['gaps']) }}</strong></div>
        </section>

        <form class="admin-filters" method="GET">
            <label><span>Search</span><input name="search" value="{{ request('search') }}" placeholder="Instructor, agency, or course"></label>
            <label><span>County</span><select name="county"><option value="">All counties</option>@foreach($options['counties'] as $option)<option value="{{ $option }}" @selected(request('county') === $option)>{{ $option }}</option>@endforeach</select></label>
            <label><span>FLEX status</span><select name="flex_status"><option value="">All FLEX statuses</option>@foreach($options['flex_statuses'] as $option)<option value="{{ $option }}" @selected(request('flex_status') === $option)>{{ $option }}</option>@endforeach</select></label>
            <label><span>Review status</span><select name="review_status"><option value="">All review statuses</option>@foreach($options['review_statuses'] as $option)<option value="{{ $option }}" @selected(request('review_status') === $option)>{{ Str::headline($option) }}</option>@endforeach</select></label>
            <button class="admin-button" type="submit">Filter</button>
            <a class="admin-button admin-button--secondary" href="{{ route('instructors.admin.index') }}">Reset</a>
        </form>

        <div @class(['admin-workspace', 'has-drawer' => $selected])>
            <div class="capability-table-wrap">
                <table class="capability-table">
                    <thead><tr><th>Instructor</th><th>County</th><th>Agency</th><th>Course</th><th>FLEX</th><th>Role</th><th>Mode</th><th>Availability</th><th>Review</th><th>Updated</th></tr></thead>
                    <tbody>
                    @forelse($capabilities as $capability)
                        <tr class="{{ optional($selected)->is($capability) ? 'is-selected' : '' }}">
                            <td><a href="{{ request()->fullUrlWithQuery(['selected' => $capability->id]) }}"><strong>{{ $capability->profile->instructor_name }}</strong><small>{{ $capability->profile->instructor_email ?: $capability->profile->instructor_phone }}</small></a></td>
                            <td>{{ $capability->profile->county }}</td><td>{{ $capability->profile->agency }}</td>
                            <td><strong>{{ $capability->course_code }}</strong><small>{{ $capability->course_title }}</small></td>
                            <td><span class="status status--{{ Str::slug($capability->flex_status) }}">{{ $capability->flex_status }}</span></td>
                            <td>{{ $capability->delivery_role }}</td><td>{{ $capability->delivery_mode }}</td><td>{{ $capability->availability }}</td>
                            <td><span class="status status--{{ $capability->review_status }}">{{ Str::headline($capability->review_status) }}</span></td>
                            <td>{{ $capability->updated_at->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="empty-table">No capability records match these filters.</td></tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="admin-pagination">{{ $capabilities->links() }}</div>
            </div>

            @if($selected)
                <aside class="review-drawer">
                    <div class="drawer-heading"><div><h2>Edit record</h2><p>{{ $selected->profile->instructor_name }} · {{ $selected->course_code }}</p></div><a href="{{ request()->fullUrlWithQuery(['selected' => null]) }}" aria-label="Close editor">×</a></div>
                    <form method="POST" action="{{ route('instructors.admin.update', $selected) }}" data-admin-edit-form>@csrf @method('PATCH')
                        <fieldset class="drawer-form-section">
                            <legend>Instructor information</legend>
                            <div class="drawer-field-grid">
                                <label class="drawer-field--wide"><span>Instructor name <b>*</b></span><input name="instructor_name" value="{{ old('instructor_name', $selected->profile->instructor_name) }}" required maxlength="120"></label>
                                <label class="drawer-field--wide"><span>Agency or organization <b>*</b></span><input name="agency" value="{{ old('agency', $selected->profile->agency) }}" required maxlength="190"></label>
                                <label><span>Work email</span><input type="email" name="instructor_email" value="{{ old('instructor_email', $selected->profile->instructor_email) }}" maxlength="190" inputmode="email"></label>
                                <label><span>Work phone</span><input type="tel" name="instructor_phone" value="{{ old('instructor_phone', $selected->profile->instructor_phone) }}" maxlength="14" inputmode="tel" placeholder="(863) 555-0123" pattern="\(\d{3}\) \d{3}-\d{4}" title="Enter a 10-digit phone number" data-phone-input></label>
                                <label class="drawer-field--wide"><span>County <b>*</b></span><select name="county" required>@foreach($options['counties'] as $option)<option value="{{ $option }}" @selected(old('county', $selected->profile->county) === $option)>{{ $option }}</option>@endforeach</select></label>
                            </div>
                            <p class="drawer-help">At least one work contact method—email or phone—is required.</p>
                        </fieldset>

                        <fieldset class="drawer-form-section">
                            <legend>Course capability</legend>
                            <div class="drawer-field-grid">
                                <label><span>Course code <b>*</b></span><input name="course_code" value="{{ old('course_code', $selected->course_code) }}" required maxlength="40"></label>
                                <label><span>FLEX status <b>*</b></span><select name="flex_status" required>@foreach($options['flex_statuses'] as $option)<option value="{{ $option }}" @selected(old('flex_status', $selected->flex_status) === $option)>{{ $option }}</option>@endforeach</select></label>
                                <label class="drawer-field--wide"><span>Course title <b>*</b></span><input name="course_title" value="{{ old('course_title', $selected->course_title) }}" required maxlength="190"></label>
                                <div class="drawer-field--wide">@include('instructors.partials.segmented-date', ['name' => 'flex_expiration_date', 'label' => 'Approval expiration', 'value' => old('flex_expiration_date', $selected->flex_expiration_date), 'id' => 'admin-flex-expiration'])</div>
                                <label><span>Delivery role <b>*</b></span><select name="delivery_role" required>@foreach($options['delivery_roles'] as $option)<option value="{{ $option }}" @selected(old('delivery_role', $selected->delivery_role) === $option)>{{ $option }}</option>@endforeach</select></label>
                                <label><span>Delivery mode <b>*</b></span><select name="delivery_mode" required>@foreach($options['delivery_modes'] as $option)<option value="{{ $option }}" @selected(old('delivery_mode', $selected->delivery_mode) === $option)>{{ $option }}</option>@endforeach</select></label>
                                <label><span>Travel within Region 7? <b>*</b></span><select name="willing_to_travel" required>@foreach($options['travel_options'] as $option)<option value="{{ $option }}" @selected(old('willing_to_travel', $selected->willing_to_travel) === $option)>{{ $option }}</option>@endforeach</select></label>
                                <label><span>Availability <b>*</b></span><select name="availability" required>@foreach($options['availability'] as $option)<option value="{{ $option }}" @selected(old('availability', $selected->availability) === $option)>{{ $option }}</option>@endforeach</select></label>
                                <label><span>Number of deliveries</span><input type="number" name="prior_deliveries" value="{{ old('prior_deliveries', $selected->prior_deliveries) }}" min="0" max="9999"></label>
                                <label><span>Regional priority? <b>*</b></span><select name="regional_priority" required>@foreach($options['priority_options'] as $option)<option value="{{ $option }}" @selected(old('regional_priority', $selected->regional_priority) === $option)>{{ $option }}</option>@endforeach</select></label>
                                <div class="drawer-field--wide">@include('instructors.partials.segmented-date', ['name' => 'last_taught_at', 'label' => 'Date last taught', 'value' => old('last_taught_at', $selected->last_taught_at), 'id' => 'admin-last-taught', 'max' => now()->toDateString()])</div>
                                <label class="drawer-field--wide"><span>Capability notes</span><textarea name="notes" rows="4" maxlength="2000">{{ old('notes', $selected->notes) }}</textarea></label>
                            </div>
                        </fieldset>

                        <fieldset class="drawer-form-section">
                            <legend>Administrative review</legend>
                            <div class="drawer-field-grid">
                                <label class="drawer-field--wide"><span>Review status <b>*</b></span><select name="review_status" required>@foreach($options['review_statuses'] as $option)<option value="{{ $option }}" @selected(old('review_status', $selected->review_status) === $option)>{{ Str::headline($option) }}</option>@endforeach</select></label>
                                <label class="drawer-field--wide"><span>Review notes</span><textarea name="review_notes" rows="5" maxlength="2000">{{ old('review_notes', $selected->review_notes) }}</textarea></label>
                            </div>
                        </fieldset>

                        <div class="drawer-actions"><button class="admin-button" type="submit">Save record</button><a class="admin-button admin-button--secondary" href="{{ request()->fullUrlWithQuery(['selected' => null]) }}">Cancel</a></div>
                    </form>
                    <footer>Submitted {{ $selected->profile->created_at->format('M j, Y g:i A') }} by {{ $selected->profile->submitted_by_name }}</footer>
                </aside>
            @endif
        </div>
    </div>
</body>
</html>

