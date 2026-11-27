@extends('layouts.app')

@section('title', 'Student Violations')

@push('styles')
    <style>
        @media print {
            body { background: #fff !important; }
            .sidebar, .topbar, .no-print, .btn, .alert, .pagination { display: none !important; }
            .main-content { padding: 0 !important; }
            .card { border: 0 !important; box-shadow: none !important; }
            .badge, .table thead th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            a { color: inherit !important; text-decoration: none !important; }
            tr { page-break-inside: avoid; }
        }
    </style>
@endpush

@section('content')
    {{-- ===== Filters + actions ===== --}}
    <div class="card mb-3 no-print">
        <div class="card-body">
            <form method="GET" action="{{ route('violations.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="q" class="form-label small mb-1">Student</label>
                    <input type="search" id="q" name="q" value="{{ request('q') }}" class="form-control"
                           placeholder="Name or Student ID">
                </div>
                <div class="col-6 col-md-2">
                    <label for="course" class="form-label small mb-1">Course</label>
                    <select id="course" name="course" class="form-select">
                        <option value="">All</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course }}" @selected(request('course') === $course)>{{ $course }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="from" class="form-label small mb-1">Incident from</label>
                    <input type="date" id="from" name="from" value="{{ request('from') }}"
                           class="form-control @error('from') is-invalid @enderror">
                </div>
                <div class="col-6 col-md-2">
                    <label for="to" class="form-label small mb-1">Incident to</label>
                    <input type="date" id="to" name="to" value="{{ request('to') }}"
                           class="form-control @error('to') is-invalid @enderror">
                    @error('to')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-navy flex-grow-1">Filter</button>
                    <a href="{{ route('violations.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div class="text-muted">
            <strong style="color: var(--maroon-800)">{{ $totals->students }}</strong>
            {{ Str::plural('student', $totals->students) }} with
            <strong style="color: var(--maroon-800)">{{ $totals->violations }}</strong>
            official {{ Str::plural('violation', $totals->violations) }}
        </div>

        <div class="d-flex gap-2 no-print">
            <a href="{{ route('violations.print', $query) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> Print
            </a>
            <a href="{{ route('violations.export', $query) }}" class="btn btn-outline-secondary">
                <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </a>
        </div>
    </div>

    {{-- ===== Table ===== --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course / Year / Section</th>
                        <th class="text-center">Violations</th>
                        <th>What they were</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($student->photo_path)
                                        <img src="{{ route('students.photo', $student) }}" alt=""
                                             class="rounded-circle border" style="width: 38px; height: 38px; object-fit: cover;">
                                    @else
                                        <span class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center text-muted"
                                              style="width: 38px; height: 38px;"><i class="bi bi-person"></i></span>
                                    @endif
                                    <div>
                                        <a href="{{ route('students.show', $student) }}" class="fw-semibold text-decoration-none">{{ $student->full_name }}</a>
                                        <div class="small text-muted">{{ $student->student_number }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $student->course }} · {{ $student->year_level }}@if ($student->section) · {{ $student->section }}@endif</td>
                            <td class="text-center">
                                <span class="badge rounded-pill fs-6" style="background: var(--maroon-700)">{{ $student->violations_count }}</span>
                            </td>
                            <td>
                                @foreach ($breakdown->get($student->id, collect()) as $item)
                                    <span class="badge bg-light text-dark border me-1 mb-1">{{ $item->category }} ×{{ $item->total }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                No students with official violations match these filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white small text-muted">
            <i class="bi bi-info-circle me-1"></i>
            Only <strong>approved</strong> reports are counted as official violations.
        </div>
    </div>

    @if ($students->hasPages())
        <div class="mt-3 no-print">
            {{ $students->links('pagination::bootstrap-5') }}
        </div>
    @endif
@endsection