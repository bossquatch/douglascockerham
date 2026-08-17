<?php

namespace Database\Seeders;

use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Region7DemoInstructorSeeder extends Seeder
{
    public function run(): void
    {
        $reviewer = User::where('email', 'reviewer@example.test')->first();

        $profiles = [
            ['Avery Brooks', 'Polk', 'Polk County Emergency Management (Demo)', 'avery.brooks@example.test', [
                ['G-300', 'Intermediate Incident Command System for Expanding Incidents', 'Approved', 'Lead Instructor', 'Any Delivery Mode', 'Yes', 'Available', 12, '2026-05-18', 'Yes', 'verified', 'Current FLEX approval confirmed for demo.'],
            ]],
            ['Jordan Kim', 'Polk', 'Lakeland Public Safety (Demo)', 'jordan.kim@example.test', [
                ['G-400', 'Advanced Incident Command System Command & General Staff: Complex Incidents', 'Approved', 'Co-Instructor', 'In Person', 'Yes', 'Limited', 4, '2025-11-07', 'Yes', 'pending', null],
                ['L-449', 'Incident Command System Curricula Train-the-Trainer', 'Pending', 'Candidate', 'In Person', 'Yes', 'Limited', 0, null, 'Yes', 'gap_identified', 'Instructor-development opportunity for the regional bench.'],
            ]],
            ['Morgan Reed', 'Hardee', 'Hardee County Emergency Management (Demo)', 'morgan.reed@example.test', [
                ['G-191', 'Emergency Operations Center/Incident Command System Interface', 'Approved', 'Lead Instructor', 'Hybrid', 'Yes', 'Available', 9, '2026-04-22', 'Yes', 'verified', 'Available for regional delivery.'],
                ['WebEOC', 'WebEOC Training', 'Approved', 'Lead Instructor', 'Any Delivery Mode', 'Yes', 'Available', 18, '2026-07-09', 'No', 'verified', null],
            ]],
            ['Casey Monroe', 'DeSoto', 'DeSoto County Public Safety (Demo)', 'casey.monroe@example.test', [
                ['L-146', 'Homeland Security Exercise and Evaluation Program Training Course', 'Approved', 'Evaluator / Observer', 'Hybrid', 'Yes', 'Available', 6, '2026-02-14', 'Yes', 'verified', 'Exercise facilitation experience noted.'],
            ]],
            ['Riley Patel', 'Okeechobee', 'Okeechobee County Emergency Management (Demo)', 'riley.patel@example.test', [
                ['G-2304', 'Emergency Operations Center Planning Skillset', 'Pending', 'Lead Instructor', 'Virtual', 'Yes', 'Limited', 2, '2025-09-30', 'Yes', 'pending', null],
                ['EOC-101', 'Emergency Operations Center Orientation', 'Approved', 'Lead Instructor', 'In Person', 'Yes', 'Available', 11, '2026-06-12', 'Yes', 'verified', null],
            ]],
            ['Taylor Bennett', 'Highlands', 'Highlands County Emergency Management (Demo)', 'taylor.bennett@example.test', [
                ['FL-393', 'Mitigation for Emergency Managers', 'Expired', 'Lead Instructor', 'Hybrid', 'Yes', 'Limited', 5, '2024-08-16', 'Yes', 'needs_changes', 'Approval expiration needs follow-up.'],
                ['G-141', 'Instructional Presentation and Evaluation Skills', 'Approved', 'Lead Instructor', 'Any Delivery Mode', 'Yes', 'Available', 8, '2026-03-27', 'No', 'verified', null],
            ]],
            ['Cameron Ellis', 'Polk', 'Polk County Training Division (Demo)', 'cameron.ellis@example.test', [
                ['FL-S.B.180', 'Florida Senior Elected Officials Course', 'Approved', 'Lead Instructor', 'Virtual', 'Yes', 'Available', 7, '2026-07-21', 'Yes', 'verified', null],
            ]],
            ['Jamie Parker', 'Highlands', 'Avon Park Public Safety (Demo)', 'jamie.parker@example.test', [
                ['MGT-315', 'Consortium Course MGT-315', 'Not Checked', 'Co-Instructor', 'In Person', 'Yes', 'Limited', 3, '2025-10-05', 'No', 'pending', null],
                ['AWR-213', 'Consortium Course AWR-213', 'Not in FLEX', 'Candidate', 'In Person', 'Yes', 'Limited', 0, null, 'Yes', 'gap_identified', 'Regional demand identified; qualified instructor not yet confirmed.'],
            ]],
        ];

        DB::transaction(function () use ($profiles, $reviewer): void {
            foreach ($profiles as [$name, $county, $agency, $email, $capabilities]) {
                $profile = InstructorProfile::firstOrCreate(
                    ['instructor_email' => $email],
                    [
                        'reference' => (string) Str::uuid(),
                        'submitted_by_name' => 'Region 7 Demo Data',
                        'submitted_by_email' => 'demo.submitter@example.test',
                        'instructor_name' => $name,
                        'agency' => $agency,
                        'instructor_phone' => null,
                        'county' => $county,
                    ],
                );

                foreach ($capabilities as [$code, $title, $flex, $role, $mode, $travel, $availability, $deliveries, $lastTaught, $priority, $reviewStatus, $reviewNotes]) {
                    $profile->capabilities()->updateOrCreate(
                        ['course_code' => $code],
                        [
                            'course_title' => $title,
                            'flex_status' => $flex,
                            'flex_expiration_date' => $flex === 'Approved' ? '2027-12-31' : null,
                            'delivery_role' => $role,
                            'delivery_mode' => $mode,
                            'willing_to_travel' => $travel,
                            'availability' => $availability,
                            'prior_deliveries' => $deliveries,
                            'last_taught_at' => $lastTaught,
                            'regional_priority' => $priority,
                            'review_status' => $reviewStatus,
                            'notes' => 'Fictional record created for local dashboard demonstration.',
                            'review_notes' => $reviewNotes,
                            'reviewed_by' => $reviewStatus === 'pending' ? null : $reviewer?->id,
                            'reviewed_at' => $reviewStatus === 'pending' ? null : now(),
                        ],
                    );
                }
            }
        });
    }
}
