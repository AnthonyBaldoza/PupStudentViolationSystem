<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViolationReportEvidence extends Model
{
    protected $table = 'violation_report_evidence';

    protected $fillable = [
        'violation_report_id',
        'path',
        'original_name',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(ViolationReport::class, 'violation_report_id');
    }
}
