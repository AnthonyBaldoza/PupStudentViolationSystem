@extends('layouts.app')

@section('title', 'Edit Pending Report #' . $report->id)

@section('content')
    <div class="card" style="max-width: 860px">
        <div class="card-header">Report Details</div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('reports.update', $report) }}" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PATCH')

                <input type="hidden" name="student_id" value="{{ old('student_id', $report->student_id) }}">
                <div class="mb-3">
                    <label class="form-label">Student</label>
                    <div class="border rounded bg-light px-3 py-2">
                        <div class="fw-semibold">{{ $report->student->full_name }}</div>
                        <div class="small text-muted">{{ $report->student->student_number }}</div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="violation_category_id" class="form-label">Violation category <span class="text-danger">*</span></label>
                        <select id="violation_category_id" name="violation_category_id" class="form-select @error('violation_category_id') is-invalid @enderror" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('violation_category_id', $report->violation_category_id) === (string) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('violation_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="incident_at" class="form-label">Incident date and time <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="incident_at" name="incident_at"
                               value="{{ old('incident_at', $report->incident_at->format('Y-m-d\TH:i')) }}"
                               max="{{ now()->format('Y-m-d\TH:i') }}"
                               class="form-control @error('incident_at') is-invalid @enderror" required>
                        @error('incident_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                        <input type="text" id="location" name="location" value="{{ old('location', $report->location) }}"
                               class="form-control @error('location') is-invalid @enderror" required maxlength="255">
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea id="description" name="description" rows="5" maxlength="5000"
                                  class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $report->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Current evidence</label>
                        @forelse ($report->evidence as $evidence)
                            <div class="d-flex align-items-center gap-2 border rounded px-3 py-2 mb-2">
                                <span class="flex-grow-1">{{ $evidence->original_name }}</span>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_evidence[]" value="{{ $evidence->id }}" id="remove-{{ $evidence->id }}">
                                    <label class="form-check-label text-danger" for="remove-{{ $evidence->id }}">Remove</label>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted small">No evidence is currently attached.</div>
                        @endforelse
                    </div>

                    <div class="col-12">
                        <label for="evidence" class="form-label">Add evidence</label>
                        <input type="file" id="evidence" name="evidence[]" accept=".jpg,.jpeg,.png,.pdf" multiple
                               class="form-control @error('evidence') is-invalid @enderror">
                        <div class="form-text">Up to 5 evidence files total, 5 MB each.</div>
                        @error('evidence')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-navy">Save Changes</button>
                    <a href="{{ route('reports.show', $report) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
