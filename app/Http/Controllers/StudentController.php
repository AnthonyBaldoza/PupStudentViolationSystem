<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Models\Student;
use App\Models\ViolationReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $students = Student::query()
            ->search($search)
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        return view('students.index', compact('students', 'search'));
    }

    /** JSON search used by the student picker on the report form. */
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $students = Student::query()
            ->search($term)
            ->orderBy('full_name')
            ->limit(10)
            ->get(['id', 'student_number', 'full_name', 'course', 'year_level', 'section']);

        return response()->json($students);
    }

    public function create(): View
    {
        return view('students.create', ['student' => new Student()]);
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $student = Student::create($request->safe()->except(['photo', 'remove_photo']));

        $this->savePhoto($request, $student);

        return redirect()
            ->route('students.show', $student)
            ->with('success', 'Student added successfully.');
    }

    public function show(Student $student): View
    {
        // Full history: every report filed against this student, newest incident first.
        $reports = $student->reports()
            ->with(['category', 'reporter'])
            ->orderByDesc('incident_at')
            ->get();

        // Only approved reports count as official violations.
        $approvedCount = $reports
            ->where('status', ViolationReport::STATUS_APPROVED)
            ->count();

        return view('students.show', compact('student', 'reports', 'approvedCount'));
    }

    public function edit(Student $student): View
    {
        return view('students.edit', compact('student'));
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $student->update($request->safe()->except(['photo', 'remove_photo']));

        $this->savePhoto($request, $student);

        return redirect()
            ->route('students.show', $student)
            ->with('success', 'Student information updated.');
    }

    /**
     * Protected student photo. Only logged-in, active admins/officers can open it
     * (route middleware). The file lives on the private disk, not under /public.
     */
    public function photo(Student $student): StreamedResponse
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_if(
            ! $student->photo_path || ! $disk->exists($student->photo_path),
            404,
            'This student has no photo.'
        );

        return $disk->response(
            $student->photo_path,
            null,
            [
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }

    /** Save a newly uploaded photo, or remove the current one if asked. */
    private function savePhoto(StudentRequest $request, Student $student): void
    {
        $disk = Storage::disk('local');

        if ($request->hasFile('photo')) {
            $newPath = $request->file('photo')->store('student-photos', 'local');
            $oldPath = $student->photo_path;

            // photo_path is not mass-assignable on purpose; set it explicitly here.
            $student->forceFill(['photo_path' => $newPath])->save();

            if ($oldPath) {
                $disk->delete($oldPath); // don't leave the replaced photo behind
            }

            return;
        }

        if ($request->boolean('remove_photo') && $student->photo_path) {
            $oldPath = $student->photo_path;

            $student->forceFill(['photo_path' => null])->save();
            $disk->delete($oldPath);
        }
    }
}