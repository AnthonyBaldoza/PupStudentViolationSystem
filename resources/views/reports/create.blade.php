@extends('layouts.app')

@section('title', 'New Violation Report')

@section('content')
    <div class="card" style="max-width: 860px">
        <div class="card-header">Report Details</div>
        <div class="card-body p-4">

            @if ($categories->isEmpty())
                <div class="alert alert-warning">
                    There are no active violation categories yet. Ask the Admin to add them
                    (Violation Categories) before filing a report.
                </div>
            @endif

            <form method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data" novalidate>
                @csrf

                {{-- ===== Student picker (logic in public/js/student-picker.js) ===== --}}
                <div class="mb-3" id="student-picker" data-search-url="{{ route('students.search') }}">
                    <label for="student-search" class="form-label">Student <span class="text-danger">*</span></label>

                    <input type="hidden" name="student_id" id="student_id" value="{{ $selectedStudent?->id }}">

                    <div id="student-picked"
                         class="d-flex justify-content-between align-items-center border rounded bg-light px-3 py-2 {{ $selectedStudent ? '' : 'd-none' }}">
                        <div>
                            <div class="fw-semibold" id="student-picked-name">{{ $selectedStudent?->full_name }}</div>
                            <div class="small text-muted" id="student-picked-meta">@if ($selectedStudent){{ $selectedStudent->student_number }} · {{ $selectedStudent->course }} · {{ $selectedStudent->year_level }}@if ($selectedStudent->section) · {{ $selectedStudent->section }}@endif @endif</div>
                        </div>
                        <button type="button" id="student-clear" class="btn btn-sm btn-outline-secondary">Change</button>
                    </div>

                    <div id="student-search-wrap" class="position-relative {{ $selectedStudent ? 'd-none' : '' }}">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="search" id="student-search" autocomplete="off"
                                   class="form-control @error('student_id') is-invalid @enderror"
                                   placeholder="Type at least 2 letters of the name or Student ID">
                        </div>
                        <div id="student-results" class="list-group position-absolute w-100 shadow-sm mt-1" style="z-index: 1050"></div>
                        <div id="student-status" class="form-text"></div>
                    </div>

                    @error('student_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Student not listed? <a href="{{ route('students.create') }}">Add the student first</a>.
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="violation_category_id" class="form-label">Violation category <span class="text-danger">*</span></label>
                        <select id="violation_category_id" name="violation_category_id"
                                class="form-select @error('violation_category_id') is-invalid @enderror" required>
                            <option value="">Select a category…</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('violation_category_id') === (string) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('violation_category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="incident_at" class="form-label">Incident date and time <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="incident_at" name="incident_at"
                               value="{{ old('incident_at', now()->format('Y-m-d\TH:i')) }}"
                               max="{{ now()->format('Y-m-d\TH:i') }}"
                               class="form-control @error('incident_at') is-invalid @enderror" required>
                        @error('incident_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                        <input type="text" id="location" name="location" value="{{ old('location') }}"
                               placeholder="e.g. Main gate, Room 204, Canteen"
                               class="form-control @error('location') is-invalid @enderror" required maxlength="255">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label">Description of the incident <span class="text-danger">*</span></label>
                        <textarea id="description" name="description" rows="5" maxlength="5000"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Describe what happened, who was involved, and any other relevant details." required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="evidence" class="form-label">Supporting evidence <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="file" id="evidence" name="evidence" accept=".jpg,.jpeg,.png,.pdf"
                               class="form-control @error('evidence') is-invalid @enderror">
                        @error('evidence')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">JPG, PNG or PDF, up to 5 MB. Only the Admin and you can open it.</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-navy" @disabled($categories->isEmpty())>
                        <i class="bi bi-send me-1"></i> Submit Report
                    </button>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/student-picker.js') }}"></script>
    <script>
        // Friendly early warning for files over 5 MB (the server checks again).
        document.getElementById('evidence').addEventListener('change', function () {
            var file = this.files[0];
            if (file && file.size > 5 * 1024 * 1024) {
                alert('That file is larger than 5 MB. Please choose a smaller file.');
                this.value = '';
            }
        });
    </script>
@endpush