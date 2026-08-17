<?php

namespace App\Http\Controllers;

use App\Models\InstructorCapability;
use App\Services\InstructorCapabilityExport;
use App\Support\InstructorContact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InstructorAdminController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);
        $capabilities = (clone $query)->paginate(25)->withQueryString();
        $selected = $request->integer('selected')
            ? InstructorCapability::with(['profile', 'reviewer'])->find($request->integer('selected'))
            : null;

        $summary = [
            'instructors' => (clone $query)->distinct()->count('instructor_profile_id'),
            'verified' => (clone $query)->where('review_status', 'verified')->count(),
            'pending' => (clone $query)->where('review_status', 'pending')->count(),
            'gaps' => (clone $query)->where('review_status', 'gap_identified')->count(),
        ];

        return view('instructors.admin.index', [
            'capabilities' => $capabilities,
            'selected' => $selected,
            'summary' => $summary,
            'options' => config('region7'),
        ]);
    }

    public function update(Request $request, InstructorCapability $capability): RedirectResponse
    {
        $options = config('region7');
        $request->merge([
            'instructor_email' => InstructorContact::email($request->input('instructor_email')),
            'instructor_phone' => InstructorContact::phone($request->input('instructor_phone')),
        ]);
        $validated = $request->validate([
            'instructor_name' => ['required', 'string', 'max:120'],
            'agency' => ['required', 'string', 'max:190'],
            'instructor_email' => ['nullable', 'email:rfc', 'max:190', 'required_without:instructor_phone'],
            'instructor_phone' => ['nullable', 'required_without:instructor_email', 'regex:/^\(\d{3}\) \d{3}-\d{4}$/'],
            'county' => ['required', Rule::in($options['counties'])],
            'course_code' => ['required', 'string', 'max:40'],
            'course_title' => ['required', 'string', 'max:190'],
            'flex_status' => ['required', Rule::in($options['flex_statuses'])],
            'flex_expiration_date' => ['nullable', 'date'],
            'delivery_role' => ['required', Rule::in($options['delivery_roles'])],
            'delivery_mode' => ['required', Rule::in($options['delivery_modes'])],
            'willing_to_travel' => ['required', Rule::in($options['travel_options'])],
            'availability' => ['required', Rule::in($options['availability'])],
            'prior_deliveries' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'last_taught_at' => ['nullable', 'date'],
            'regional_priority' => ['required', Rule::in($options['priority_options'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'review_status' => ['required', Rule::in($options['review_statuses'])],
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($validated, $capability, $request): void {
            $capability->profile->update([
                'instructor_name' => $validated['instructor_name'],
                'agency' => $validated['agency'],
                'instructor_email' => $validated['instructor_email'] ?? null,
                'instructor_phone' => $validated['instructor_phone'] ?? null,
                'county' => $validated['county'],
            ]);

            $capability->update([
                'course_code' => $validated['course_code'],
                'course_title' => $validated['course_title'],
                'flex_status' => $validated['flex_status'],
                'flex_expiration_date' => $validated['flex_expiration_date'] ?? null,
                'delivery_role' => $validated['delivery_role'],
                'delivery_mode' => $validated['delivery_mode'],
                'willing_to_travel' => $validated['willing_to_travel'],
                'availability' => $validated['availability'],
                'prior_deliveries' => $validated['prior_deliveries'] ?? null,
                'last_taught_at' => $validated['last_taught_at'] ?? null,
                'regional_priority' => $validated['regional_priority'],
                'notes' => $validated['notes'] ?? null,
                'review_status' => $validated['review_status'],
                'review_notes' => $validated['review_notes'] ?? null,
                'reviewed_by' => $request->user()?->id,
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('status', 'Instructor and course capability updated.');
    }

    public function export(Request $request, InstructorCapabilityExport $exporter): BinaryFileResponse
    {
        $path = $exporter->build($this->filteredQuery($request)->get());

        return response()->download(
            $path,
            'region-7-instructor-capability-matrix-'.now()->format('Y-m-d').'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    private function filteredQuery(Request $request): Builder
    {
        return InstructorCapability::query()
            ->with('profile')
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->string('search')).'%';
                $query->where(function (Builder $query) use ($search) {
                    $query->where('course_code', 'like', $search)
                        ->orWhere('course_title', 'like', $search)
                        ->orWhereHas('profile', fn (Builder $profile) => $profile
                            ->where('instructor_name', 'like', $search)
                            ->orWhere('agency', 'like', $search));
                });
            })
            ->when($request->filled('county'), fn (Builder $query) => $query
                ->whereHas('profile', fn (Builder $profile) => $profile->where('county', $request->string('county'))))
            ->when($request->filled('course'), fn (Builder $query) => $query->where('course_code', $request->string('course')))
            ->when($request->filled('flex_status'), fn (Builder $query) => $query->where('flex_status', $request->string('flex_status')))
            ->when($request->filled('review_status'), fn (Builder $query) => $query->where('review_status', $request->string('review_status')))
            ->latest('instructor_capabilities.updated_at');
    }
}
