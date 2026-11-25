<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Models\Student;
use App\Models\ViolationReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

    public function create(): View
    {
        return view('students.create', ['student' => new Student()]);
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $student = Student::create($request->validated());

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
        $student->update($request->validated());

        return redirect()
            ->route('students.show', $student)
            ->with('success', 'Student information updated.');
    }
}