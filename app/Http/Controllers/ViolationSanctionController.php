<?php

namespace App\Http\Controllers;

use App\Models\ViolationSanction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ViolationSanctionController extends Controller
{
    public function complete(Request $request, ViolationSanction $sanction): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        if (! $sanction->isCompleted()) {
            $sanction->update([
                'status' => ViolationSanction::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        }

        return redirect()
            ->route('reports.show', $sanction->violation_report_id)
            ->with('success', 'Sanction marked as completed.');
    }
}
