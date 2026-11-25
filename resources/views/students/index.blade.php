@extends('layouts.app')

@section('title', 'Students')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
        <form method="GET" action="{{ route('students.index') }}" class="flex-grow-1" style="max-width: 520px" role="search">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="search" name="q" value="{{ $search }}" class="form-control"
                       placeholder="Search by name or Student ID" aria-label="Search students">
                <button class="btn btn-navy" type="submit">Search</button>
                @if ($search !== '')
                    <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">Clear</a>
                @endif
            </div>
        </form>

        <a href="{{ route('students.create') }}" class="btn btn-navy">
            <i class="bi bi-plus-lg me-1"></i> Add Student
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Section</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td class="fw-semibold">{{ $student->student_number }}</td>
                            <td>{{ $student->full_name }}</td>
                            <td>{{ $student->course }}</td>
                            <td>{{ $student->year_level }}</td>
                            <td>{{ $student->section ?? '—' }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('students.show', $student) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                @if ($search !== '')
                                    No students match "{{ $search }}".
                                @else
                                    No students yet. Click "Add Student" to create the first record.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($students->total() > 0)
            <div class="card-footer bg-white small text-muted">
                Showing {{ $students->firstItem() }}–{{ $students->lastItem() }} of {{ $students->total() }} students
            </div>
        @endif
    </div>

    @if ($students->hasPages())
        <div class="mt-3">
            {{ $students->links('pagination::bootstrap-5') }}
        </div>
    @endif
@endsection