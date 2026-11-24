<?php

namespace App\Http\Controllers;

use App\Models\ViolationReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        // Admin sees every report; an officer sees only the reports they submitted.
        $reports = fn () => ViolationReport::query()
            ->when($user->isOfficer(), fn ($q) => $q->where('reported_by', $user->id));

        $stats = [
            'total' => $reports()->count(),
            'pending' => $reports()->pending()->count(),
            'approved' => $reports()->approved()->count(),
            'rejected' => $reports()->rejected()->count(),
        ];

        $recent = $reports()
            ->with(['student', 'category', 'reporter'])
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard', compact('stats', 'recent'));
    }
}
