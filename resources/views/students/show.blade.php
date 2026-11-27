@extends('layouts.app')

@section('title', 'Student Profile')

@push('styles')
    <style>
        @media print {
            body { background: #fff !important; }
            .sidebar, .topbar, .btn, .alert { display: none !important; }
            .main-content { padding: 0 !important; }
            .card { border: 0 !important; box-shadow: none !important; }
            .badge, .table thead th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            a { color: inherit !important; text-decoration: none !important; }
            tr { page-break-inside: avoid; }
        }
    </style>
@endpush

@section('content')
    <div class="d-none d-print-block mb-3">
        <h1 class="h4 mb-0" style="color: var(--maroon-800)">Student Violation History</h1>
        <div class="small text-muted">Printed {{ now()->format('F d, Y h:i A') }} by {{ auth()->user()->name }}</div>
    </div>

    <div class="row g-4">

        {{-- ===== Student details ===== --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Student Information</span>
                    <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if ($student->photo_path)
                            <a href="{{ route('students.photo', $student) }}" target="_blank" rel="noopener">
                                <img src="{{ route('students.photo', $student) }}" alt="Photo of {{ $student->full_name }}"
                                     class="rounded border" style="width: 180px; height: 180px; object-fit: cover;">
                            </a>
                        @else
                            <div class="rounded border bg-light d-inline-flex flex-column align-items-center justify-content-center text-muted"
                                 style="width: 180px; height: 180px;">
                                <i class="bi bi-person fs-1"></i>
                                <span class="small">No photo</span>
                            </div>
                        @endif
                    </div>

                    <h2 class="h5 mb-1" style="color: var(--navy-800)">{{ $student->full_name }}</h2>
                    <p class="text-muted mb-3">{{ $student->student_number }}</p>

                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">Course</dt>
                        <dd class="col-7">{{ $student->course }}</dd>

                        <dt class="col-5 text-muted fw-normal">Year level</dt>
                        <dd class="col-7">{{ $student->year_level }}</dd>

                        <dt class="col-5 text-muted fw-normal">Section</dt>
                        <dd class="col-7 mb-0">{{ $student->section ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="row g-3 mt-0">
                <div class="col-6">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-value">{{ $approvedCount }}</div>
                            <div class="stat-label">Official violations</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="stat-value">{{ $reports->count() }}</div>
                            <div class="stat-label">Total reports filed</div>
                        </div>
                    </div>
                </div>
            </div>

            @if (Route::has('reports.create') && auth()->user()->isOfficer())
                <a href="{{ route('reports.create', ['student_id' => $student->id]) }}" class="btn btn-navy w-100 mt-3">
                    <i class="bi bi-plus-circle me-1"></i> Report a Violation
                </a>
            @endif

            <button type="button" onclick="window.print()" class="btn btn-outline-secondary w-100 mt-2">
                <i class="bi bi-printer me-1"></i> Print History
            </button>

            <a href="{{ route('students.index') }}" class="btn btn-link px-0 mt-2">
                <i class="bi bi-arrow-left"></i> Back to students
            </a>
        </div>

        {{-- ===== Violation history ===== --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Violation History</div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Incident Date</th>
                                <th>Status</th>
                                <th>Reported By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reports as $report)
                                <tr class="{{ $report->isApproved() ? '' : 'text-muted' }}">
                                    <td>
                                        @if (Route::has('reports.show'))
                                            <a href="{{ route('reports.show', $report) }}" class="text-decoration-none">{{ $report->category->name }}</a>
                                        @else
                                            {{ $report->category->name }}
                                        @endif
                                    </td>
                                    <td>{{ $report->incident_at->format('M d, Y h:i A') }}</td>
                                    <td><x-status-badge :status="$report->status" /></td>
                                    <td>{{ $report->reporter->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        No violation reports for this student.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white small text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Only <strong>approved</strong> reports count as official violations. Pending and rejected reports are shown for reference.
                </div>
            </div>
        </div>
    </div>
@endsection