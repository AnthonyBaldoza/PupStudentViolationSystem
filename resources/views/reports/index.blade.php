@extends('layouts.app')

@section('title', auth()->user()->isAdmin() ? 'All Reports' : 'My Reports')

@section('content')
    @php
        $isAdmin = auth()->user()->isAdmin();
        $tabs = [
            ''         => ['All',      $counts->sum()],
            'pending'  => ['Pending',  $counts->get('pending', 0)],
            'approved' => ['Approved', $counts->get('approved', 0)],
            'rejected' => ['Rejected', $counts->get('rejected', 0)],
        ];
    @endphp

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
        <ul class="nav nav-pills gap-1" style="--bs-nav-pills-link-active-bg: var(--maroon-700); --bs-nav-link-color: var(--maroon-700); --bs-nav-link-hover-color: var(--maroon-900);">
            @foreach ($tabs as $key => [$label, $total])
                <li class="nav-item">
                    <a class="nav-link {{ ($status ?? '') === $key ? 'active' : '' }}"
                       href="{{ route('reports.index', $key === '' ? [] : ['status' => $key]) }}">
                        {{ $label }}
                        <span class="badge rounded-pill bg-light text-dark ms-1">{{ $total }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        @if (! $isAdmin)
            <a href="{{ route('reports.create') }}" class="btn btn-navy text-nowrap">
                <i class="bi bi-plus-lg me-1"></i> New Report
            </a>
        @endif
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Category</th>
                        <th>Incident Date</th>
                        @if ($isAdmin)
                            <th>Reported By</th>
                        @endif
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr>
                            <td class="text-muted">{{ $report->id }}</td>
                            <td>
                                <a href="{{ route('students.show', $report->student) }}" class="fw-semibold text-decoration-none">{{ $report->student->full_name }}</a>
                                <div class="small text-muted">{{ $report->student->student_number }}</div>
                            </td>
                            <td>{{ $report->category->name }}</td>
                            <td>{{ $report->incident_at->format('M d, Y h:i A') }}</td>
                            @if ($isAdmin)
                                <td>{{ $report->reporter->name }}</td>
                            @endif
                            <td><x-status-badge :status="$report->status" /></td>
                            <td class="text-end text-nowrap">
                                @if ($report->isPending() && ! $isAdmin && $report->reported_by === auth()->id())
                                    <a href="{{ route('reports.edit', $report) }}" class="btn btn-sm btn-outline-secondary me-1">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                @endif
                                <a href="{{ route('reports.show', $report) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 7 : 6 }}" class="text-center text-muted py-5">
                                @if ($status)
                                    No {{ $status }} reports.
                                @elseif ($isAdmin)
                                    No reports have been submitted yet.
                                @else
                                    You haven't submitted any reports yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reports->total() > 0)
            <div class="card-footer bg-white small text-muted">
                Showing {{ $reports->firstItem() }}–{{ $reports->lastItem() }} of {{ $reports->total() }} reports
            </div>
        @endif
    </div>

    @if ($reports->hasPages())
        <div class="mt-3">
            {{ $reports->links('pagination::bootstrap-5') }}
        </div>
    @endif
@endsection