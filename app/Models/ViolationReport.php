<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViolationReport extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'student_id',
        'violation_category_id',
        'reported_by',
        'incident_at',
        'location',
        'description',
        'evidence_path',
        'evidence_original_name',
    ];

    // status, reviewed_by, reviewed_at and review_remarks are intentionally NOT
    // mass-assignable: only the admin review action may set them.

    protected function casts(): array
    {
        return [
            'incident_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return array<int, string> */
    public static function statuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ViolationCategory::class, 'violation_category_id');
    }

    /** The officer who submitted the report. */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /** The admin who approved or rejected the report. */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
