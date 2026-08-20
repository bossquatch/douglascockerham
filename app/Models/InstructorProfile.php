<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstructorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'submitted_by_name', 'submitted_by_email', 'instructor_name',
        'agency', 'instructor_email', 'instructor_phone', 'county', 'is_test', 'review_status',
        'review_notes', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_test' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function capabilities(): HasMany
    {
        return $this->hasMany(InstructorCapability::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
