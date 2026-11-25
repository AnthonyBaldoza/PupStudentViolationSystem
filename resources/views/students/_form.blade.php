{{-- Shared by create and edit. Expects: $student, $action, $cancel --}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" novalidate>
    @csrf
    @if ($student->exists)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label for="student_number" class="form-label">Student ID <span class="text-danger">*</span></label>
            <input type="text" id="student_number" name="student_number"
                   value="{{ old('student_number', $student->student_number) }}"
                   class="form-control @error('student_number') is-invalid @enderror" required>
            @error('student_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="full_name" class="form-label">Full name <span class="text-danger">*</span></label>
            <input type="text" id="full_name" name="full_name"
                   value="{{ old('full_name', $student->full_name) }}"
                   class="form-control @error('full_name') is-invalid @enderror" required>
            @error('full_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label for="course" class="form-label">Course <span class="text-danger">*</span></label>
            <input type="text" id="course" name="course"
                   value="{{ old('course', $student->course) }}"
                   class="form-control @error('course') is-invalid @enderror" required>
            @error('course')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label for="year_level" class="form-label">Year level <span class="text-danger">*</span></label>
            <input type="text" id="year_level" name="year_level" placeholder="e.g. 1st Year"
                   value="{{ old('year_level', $student->year_level) }}"
                   class="form-control @error('year_level') is-invalid @enderror" required>
            @error('year_level')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-3">
            <label for="section" class="form-label">Section</label>
            <input type="text" id="section" name="section"
                   value="{{ old('section', $student->section) }}"
                   class="form-control @error('section') is-invalid @enderror">
            @error('section')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label for="photo" class="form-label">Student photo <span class="text-muted fw-normal">(optional)</span></label>
            <div class="d-flex align-items-center gap-3">
                @if ($student->exists && $student->photo_path)
                    <img src="{{ route('students.photo', $student) }}" alt="Current photo"
                         class="rounded border" style="width: 84px; height: 84px; object-fit: cover;">
                @endif
                <div class="flex-grow-1">
                    <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png"
                           class="form-control @error('photo') is-invalid @enderror">
                    @error('photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="form-text">JPG or PNG, up to 3 MB. A clear, front-facing photo helps the Admin recognize the student.</div>
                    @enderror

                    @if ($student->exists && $student->photo_path)
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_photo" id="remove_photo" value="1">
                            <label class="form-check-label" for="remove_photo">Remove current photo</label>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-navy">
            <i class="bi bi-check-lg me-1"></i> {{ $student->exists ? 'Save Changes' : 'Add Student' }}
        </button>
        <a href="{{ $cancel }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>