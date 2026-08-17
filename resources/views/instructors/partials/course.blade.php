@php($prefix = "courses[{$index}]")
@php($course = $course ?? [])
<section class="course-entry" data-course-entry>
    <div class="course-entry__heading">
        <div>
            <span class="course-entry__number" data-course-number>Course {{ is_numeric($index) ? ((int) $index + 1) : '' }}</span>
            <p>Enter the approval and delivery details for this course.</p>
        </div>
        <button class="button button--quiet" type="button" data-remove-course>Remove course</button>
    </div>

    <div class="field-grid field-grid--four">
        <div class="field field--wide course-combobox" data-course-combobox>
            <label for="course-search-{{ $index }}">Course <b>*</b></label>
            <div class="course-combobox__control">
                <input id="course-search-{{ $index }}" type="search" data-course-search role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="course-options-{{ $index }}" placeholder="Search and select a course" autocomplete="off">
                <button type="button" data-course-toggle aria-label="Show all courses" tabindex="-1">
                    <svg viewBox="0 0 20 20" aria-hidden="true"><path d="m6 8 4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
            <select name="{{ $prefix }}[course_key]" data-course-picker hidden><option value="">Select a course</option>@foreach($options['course_catalog'] as $code => $title)<option value="{{ $code }}" @selected(($course['course_key'] ?? '') === $code)>{{ $code }} — {{ $title }}</option>@endforeach<option value="other" @selected(($course['course_key'] ?? '') === 'other')>Other — course not listed</option></select>
            <div id="course-options-{{ $index }}" class="course-combobox__menu" data-course-menu role="listbox" hidden>
                @foreach($options['course_catalog'] as $code => $title)
                    <button type="button" role="option" data-course-option data-value="{{ $code }}" data-label="{{ $code }} — {{ $title }}" aria-selected="{{ ($course['course_key'] ?? '') === $code ? 'true' : 'false' }}"><strong>{{ $code }}</strong><span><span>{{ $title }}</span>@if(in_array($code, $options['courses_not_in_current_fdem_catalog'] ?? [], true))<small>Not currently listed in the FDEM catalog</small>@endif</span></button>
                @endforeach
                <button type="button" role="option" data-course-option data-value="other" data-label="Other — Course not listed" aria-selected="{{ ($course['course_key'] ?? '') === 'other' ? 'true' : 'false' }}"><strong>Other</strong><span>Course not listed</span></button>
                <p class="course-combobox__empty" data-course-empty hidden>No courses match that search.</p>
            </div>
        </div>
        <label class="field" data-other-course-field @if(($course['course_key'] ?? '') !== 'other') hidden @endif><span>Other course code <b>*</b></span><input name="{{ $prefix }}[other_course_code]" value="{{ $course['other_course_code'] ?? '' }}" maxlength="40" placeholder="Enter course code"></label>
        <label class="field field--wide" data-other-course-field @if(($course['course_key'] ?? '') !== 'other') hidden @endif><span>Other course title <b>*</b></span><input name="{{ $prefix }}[other_course_title]" value="{{ $course['other_course_title'] ?? '' }}" maxlength="190" placeholder="Enter course title"></label>
        <label class="field"><span>Instructor’s FLEX approval status for this course <b>*</b></span><select name="{{ $prefix }}[flex_status]" required><option value="">Select status</option>@foreach($options['flex_statuses'] as $option)<option @selected(($course['flex_status'] ?? '') === $option)>{{ $option }}</option>@endforeach</select><small class="field-hint">Check current status in <a href="{{ config('region7.fdem_catalog_source') }}" target="_blank" rel="noopener noreferrer">FLEX</a>.</small></label>
        @include('instructors.partials.segmented-date', ['name' => "{$prefix}[flex_expiration_date]", 'label' => 'Approval expiration', 'value' => $course['flex_expiration_date'] ?? '', 'id' => "flex-expiration-{$index}", 'class' => 'field--wide'])

        <label class="field"><span>Delivery role <b>*</b></span><select name="{{ $prefix }}[delivery_role]" required><option value="">Select role</option>@foreach($options['delivery_roles'] as $option)<option @selected(($course['delivery_role'] ?? '') === $option)>{{ $option }}</option>@endforeach</select></label>
        <label class="field"><span>Delivery mode <b>*</b></span><select name="{{ $prefix }}[delivery_mode]" required><option value="">Select mode</option>@foreach($options['delivery_modes'] as $option)<option @selected(($course['delivery_mode'] ?? '') === $option)>{{ $option }}</option>@endforeach</select></label>

        <label class="field"><span>Travel within Region 7? <b>*</b></span><select name="{{ $prefix }}[willing_to_travel]" required><option value="">Select</option>@foreach($options['travel_options'] as $option)<option @selected(($course['willing_to_travel'] ?? '') === $option)>{{ $option }}</option>@endforeach</select></label>
        <label class="field"><span>Current availability <b>*</b></span><select name="{{ $prefix }}[availability]" required><option value="">Select availability</option>@foreach($options['availability'] as $option)<option @selected(($course['availability'] ?? '') === $option)>{{ $option }}</option>@endforeach</select></label>
        <label class="field"><span>Number of times this course has been delivered</span><input type="number" min="0" max="9999" name="{{ $prefix }}[prior_deliveries]" value="{{ $course['prior_deliveries'] ?? '' }}"></label>

        @include('instructors.partials.segmented-date', ['name' => "{$prefix}[last_taught_at]", 'label' => 'Date last taught', 'value' => $course['last_taught_at'] ?? '', 'id' => "last-taught-{$index}", 'class' => 'field--wide'])
        <label class="field"><span>Regional priority course? <b>*</b></span><select name="{{ $prefix }}[regional_priority]" required><option value="">Select</option>@foreach($options['priority_options'] as $option)<option @selected(($course['regional_priority'] ?? '') === $option)>{{ $option }}</option>@endforeach</select></label>
    </div>
    <label class="field field--notes"><span>Notes, limitations, or development needs</span><textarea name="{{ $prefix }}[notes]" maxlength="2000" rows="3">{{ $course['notes'] ?? '' }}</textarea></label>
</section>

