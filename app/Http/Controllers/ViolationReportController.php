<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreViolationReportRequest;
use App\Http\Requests\UpdateViolationReportRequest;
use App\Models\Student;
use App\Models\ViolationCategory;
use App\Models\ViolationReport;
use App\Models\ViolationReportEvidence;
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
        $uploadedFiles = $request->file('evidence', []);
        $storedEvidence = [];

        try {
            $report = ViolationReport::create([
                ...$data,
                'reported_by' => $request->user()->id,
            ]);

            foreach ($uploadedFiles as $file) {
                $path = $file->store('evidence', 'local');
                $storedEvidence[] = $path;

                $report->evidence()->create([
                    'path' => $path,
                    'original_name' => $this->cleanEvidenceName($file->getClientOriginalName()),
                ]);
            }
        } catch (Throwable $e) {
            foreach ($storedEvidence as $path) {
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

        $report->load(['student', 'category', 'reporter', 'reviewer', 'evidence', 'sanction']);

        return view('reports.show', compact('report'));
    }

    public function edit(ViolationReport $report): View
    {
        Gate::authorize('update', $report);

        $categories = ViolationCategory::active()->orderBy('name')->get();
        $report->load(['student', 'evidence']);

        return view('reports.edit', compact('report', 'categories'));
    }

    public function update(UpdateViolationReportRequest $request, ViolationReport $report): RedirectResponse
    {
        $data = $request->safe()->except(['evidence', 'remove_evidence']);
        $storedEvidence = [];

        try {
            $report->update($data);

            $removeIds = $request->validated('remove_evidence') ?? [];
            $evidenceToRemove = $report->evidence()
                ->whereIn('id', $removeIds)
                ->get();

            foreach ($evidenceToRemove as $evidence) {
                Storage::disk('local')->delete($evidence->path);
                $evidence->delete();
            }

            $existingCount = $report->evidence()->count();
            $uploadedFiles = $request->file('evidence', []);

            foreach ($uploadedFiles as $file) {
                if ($existingCount >= 5) {
                    break;
                }

                $path = $file->store('evidence', 'local');
                $storedEvidence[] = $path;

                $report->evidence()->create([
                    'path' => $path,
                    'original_name' => $this->cleanEvidenceName($file->getClientOriginalName()),
                ]);
                $existingCount++;
            }
        } catch (Throwable $e) {
            foreach ($storedEvidence as $path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Pending report updated successfully.');
    }

    /** Protected evidence file: only the report's officer or an admin can open it. */
    public function evidence(ViolationReport $report, ?ViolationReportEvidence $evidence = null): StreamedResponse
    {
        Gate::authorize('view', $report);

        $disk = Storage::disk('local');

        if ($evidence) {
            abort_if((int) $evidence->violation_report_id !== (int) $report->id, 404);
            $path = $evidence->path;
            $originalName = $evidence->original_name;
        } else {
            $legacyEvidence = $report->evidence()->oldest()->first();
            $path = $legacyEvidence?->path ?? $report->evidence_path;
            $originalName = $legacyEvidence?->original_name ?? $report->evidence_original_name;
        }

        abort_if(
            ! $path || ! $disk->exists($path),
            404,
            'No evidence file found for this report.'
        );

        return $disk->response(
            $path,
            $originalName,
            [
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }

    private function cleanEvidenceName(string $name): string
    {
        return (string) preg_replace(
            '/[^\p{L}\p{N}\s._()\-]/u',
            '_',
            mb_substr($name, 0, 200)
        );
    }
}
