<?php

use App\Models\InstructorCapability;
use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Support\Str;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function createCapabilityRecord(): InstructorCapability
{
    $profile = InstructorProfile::create([
        'reference' => (string) Str::uuid(),
        'submitted_by_name' => 'Taylor Morgan',
        'submitted_by_email' => 'taylor@example.gov',
        'instructor_name' => 'Alex Rivera',
        'agency' => 'Polk County Emergency Management',
        'instructor_email' => 'alex@example.gov',
        'instructor_phone' => null,
        'county' => 'Polk',
    ]);

    return $profile->capabilities()->create([
        'course_code' => 'G-300',
        'course_title' => 'Intermediate Incident Command System for Expanding Incidents',
        'flex_status' => 'Approved',
        'delivery_role' => 'Lead Instructor',
        'delivery_mode' => 'In Person',
        'willing_to_travel' => 'Yes',
        'availability' => 'Available',
        'regional_priority' => 'Yes',
        'review_status' => 'pending',
    ]);
}

function validAdminUpdatePayload(InstructorCapability $capability, array $overrides = []): array
{
    return array_replace([
        'instructor_name' => $capability->profile->instructor_name,
        'agency' => $capability->profile->agency,
        'instructor_email' => $capability->profile->instructor_email,
        'instructor_phone' => $capability->profile->instructor_phone,
        'county' => $capability->profile->county,
        'course_code' => $capability->course_code,
        'course_title' => $capability->course_title,
        'flex_status' => $capability->flex_status,
        'flex_expiration_date' => null,
        'delivery_role' => $capability->delivery_role,
        'delivery_mode' => $capability->delivery_mode,
        'willing_to_travel' => $capability->willing_to_travel,
        'availability' => $capability->availability,
        'prior_deliveries' => 5,
        'last_taught_at' => '2026-05-12',
        'regional_priority' => $capability->regional_priority,
        'notes' => 'Available regionally.',
        'review_status' => $capability->review_status,
        'review_notes' => null,
    ], $overrides);
}

test('the public can access instructor administration', function () {
    $this->get(route('instructors.admin.index'))
        ->assertOk()
        ->assertSeeText('Instructor capability review')
        ->assertSeeText('Polk · Hardee · DeSoto · Okeechobee · Highlands')
        ->assertSeeText('Intake')
        ->assertSeeText('Administration');
});

test('non administrators can access instructor administration', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('instructors.admin.index'))
        ->assertOk();
});

test('administrators can review instructor capabilities', function () {
    $capability = createCapabilityRecord();
    $administrator = User::factory()->create();

    $this->actingAs($administrator)
        ->get(route('instructors.admin.index'))
        ->assertOk()
        ->assertSeeText('Alex Rivera')
        ->assertSeeText('G-300');

    $this->actingAs($administrator)
        ->patch(route('instructors.admin.update', $capability), validAdminUpdatePayload($capability, [
            'review_status' => 'verified',
            'review_notes' => 'Checked directly in FLEX.',
        ]))->assertSessionHas('status');

    expect($capability->fresh()->review_status)->toBe('verified')
        ->and($capability->fresh()->reviewed_by)->toBe($administrator->id);
});

test('administrators can edit instructor and course records', function () {
    $capability = createCapabilityRecord();

    $this->actingAs(User::factory()->create())
        ->patch(route('instructors.admin.update', $capability), validAdminUpdatePayload($capability, [
            'instructor_name' => 'Alexis Rivera',
            'instructor_email' => null,
            'instructor_phone' => '863-555-0199',
            'course_title' => 'Updated Intermediate ICS',
            'flex_expiration_date' => '2029-12-31',
            'prior_deliveries' => 14,
        ]))->assertSessionHas('status');

    expect($capability->profile->fresh()->instructor_name)->toBe('Alexis Rivera')
        ->and($capability->profile->fresh()->instructor_phone)->toBe('(863) 555-0199')
        ->and($capability->fresh()->course_title)->toBe('Updated Intermediate ICS')
        ->and($capability->fresh()->prior_deliveries)->toBe(14)
        ->and($capability->fresh()->flex_expiration_date->format('Y-m-d'))->toBe('2029-12-31');
});

test('public visitors can edit instructor and course records', function () {
    $capability = createCapabilityRecord();

    $this->patch(route('instructors.admin.update', $capability), validAdminUpdatePayload($capability, [
        'availability' => 'Limited',
        'review_status' => 'verified',
    ]))->assertSessionHas('status');

    expect($capability->fresh()->availability)->toBe('Limited')
        ->and($capability->fresh()->review_status)->toBe('verified')
        ->and($capability->fresh()->reviewed_by)->toBeNull();
});

test('dashboard totals reflect the active filters', function () {
    $pending = createCapabilityRecord();
    $pending->update(['review_status' => 'pending']);
    $verified = createCapabilityRecord();
    $verified->profile->update(['instructor_name' => 'Morgan Lee', 'county' => 'Hardee']);
    $verified->update(['review_status' => 'verified', 'course_code' => 'G-400']);

    $this->actingAs(User::factory()->create())
        ->get(route('instructors.admin.index', ['review_status' => 'pending']))
        ->assertOk()
        ->assertSee('<strong data-summary-instructors>1</strong>', false)
        ->assertSee('<strong data-summary-verified>0</strong>', false)
        ->assertSee('<strong data-summary-pending>1</strong>', false)
        ->assertSee('<strong data-summary-gaps>0</strong>', false);
});

test('public visitors can export a real xlsx workbook', function () {
    createCapabilityRecord();

    $response = $this->get(route('instructors.admin.export'));

    $response->assertOk();
    expect($response->headers->get('content-type'))
        ->toContain('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});
