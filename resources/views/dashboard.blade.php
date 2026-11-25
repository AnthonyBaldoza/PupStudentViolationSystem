@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $isAdmin = auth()->user()->isAdmin();
        $cards = [
            ['label' => 'Total Reports', 'value' => $stats['total'],    'icon' => 'bi-journal-text'],
            ['label' => 'Pending',       'value' => $stats['pending'],  'icon' => 'bi-hourglass-split'],
            ['label' => 'Approved',      'value' => $stats['approved'], 'icon' => 'bi-check-circle'],
            ['label' => 'Rejected',      'value' => $stats['rejected'], 'icon' => 'bi-x-circle'],
        ];
    @endphp

    <p class="text-muted mb-4">
        {{ $isAdmin ? 'Overview of all submitted violation reports.' : 'Overview of the reports you submitted.' }}
    </p>

    <div class="row g-3 mb-4">
        @foreach ($cards as $card)
            <div class="col-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon"><i class="bi {{ $card['icon'] }}"></i></div>
                        <div>
                            <div class="stat-value">{{ number_format($card['value']) }}</div>
                            <div class="stat-label">{{ $card['label'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Recent Violation Reports</span>
            @if (Route::has('reports.index'))
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">View all</a>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Category</th>
                        <th>Incident Date</th>
                        @if ($isAdmin)
                            <th>Reported By</th>
                        @endif
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recent as $report)
                        <tr>
                            <td>
                                <a href="{{ route('reports.show', $report) }}" class="fw-semibold text-decoration-none">{{ $report->student->full_name }}</a>
                                <div class="small text-muted">{{ $report->student->student_number }}</div>
                            </td>
                            <td>{{ $report->category->name }}</td>
                            <td>{{ $report->incident_at->format('M d, Y h:i A') }}</td>
                            @if ($isAdmin)
                                <td>{{ $report->reporter->name }}</td>
                            @endif
                            <td><x-status-badge :status="$report->status" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 5 : 4 }}" class="text-center text-muted py-5">
                                No violation reports yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
