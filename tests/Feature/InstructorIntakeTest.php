<?php

use App\Models\InstructorCapability;
use App\Models\InstructorProfile;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function validInstructorPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'submitted_by_name' => 'Taylor Morgan',
        'submitted_by_email' => 'taylor@example.gov',
        'instructor_name' => 'Alex Rivera',
        'agency' => 'Polk County Emergency Management',
        'instructor_email' => 'alex@example.gov',
        'instructor_phone' => '863-555-0100',
        'county' => 'Polk',
        'accuracy_confirmation' => '1',
        'courses' => [[
            'course_key' => 'G-300',
            'flex_status' => 'Approved',
            'flex_expiration_date' => '2027-06-30',
            'delivery_role' => 'Lead Instructor',
            'delivery_mode' => 'Any Delivery Mode',
            'willing_to_travel' => 'Yes',
            'availability' => 'Available',
            'prior_deliveries' => 8,
            'last_taught_at' => '2026-03-15',
            'regional_priority' => 'Yes',
            'notes' => 'Available for weekday delivery.',
        ]],
    ], $overrides);
}

test('the public instructor intake can be rendered without signing in', function () {
    $this->get(route('instructors.create'))
        ->assertOk()
        ->assertSeeText('Region 7 Instructor Capability Intake')
        ->assertSee('alt="Florida Region 7 Emergency Management shield"', false)
        ->assertSee(asset('images/region-7-emergency-management-shield.webp'))
        ->assertSee('href="'.asset('images/region-7-emergency-management-shield-full.webp').'"', false)
        ->assertSee('href="'.route('instructors.create').'" class="region-brand__copy"', false)
        ->assertSeeText('Administration')
        ->assertSeeText('Add another course')
        ->assertSee('role="combobox"', false)
        ->assertSee('type="date"', false)
        ->assertSee('max="'.now()->toDateString().'"', false)
        ->assertDontSee('data-date-month', false)
        ->assertSee('data-reuse-submitter', false)
        ->assertSeeText('Use submitter name and email for the instructor')
        ->assertSeeText('Florida’s Learning Exchange')
        ->assertSee(config('region7.fdem_catalog_source'))
        ->assertSee('placeholder="Search and select a course"', false)
        ->assertSeeText('G-300 — Intermediate Incident Command System for Expanding Incidents')
        ->assertSeeText('Not currently listed in the FDEM catalog')
        ->assertSeeText('Other — course not listed')
        ->assertSeeText('Instructor’s FLEX approval status for this course')
        ->assertDontSeeText('No account or Microsoft sign-in is required.');

    $this->assertFileExists(public_path('images/region-7-emergency-management-shield.webp'));
    $this->assertFileExists(public_path('images/region-7-emergency-management-shield-full.webp'));
});

test('legacy instructor URLs redirect to the training application', function () {
    $this->get('/region7/instructors')->assertRedirect('/em/training');
    $this->get('/region7/instructors/admin')->assertRedirect(route('login'));
});

test('one instructor submission stores multiple course capabilities', function () {
    $payload = validInstructorPayload();
    $payload['courses'][] = array_replace($payload['courses'][0], [
        'course_key' => 'G-400',
        'delivery_role' => 'Co-Instructor',
    ]);

    $response = $this->post(route('instructors.store'), $payload);

    $response->assertRedirect(route('instructors.success'));
    expect(InstructorProfile::count())->toBe(1)
        ->and(InstructorProfile::first()->is_test)->toBeFalse()
        ->and(InstructorCapability::count())->toBe(2)
        ->and(InstructorProfile::first()->instructor_name)->toBe('Alex Rivera')
        ->and(InstructorCapability::where('course_code', 'G-400')->value('course_title'))->toBe('Advanced Incident Command System Command & General Staff: Complex Incidents');
});

test('a course not in the catalog can still be submitted', function () {
    $payload = validInstructorPayload([
        'courses' => [[
            'course_key' => 'other',
            'other_course_code' => 'LOCAL-101',
            'other_course_title' => 'Local Emergency Management Orientation',
        ]],
    ]);

    $this->post(route('instructors.store'), $payload)
        ->assertRedirect(route('instructors.success'));

    expect(InstructorCapability::first()->course_code)->toBe('LOCAL-101')
        ->and(InstructorCapability::first()->course_title)->toBe('Local Emergency Management Orientation');
});

test('an instructor work email or work phone is required', function () {
    $this->post(route('instructors.store'), validInstructorPayload([
        'instructor_email' => null,
        'instructor_phone' => null,
    ]))->assertSessionHasErrors(['instructor_email', 'instructor_phone']);

    expect(InstructorProfile::count())->toBe(0);
});

test('date last taught cannot be in the future', function () {
    $this->post(route('instructors.store'), validInstructorPayload([
        'courses' => [[
            'last_taught_at' => now()->addDay()->toDateString(),
        ]],
    ]))->assertSessionHasErrors('courses.0.last_taught_at');

    expect(InstructorProfile::count())->toBe(0);
});

test('optional email is validated and phone numbers are normalized', function () {
    $this->post(route('instructors.store'), validInstructorPayload([
        'instructor_email' => 'not-an-email',
        'instructor_phone' => null,
    ]))->assertSessionHasErrors('instructor_email');

    $this->post(route('instructors.store'), validInstructorPayload([
        'instructor_email' => null,
        'instructor_phone' => '863.555.0100',
    ]))->assertRedirect(route('instructors.success'));

    expect(InstructorProfile::latest('id')->value('instructor_phone'))->toBe('(863) 555-0100');
});

test('the submission confirmation includes a clear exit', function () {
    $this->post(route('instructors.store'), validInstructorPayload())
        ->assertRedirect(route('instructors.success'));

    $this->get(route('instructors.success'))
        ->assertOk()
        ->assertSee('alt="Florida Region 7 Emergency Management shield"', false)
        ->assertSee('href="'.asset('images/region-7-emergency-management-shield-full.webp').'"', false)
        ->assertSeeText('Exit to website')
        ->assertSeeText('Florida’s Learning Exchange')
        ->assertSee(config('region7.fdem_catalog_source'))
        ->assertSee(route('home'));
});

test('the honeypot rejects automated submissions', function () {
    $this->post(route('instructors.store'), validInstructorPayload(['website' => 'spam.example']))
        ->assertSessionHasErrors('website');

    expect(InstructorProfile::count())->toBe(0);
});



