<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorCapability extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_code', 'course_title', 'flex_status', 'flex_expiration_date',
        'delivery_role', 'delivery_mode', 'willing_to_travel', 'availability',
        'prior_deliveries', 'last_taught_at', 'regional_priority',
        'review_status', 'notes', 'review_notes',
        'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'flex_expiration_date' => 'date',
            'last_taught_at' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(InstructorProfile::class, 'instructor_profile_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
