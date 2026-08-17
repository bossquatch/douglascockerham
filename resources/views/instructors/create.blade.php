<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Region 7 emergency-management instructor capability intake.">
    <title>Region 7 Instructor Capability Intake</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/css/instructors.css', 'resources/js/app.js', 'resources/js/instructors.js'])
</head>
<body class="instructor-page">
    @include('instructors.partials.header', ['active' => 'intake'])

    <main class="intake-shell" data-instructor-intake>
        <div class="intake-main">
            <div class="page-heading">
                <h1>Region 7 Instructor Capability Intake</h1>
                <p>Create one instructor profile, then add every course that instructor can lead, co-teach, evaluate, or is developing toward.</p>
            </div>

            <div class="privacy-note">
                Provide work contact information only. Do not upload certificates, identification documents, or other sensitive records.
            </div>

            @if ($errors->any())
                <div class="error-summary" role="alert">
                    <strong>Please review the highlighted information.</strong>
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('instructors.store') }}" data-intake-form>
                @csrf
                <input class="honeypot" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">

                <section class="form-section">
                    <div class="section-heading"><span>1</span><div><h2>Instructor information</h2><p>Tell us who is submitting and how to reach the instructor.</p></div></div>
                    <div class="field-grid field-grid--two">
                        <label class="field"><span>Submitted by — full name <b>*</b></span><input name="submitted_by_name" value="{{ old('submitted_by_name') }}" required maxlength="120" autocomplete="name"></label>
                        <label class="field"><span>Submitter work email <b>*</b></span><input type="email" name="submitted_by_email" value="{{ old('submitted_by_email') }}" required maxlength="190" autocomplete="email" inputmode="email"></label>
                        <label class="field"><span>Instructor full name <b>*</b></span><input name="instructor_name" value="{{ old('instructor_name') }}" required maxlength="120"></label>
                        <label class="field"><span>Agency or organization <b>*</b></span><input name="agency" value="{{ old('agency') }}" required maxlength="190"></label>
                        <label class="field"><span>Instructor work email</span><input type="email" name="instructor_email" value="{{ old('instructor_email') }}" maxlength="190" inputmode="email"></label>
                        <label class="field"><span>Instructor work phone</span><input type="tel" name="instructor_phone" value="{{ old('instructor_phone') }}" maxlength="14" inputmode="tel" autocomplete="tel" placeholder="(863) 555-0123" pattern="\(\d{3}\) \d{3}-\d{4}" title="Enter a 10-digit phone number" data-phone-input></label>
                        <label class="field"><span>County <b>*</b></span><select name="county" required><option value="">Select county</option>@foreach($options['counties'] as $option)<option @selected(old('county') === $option)>{{ $option }}</option>@endforeach</select></label>
                    </div>
                    <p class="field-help">At least one instructor contact method—work email or work phone—is required.</p>
                </section>

                <section class="form-section">
                    <div class="section-heading"><span>2</span><div><h2>Course capabilities</h2><p>Add a separate course entry for each capability. Course-specific FLEX and delivery details stay attached to the correct course.</p></div></div>
                    <div data-course-list>
                        @foreach(old('courses', [[]]) as $index => $course)
                            @include('instructors.partials.course', ['index' => $index, 'course' => $course])
                        @endforeach
                    </div>
                    <button class="button button--add" type="button" data-add-course><span aria-hidden="true">＋</span> Add another course</button>
                </section>

                <section class="form-section form-section--submit">
                    <div class="section-heading"><span>3</span><div><h2>Review and submit</h2><p>Information will be reviewed against FLEX before it is counted as regional delivery capacity.</p></div></div>
                    <label class="confirmation"><input type="checkbox" name="accuracy_confirmation" value="1" required @checked(old('accuracy_confirmation'))><span>I confirm this information is accurate to the best of my knowledge and may be used for Region 7 training coordination.</span></label>
                    <button class="button button--primary" type="submit">Submit instructor profile <span aria-hidden="true">→</span></button>
                </section>
            </form>
        </div>

        <aside class="review-rail" aria-label="Submission summary">
            <h2>Review summary</h2>
            <dl>
                <div><dt>Instructor</dt><dd data-summary-instructor>Not entered</dd></div>
                <div><dt>Agency</dt><dd data-summary-agency>Not entered</dd></div>
                <div><dt>County</dt><dd data-summary-county>Not selected</dd></div>
                <div><dt>Courses</dt><dd><strong data-summary-courses>1</strong> <span data-summary-course-label>capability</span></dd></div>
            </dl>
            <p>Use one profile for the instructor. Add as many course entries as needed before submitting.</p>
        </aside>
    </main>

    <template data-course-template>
        @include('instructors.partials.course', ['index' => '__INDEX__', 'course' => []])
    </template>
</body>
</html>
