@extends('layouts.app')

@section('title', 'Report #' . $report->id)

@section('content')
    <div class="row g-4">

        {{-- ===== Incident details + evidence ===== --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">Incident Details</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted fw-normal">Student</dt>
                        <dd class="col-sm-8">
                            <div class="d-flex align-items-center gap-3">
                                @if ($report->student->photo_path)
                                    <a href="{{ route('students.photo', $report->student) }}" target="_blank" rel="noopener">
                                        <img src="{{ route('students.photo', $report->student) }}" alt="Photo of {{ $report->student->full_name }}"
                                             class="rounded border" style="width: 84px; height: 84px; object-fit: cover;">
                                    </a>
                                @endif
                                <div>
                                    <a href="{{ route('students.show', $report->student) }}" class="fw-semibold text-decoration-none">{{ $report->student->full_name }}</a>
                                    <div class="small text-muted">
                                        {{ $report->student->student_number }} · {{ $report->student->course }} · {{ $report->student->year_level }}@if ($report->student->section) · {{ $report->student->section }}@endif
                                    </div>
                                </div>
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
                    @if ($report->evidence->isEmpty())
                        <span class="text-muted">No evidence was attached to this report.</span>
                    @else
                        @foreach ($report->evidence as $evidence)
                            @php
                                $ext = strtolower(pathinfo($evidence->path, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png'], true);
                            @endphp
                            @if ($isImage)
                                <a href="{{ route('reports.evidence.show', [$report, $evidence]) }}" target="_blank" rel="noopener">
                                    <img src="{{ route('reports.evidence.show', [$report, $evidence]) }}" alt="Evidence image"
                                         class="img-fluid rounded border mb-3 d-block" style="max-height: 480px">
                                </a>
                            @endif
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                <i class="bi {{ $isImage ? 'bi-file-earmark-image' : 'bi-file-earmark-pdf' }} fs-5" style="color: var(--maroon-700)"></i>
                                <span class="me-2">{{ $evidence->original_name }}</span>
                                <a href="{{ route('reports.evidence.show', [$report, $evidence]) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-box-arrow-up-right"></i> Open
                                </a>
                            </div>
                        @endforeach
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

                    @if ($report->isPending() && ! auth()->user()->isAdmin())
                        <div class="alert alert-warning small mt-3 mb-0">
                            <i class="bi bi-hourglass-split me-1"></i> Waiting for review by an Admin.
                            Only approved reports count as official violations.
                        </div>
                        <a href="{{ route('reports.edit', $report) }}" class="btn btn-outline-secondary w-100 mt-3">
                            <i class="bi bi-pencil me-1"></i> Edit Pending Report
                        </a>
                    @endif
                </div>
            </div>

            @if ($report->sanction)
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Sanction / Action Plan</span>
                        <span class="badge {{ $report->sanction->isCompleted() ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ ucfirst($report->sanction->status) }}
                        </span>
                    </div>
                    <div class="card-body small">
                        <dl class="row mb-0">
                            <dt class="col-4 text-muted fw-normal">Action</dt>
                            <dd class="col-8">{{ $report->sanction->title }}</dd>
                            <dt class="col-4 text-muted fw-normal">Details</dt>
                            <dd class="col-8">{{ $report->sanction->description ?: '—' }}</dd>
                            <dt class="col-4 text-muted fw-normal">Due date</dt>
                            <dd class="col-8 {{ $report->sanction->isCompleted() ? '' : 'mb-0' }}">
                                {{ $report->sanction->due_at?->format('M d, Y') ?? 'No deadline set' }}
                            </dd>
                            @if ($report->sanction->isCompleted())
                                <dt class="col-4 text-muted fw-normal">Completed</dt>
                                <dd class="col-8 mb-0">{{ $report->sanction->completed_at?->format('M d, Y h:i A') }}</dd>
                            @endif
                        </dl>
                        @if (auth()->user()->isAdmin() && ! $report->sanction->isCompleted())
                            <form method="POST" action="{{ route('sanctions.complete', $report->sanction) }}" class="mt-3">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm w-100"
                                        onclick="return confirm('Mark this sanction as completed?');">
                                    <i class="bi bi-check-circle me-1"></i> Mark Completed
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ===== Admin review (only for pending reports) ===== --}}
            @if (auth()->user()->isAdmin() && $report->isPending())
                <div class="card mt-3">
                    <div class="card-header">Review This Report</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('reports.review', $report) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label for="remarks" class="form-label">Remarks</label>
                                <textarea id="remarks" name="remarks" rows="4" maxlength="2000"
                                          class="form-control @error('remarks') is-invalid @enderror"
                                          placeholder="Required when rejecting. Optional when approving.">{{ old('remarks') }}</textarea>
                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('decision')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror

                                <div class="border-top pt-3 mt-3">
                                    <p class="small fw-semibold mb-2">Optional sanction / action plan</p>
                                    <div class="mb-2">
                                        <label for="sanction_title" class="form-label small">Action</label>
                                        <input type="text" id="sanction_title" name="sanction_title" maxlength="150"
                                               value="{{ old('sanction_title') }}"
                                               class="form-control form-control-sm @error('sanction_title') is-invalid @enderror"
                                               placeholder="e.g. Community service">
                                        @error('sanction_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-2">
                                        <label for="sanction_description" class="form-label small">Details</label>
                                        <textarea id="sanction_description" name="sanction_description" rows="2" maxlength="2000"
                                                  class="form-control form-control-sm">{{ old('sanction_description') }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="sanction_due_at" class="form-label small">Due date</label>
                                        <input type="date" id="sanction_due_at" name="sanction_due_at"
                                               value="{{ old('sanction_due_at') }}"
                                               min="{{ now()->format('Y-m-d') }}"
                                               class="form-control form-control-sm @error('sanction_due_at') is-invalid @enderror">
                                        @error('sanction_due_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="decision" value="approved" class="btn btn-success"
                                        onclick="return confirm('Approve this report? It will count as an official violation.');">
                                    <i class="bi bi-check-circle me-1"></i> Approve
                                </button>
                                <button type="submit" name="decision" value="rejected" class="btn btn-outline-danger"
                                        onclick="return confirm('Reject this report?');">
                                    <i class="bi bi-x-circle me-1"></i> Reject
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <a href="{{ route('reports.index') }}" class="btn btn-link px-0 mt-2">
                <i class="bi bi-arrow-left"></i> Back to reports
            </a>
        </div>
    </div>
@endsection