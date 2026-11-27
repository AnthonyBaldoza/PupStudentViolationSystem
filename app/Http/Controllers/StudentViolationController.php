<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ViolationReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin-only summary: every student with at least one OFFICIAL (approved) violation,
 * how many they have, and what they were. Pending and rejected reports are never counted.
 */
class StudentViolationController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $f = $this->filters($request);

        $students = $this->listQuery($f)->paginate(self::PER_PAGE)->withQueryString();
        $breakdown = $this->breakdown($f, $students->getCollection()->pluck('id')->all());
        $totals = $this->totals($f);
        $query = Arr::except($request->query(), 'page');

        $courses = Student::query()
            ->whereNotNull('course')
            ->distinct()
            ->orderBy('course')
            ->pluck('course');

        return view('violations.index', compact('students', 'breakdown', 'totals', 'courses', 'query'));
    }

    /** Print-friendly page: ALL matching students (no pagination). */
    public function printable(Request $request): View
    {
        $f = $this->filters($request);

        $students = $this->listQuery($f)->get();
        $breakdown = $this->breakdown($f);
        $totals = $this->totals($f);
        $query = Arr::except($request->query(), 'page');

        $filterSummary = collect([
            $f['q'] !== '' ? 'Search: ' . $f['q'] : null,
            $f['course'] ? 'Course: ' . $f['course'] : null,
            $f['from'] ? 'From: ' . $f['from']->format('M d, Y') : null,
            $f['to'] ? 'To: ' . $f['to']->format('M d, Y') : null,
        ])->filter()->implode(' · ');

        return view('violations.print', compact('students', 'breakdown', 'totals', 'query', 'filterSummary'));
    }

    public function export(Request $request): StreamedResponse
    {
        $f = $this->filters($request);

        $students = $this->listQuery($f)->get();
        $breakdown = $this->breakdown($f);

        $filename = 'student-violations-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($students, $breakdown) {
            $out = fopen('php://output', 'w');

            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows names correctly

            fputcsv($out, [
                'Student ID', 'Full Name', 'Course', 'Year Level', 'Section',
                'Official Violations', 'Violations',
            ], ',', '"', '');

            foreach ($students as $student) {
                $details = $breakdown->get($student->id, collect())
                    ->map(fn ($b) => $b->category . ' x' . $b->total)
                    ->implode('; ');

                $row = [
                    $student->student_number,
                    $student->full_name,
                    $student->course,
                    $student->year_level,
                    $student->section,
                    $student->violations_count,
                    $details,
                ];

                fputcsv($out, array_map(fn ($v) => $this->csvSafe($v), $row), ',', '"', '');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ------------------------------------------------------------------

    /** @return array{q: string, course: ?string, from: ?Carbon, to: ?Carbon} */
    private function filters(Request $request): array
    {
        $v = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'course' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return [
            'q' => trim((string) ($v['q'] ?? '')),
            'course' => ($v['course'] ?? '') !== '' ? $v['course'] : null,
            'from' => ! empty($v['from']) ? Carbon::parse($v['from'])->startOfDay() : null,
            'to' => ! empty($v['to']) ? Carbon::parse($v['to'])->endOfDay() : null,
        ];
    }

    /** Students joined to their APPROVED reports, with the filters applied. */
    private function baseQuery(array $f): Builder
    {
        return Student::query()
            ->search($f['q'])
            ->when($f['course'], fn ($q, $course) => $q->where('students.course', $course))
            ->join('violation_reports as r', 'r.student_id', '=', 'students.id')
            ->where('r.status', ViolationReport::STATUS_APPROVED)
            ->when($f['from'], fn ($q, $d) => $q->where('r.incident_at', '>=', $d))
            ->when($f['to'], fn ($q, $d) => $q->where('r.incident_at', '<=', $d));
    }

    /** One row per student with violations_count, most violations first. */
    private function listQuery(array $f): Builder
    {
        return $this->baseQuery($f)
            ->select('students.*')
            ->selectRaw('count(r.id) as violations_count')
            ->groupBy('students.id')
            ->orderByDesc('violations_count')
            ->orderBy('students.full_name');
    }

    private function totals(array $f): object
    {
        $row = $this->baseQuery($f)
            ->selectRaw('count(r.id) as violations, count(distinct students.id) as students')
            ->toBase()
            ->first();

        return (object) [
            'violations' => (int) ($row->violations ?? 0),
            'students' => (int) ($row->students ?? 0),
        ];
    }

    /**
     * "What they were": per student, the count of approved violations per category.
     * Pass student IDs to limit it to the current page; null = everyone.
     */
    private function breakdown(array $f, ?array $studentIds = null): Collection
    {
        return $this->baseQuery($f)
            ->join('violation_categories as c', 'c.id', '=', 'r.violation_category_id')
            ->when($studentIds !== null, fn ($q) => $q->whereIn('students.id', $studentIds))
            ->selectRaw('students.id as student_id, c.name as category, count(r.id) as total')
            ->groupBy('students.id', 'c.id', 'c.name')
            ->orderByDesc('total')
            ->orderBy('c.name')
            ->toBase()
            ->get()
            ->groupBy('student_id');
    }

    /** Stop spreadsheet formula injection: a cell starting with = + - @ is treated as text. */
    private function csvSafe(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}