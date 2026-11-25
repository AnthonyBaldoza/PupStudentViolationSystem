<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ViolationReport;

class ViolationReportPolicy
{
    /**
     * Admin can view every report. An officer can only view reports they submitted.
     * This also guards the evidence file download.
     */
    public function view(User $user, ViolationReport $report): bool
    {
        return $user->isAdmin() || (int) $report->reported_by === (int) $user->id;
    }

    /** Only officers file reports. */
    public function create(User $user): bool
    {
        return $user->isOfficer();
    }
}