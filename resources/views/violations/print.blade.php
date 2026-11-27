<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Violations Report &middot; {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body { background: #fff; color: #000; }
        .report-title { color: var(--maroon-800); }
        .badge, .table thead th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        @media print {
            .no-print, .btn { display: none !important; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
            body { padding: 0 !important; }
        }
    </style>
</head>
<body class="p-4">

    <div class="no-print mb-3 d-flex gap-2">
        <button type="button" class="btn btn-navy" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print
        </button>
        <a href="{{ route('violations.index', $query) }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <header class="mb-3">
        <h1 class="h4 report-title mb-1">Student Violations Report</h1>
        <div class="small text-muted">
            Printed {{ now()->format('F d, Y h:i A') }} by {{ auth()->user()->name }}
        </div>
        <div class="small">
            <strong>Filters:</strong> {{ $filterSummary !== '' ? $filterSummary : 'All students' }}
        </div>
        <div class="small">
            <strong>{{ $totals->students }}</strong> {{ Str::plural('student', $totals->students) }} with
            <strong>{{ $totals->violations }}</strong> official {{ Str::plural('violation', $totals->violations) }}
        </div>
    </header>

    <table class="table table-sm table-bordered align-middle">
        <thead>
            <tr>
                <th style="width: 3rem">#</th>
                <th>Student ID</th>
                <th>Full Name</th>
                <th>Course / Year / Section</th>
                <th class="text-center">Violations</th>
                <th>What they were</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $student->student_number }}</td>
                    <td class="fw-semibold">{{ $student->full_name }}</td>
                    <td>{{ $student->course }} · {{ $student->year_level }}@if ($student->section) · {{ $student->section }}@endif</td>
                    <td class="text-center fw-bold">{{ $student->violations_count }}</td>
                    <td>
                        {{ $breakdown->get($student->id, collect())->map(fn ($b) => $b->category . ' ×' . $b->total)->implode(', ') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        No students with official violations match these filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="small text-muted mb-0">
        Only approved reports are counted as official violations. Pending and rejected reports are not included.
    </p>

    <script>
        // Open the print dialog automatically once the page has loaded.
        window.addEventListener('load', function () { window.print(); });
    </script>
</body>
</html>