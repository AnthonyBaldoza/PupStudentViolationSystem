<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'student_number',
        'full_name',
        'course',
        'year_level',
        'section',
    ];

    /** Every report filed against this student (any status). */
    public function reports(): HasMany
    {
        return $this->hasMany(ViolationReport::class);
    }

    /** Only approved reports - these are the official violations. */
    public function approvedReports(): HasMany
    {
        return $this->reports()->where('status', ViolationReport::STATUS_APPROVED);
    }

    /** Search by name or Student ID (case-insensitive, PostgreSQL ILIKE). */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        // Escape LIKE wildcards so user input is matched literally.
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('full_name', 'ilike', $like)
              ->orWhere('student_number', 'ilike', $like);
        });
    }
}
