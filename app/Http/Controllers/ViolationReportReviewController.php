<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewViolationReportRequest;
use App\Models\ViolationReport;
use App\Models\ViolationSanction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

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

        $updated = DB::transaction(function () use ($request, $report, $decision, $remarks) {
            $updated = ViolationReport::query()
                ->whereKey($report->id)
                ->where('status', ViolationReport::STATUS_PENDING)
                ->update([
                    'status' => $decision,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'review_remarks' => $remarks !== '' ? $remarks : null,
                ]);

            if ($updated > 0 && $decision === ViolationReport::STATUS_APPROVED) {
                $sanctionTitle = trim((string) $request->validated('sanction_title'));

                if ($sanctionTitle !== '') {
                    $report->sanction()->create([
                        'title' => $sanctionTitle,
                        'description' => trim((string) $request->validated('sanction_description')) ?: null,
                        'due_at' => $request->validated('sanction_due_at') ?: null,
                        'status' => ViolationSanction::STATUS_PENDING,
                    ]);
                }
            }

            return $updated;
        });

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
