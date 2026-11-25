<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreViolationReportRequest;
use App\Models\Student;
use App\Models\ViolationCategory;
use App\Models\ViolationReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ViolationReportController extends Controller
{
    /** Admin sees all reports; an officer sees only their own. */
    public function index(Request $request): View
    {
        $user = $request->user();

        $status = $request->query('status');
        if (! in_array($status, ViolationReport::statuses(), true)) {
            $status = null;
        }

        $scope = fn () => ViolationReport::query()
            ->when($user->isOfficer(), fn ($q) => $q->where('reported_by', $user->id));

        // Numbers shown on the status tabs.
        $counts = $scope()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $reports = $scope()
            ->with(['student', 'category', 'reporter'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('reports.index', compact('reports', 'counts', 'status'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', ViolationReport::class);

        $categories = ViolationCategory::active()->orderBy('name')->get();

        // Pre-select a student when coming from a student's profile (?student_id=)
        // or when the form is redisplayed after a validation error.
        $studentId = old('student_id', $request->query('student_id'));
        $selectedStudent = ctype_digit((string) $studentId) ? Student::find($studentId) : null;

        return view('reports.create', compact('categories', 'selectedStudent'));
    }

    public function store(StoreViolationReportRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('evidence');

        $path = null;
        $originalName = null;

        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');

            // Private disk (storage/app/private): NOT reachable by URL. Random file name.
            $path = $file->store('evidence', 'local');

            // Keep a cleaned-up copy of the original name for display / download.
            $originalName = preg_replace(
                '/[^\p{L}\p{N}\s._()\-]/u',
                '_',
                mb_substr($file->getClientOriginalName(), 0, 200)
            );
        }

        try {
            $report = ViolationReport::create([
                ...$data,
                'reported_by' => $request->user()->id,
                'evidence_path' => $path,
                'evidence_original_name' => $originalName,
            ]);
        } catch (Throwable $e) {
            // Don't leave an orphan file behind if the database insert failed.
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Violation report submitted. It is now pending review.');
    }

    public function show(ViolationReport $report): View
    {
        Gate::authorize('view', $report);

        $report->load(['student', 'category', 'reporter', 'reviewer']);

        return view('reports.show', compact('report'));
    }

    /** Protected evidence file: only the report's officer or an admin can open it. */
    public function evidence(ViolationReport $report): StreamedResponse
    {
        Gate::authorize('view', $report);

        $disk = Storage::disk('local');

        abort_if(
            ! $report->evidence_path || ! $disk->exists($report->evidence_path),
            404,
            'No evidence file found for this report.'
        );

        return $disk->response(
            $report->evidence_path,
            $report->evidence_original_name,
            [
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }
}