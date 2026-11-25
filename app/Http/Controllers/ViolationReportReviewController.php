<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewViolationReportRequest;
use App\Models\ViolationReport;
use Illuminate\Http\RedirectResponse;

class ViolationReportReviewController extends Controller
{
    /**
     * Approve or reject a pending report.
     *
     * The update only matches rows that are STILL pending, so two admins can't
     * overwrite each other's decision and a reviewed report can't be re-reviewed.
     * (status / reviewed_by / reviewed_at / review_remarks are not mass-assignable
     * on the model, so this query-level update is the only place they get set.)
     */
    public function __invoke(ReviewViolationReportRequest $request, ViolationReport $report): RedirectResponse
    {
        $decision = $request->validated('decision');
        $remarks = trim((string) $request->validated('remarks'));

        $updated = ViolationReport::query()
            ->whereKey($report->id)
            ->where('status', ViolationReport::STATUS_PENDING)
            ->update([
                'status' => $decision,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_remarks' => $remarks !== '' ? $remarks : null,
            ]);

        if ($updated === 0) {
            return redirect()
                ->route('reports.show', $report)
                ->with('error', 'This report has already been reviewed.');
        }

        return redirect()
            ->route('reports.show', $report)
            ->with('success', $decision === ViolationReport::STATUS_APPROVED
                ? 'Report approved. It now counts as an official violation.'
                : 'Report rejected.');
    }
}