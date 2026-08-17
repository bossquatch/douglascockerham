<?php

namespace App\Http\Controllers;

use App\Models\InstructorProfile;
use App\Support\InstructorContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InstructorIntakeController extends Controller
{
    public function create(): View
    {
        return view('instructors.create', ['options' => config('region7')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $options = config('region7');
        $courseCatalog = $options['course_catalog'];
        $request->merge([
            'submitted_by_email' => InstructorContact::email($request->input('submitted_by_email')),
            'instructor_email' => InstructorContact::email($request->input('instructor_email')),
            'instructor_phone' => InstructorContact::phone($request->input('instructor_phone')),
        ]);
        $validated = $request->validate([
            'website' => ['nullable', 'size:0'],
            'submitted_by_name' => ['required', 'string', 'max:120'],
            'submitted_by_email' => ['required', 'email:rfc', 'max:190'],
            'instructor_name' => ['required', 'string', 'max:120'],
            'agency' => ['required', 'string', 'max:190'],
            'instructor_email' => ['nullable', 'email:rfc', 'max:190', 'required_without:instructor_phone'],
            'instructor_phone' => ['nullable', 'required_without:instructor_email', 'regex:/^\(\d{3}\) \d{3}-\d{4}$/'],
            'county' => ['required', Rule::in($options['counties'])],
            'courses' => ['required', 'array', 'min:1', 'max:25'],
            'courses.*.course_key' => ['required', Rule::in([...array_keys($courseCatalog), 'other'])],
            'courses.*.other_course_code' => ['nullable', 'required_if:courses.*.course_key,other', 'string', 'max:40'],
            'courses.*.other_course_title' => ['nullable', 'required_if:courses.*.course_key,other', 'string', 'max:190'],
            'courses.*.flex_status' => ['required', Rule::in($options['flex_statuses'])],
            'courses.*.flex_expiration_date' => ['nullable', 'date'],
            'courses.*.delivery_role' => ['required', Rule::in($options['delivery_roles'])],
            'courses.*.delivery_mode' => ['required', Rule::in($options['delivery_modes'])],
            'courses.*.willing_to_travel' => ['required', Rule::in($options['travel_options'])],
            'courses.*.availability' => ['required', Rule::in($options['availability'])],
            'courses.*.prior_deliveries' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'courses.*.last_taught_at' => ['nullable', 'date'],
            'courses.*.regional_priority' => ['required', Rule::in($options['priority_options'])],
            'courses.*.notes' => ['nullable', 'string', 'max:2000'],
            'accuracy_confirmation' => ['accepted'],
        ]);

        $courses = collect($validated['courses'])->map(function (array $course) use ($courseCatalog): array {
            $courseKey = $course['course_key'];
            $course['course_code'] = $courseKey === 'other' ? $course['other_course_code'] : $courseKey;
            $course['course_title'] = $courseKey === 'other' ? $course['other_course_title'] : $courseCatalog[$courseKey];
            unset($course['course_key'], $course['other_course_code'], $course['other_course_title']);

            return $course;
        })->all();

        $profile = DB::transaction(function () use ($validated, $courses): InstructorProfile {
            $profile = InstructorProfile::create([
                'reference' => (string) Str::uuid(),
                'submitted_by_name' => $validated['submitted_by_name'],
                'submitted_by_email' => $validated['submitted_by_email'],
                'instructor_name' => $validated['instructor_name'],
                'agency' => $validated['agency'],
                'instructor_email' => $validated['instructor_email'] ?? null,
                'instructor_phone' => $validated['instructor_phone'] ?? null,
                'county' => $validated['county'],
            ]);

            $profile->capabilities()->createMany($courses);

            return $profile;
        });

        return redirect()->route('instructors.success')
            ->with('submission_reference', $profile->reference)
            ->with('submitted_course_count', count($courses));
    }

    public function success(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('submission_reference')) {
            return redirect()->route('instructors.create');
        }

        return view('instructors.success');
    }
}
