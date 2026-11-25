@extends('layouts.app')

@section('title', 'Report #' . $report->id)

@section('content')
    @php
        $ext = $report->evidence_path ? strtolower(pathinfo($report->evidence_path, PATHINFO_EXTENSION)) : null;
        $isImage = in_array($ext, ['jpg', 'jpeg', 'png'], true);
    @endphp

    <div class="row g-4">

        {{-- ===== Incident details + evidence ===== --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">Incident Details</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted fw-normal">Student</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('students.show', $report->student) }}" class="fw-semibold text-decoration-none">{{ $report->student->full_name }}</a>
                            <div class="small text-muted">
                                {{ $report->student->student_number }} · {{ $report->student->course }} · {{ $report->student->year_level }}@if ($report->student->section) · {{ $report->student->section }}@endif
                            </div>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">Violation category</dt>
                        <dd class="col-sm-8">{{ $report->category->name }}</dd>

                        <dt class="col-sm-4 text-muted fw-normal">Incident date &amp; time</dt>
                        <dd class="col-sm-8">{{ $report->incident_at->format('F d, Y \a\t h:i A') }}</dd>

                        <dt class="col-sm-4 text-muted fw-normal">Location</dt>
                        <dd class="col-sm-8">{{ $report->location }}</dd>

                        <dt class="col-sm-4 text-muted fw-normal">Description</dt>
                        <dd class="col-sm-8 mb-0">{!! nl2br(e($report->description)) !!}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Supporting Evidence</div>
                <div class="card-body">
                    @if (! $report->evidence_path)
                        <span class="text-muted">No evidence was attached to this report.</span>
                    @else
                        @if ($isImage)
                            <a href="{{ route('reports.evidence', $report) }}" target="_blank" rel="noopener">
                                <img src="{{ route('reports.evidence', $report) }}" alt="Evidence image"
                                     class="img-fluid rounded border mb-3" style="max-height: 480px">
                            </a>
                        @endif

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <i class="bi {{ $isImage ? 'bi-file-earmark-image' : 'bi-file-earmark-pdf' }} fs-4" style="color: var(--maroon-700)"></i>
                            <span class="me-2">{{ $report->evidence_original_name ?? 'evidence.' . $ext }}</span>
                            <a href="{{ route('reports.evidence', $report) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-box-arrow-up-right"></i> Open
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== Status / review info ===== --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Status</span>
                    <x-status-badge :status="$report->status" />
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">Report #</dt>
                        <dd class="col-7">{{ $report->id }}</dd>

                        <dt class="col-5 text-muted fw-normal">Reported by</dt>
                        <dd class="col-7">{{ $report->reporter->name }}</dd>

                        <dt class="col-5 text-muted fw-normal">Submitted</dt>
                        <dd class="col-7 {{ $report->isPending() ? 'mb-0' : '' }}">{{ $report->created_at->format('M d, Y h:i A') }}</dd>

                        @unless ($report->isPending())
                            <dt class="col-5 text-muted fw-normal">Reviewed by</dt>
                            <dd class="col-7">{{ $report->reviewer?->name ?? '—' }}</dd>

                            <dt class="col-5 text-muted fw-normal">Reviewed on</dt>
                            <dd class="col-7">{{ $report->reviewed_at?->format('M d, Y h:i A') ?? '—' }}</dd>

                            <dt class="col-5 text-muted fw-normal">Remarks</dt>
                            <dd class="col-7 mb-0">{!! $report->review_remarks ? nl2br(e($report->review_remarks)) : '—' !!}</dd>
                        @endunless
                    </dl>

                    @if ($report->isPending())
                        <div class="alert alert-warning small mt-3 mb-0">
                            <i class="bi bi-hourglass-split me-1"></i> Waiting for review by an Admin.
                            Only approved reports count as official violations.
                        </div>
                    @endif
                </div>
            </div>

            <a href="{{ route('reports.index') }}" class="btn btn-link px-0 mt-2">
                <i class="bi bi-arrow-left"></i> Back to reports
            </a>
        </div>
    </div>
@endsection