@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    @php
        $isAdmin = $user->isAdmin();
        $roleLabel = $isAdmin ? 'Admin (Teacher / Adviser)' : 'Student Council Officer';
        $photoUrl = $user->photo_path
            ? route('users.photo', ['user' => $user, 'v' => $user->updated_at?->timestamp])
            : null;

        $courses = config('school.courses', []);
        $yearLevels = config('school.year_levels', []);

        // Checkbox state: after a failed save show what was just ticked, otherwise the saved values.
        $checkedCourses = session()->hasOldInput() ? (array) old('handled_courses', []) : ($user->handled_courses ?? []);
        $checkedYears = session()->hasOldInput() ? (array) old('handled_year_levels', []) : ($user->handled_year_levels ?? []);
    @endphp

    <div class="row g-4">

        {{-- ===== Summary card ===== --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center p-4">
                    @if ($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Profile picture of {{ $user->name }}"
                             class="rounded-circle border mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <div class="rounded-circle border bg-light d-inline-flex align-items-center justify-content-center text-muted mb-3"
                             style="width: 150px; height: 150px;">
                            <i class="bi bi-person" style="font-size: 4rem;"></i>
                        </div>
                    @endif

                    <h2 class="h5 mb-1" style="color: var(--maroon-800)">{{ $user->name }}</h2>
                    <span class="badge" style="background: var(--maroon-700)">{{ $roleLabel }}</span>
                    @if ($user->position)
                        <div class="small text-muted mt-2">{{ $user->position }}</div>
                    @endif

                    <dl class="row text-start small mt-4 mb-0">
                        <dt class="col-5 text-muted fw-normal">Email</dt>
                        <dd class="col-7 text-break">{{ $user->email }}</dd>

                        <dt class="col-5 text-muted fw-normal">Contact</dt>
                        <dd class="col-7">{{ $user->contact_number ?: '—' }}</dd>

                        @if ($isAdmin)
                            <dt class="col-5 text-muted fw-normal">Department</dt>
                            <dd class="col-7">{{ $user->department ?: '—' }}</dd>

                            <dt class="col-5 text-muted fw-normal">Courses</dt>
                            <dd class="col-7">
                                @forelse ($user->handled_courses ?? [] as $course)
                                    <span class="badge text-bg-light border">{{ $course }}</span>
                                @empty
                                    —
                                @endforelse
                            </dd>

                            <dt class="col-5 text-muted fw-normal">Year levels</dt>
                            <dd class="col-7">
                                @forelse ($user->handled_year_levels ?? [] as $year)
                                    <span class="badge text-bg-light border">{{ $year }}</span>
                                @empty
                                    —
                                @endforelse
                            </dd>
                        @else
                            <dt class="col-5 text-muted fw-normal">Student ID</dt>
                            <dd class="col-7">{{ $user->student_number ?: '—' }}</dd>

                            <dt class="col-5 text-muted fw-normal">Course</dt>
                            <dd class="col-7">{{ $user->course ?: '—' }}</dd>

                            <dt class="col-5 text-muted fw-normal">Year level</dt>
                            <dd class="col-7">{{ $user->year_level ?: '—' }}</dd>

                            <dt class="col-5 text-muted fw-normal">Section</dt>
                            <dd class="col-7">{{ $user->section ?: '—' }}</dd>
                        @endif

                        <dt class="col-5 text-muted fw-normal">Member since</dt>
                        <dd class="col-7 mb-0">{{ $user->created_at?->format('M d, Y') }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Real numbers from the database --}}
            <div class="row g-2 mt-1">
                @foreach ($stats as [$label, $value])
                    <div class="col-6">
                        <div class="card h-100">
                            <div class="card-body py-3 text-center">
                                <div class="h4 mb-0" style="color: var(--maroon-800)">{{ number_format($value) }}</div>
                                <div class="small text-muted">{{ $label }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-lg-8">

            {{-- ===== Profile information ===== --}}
            <div class="card mb-4">
                <div class="card-header">Profile Information</div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PATCH')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                       class="form-control @error('name') is-invalid @enderror" required autocomplete="name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                       class="form-control @error('email') is-invalid @enderror" required autocomplete="username">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">This is the email you use to log in.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="contact_number" class="form-label">Contact number <span class="text-muted fw-normal">(optional)</span></label>
                                <input type="text" id="contact_number" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}"
                                       class="form-control @error('contact_number') is-invalid @enderror" maxlength="30"
                                       placeholder="09XX XXX XXXX" autocomplete="tel">
                                @error('contact_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="position" class="form-label">
                                    {{ $isAdmin ? 'Position / title' : 'Council position' }}
                                    <span class="text-muted fw-normal">(optional)</span>
                                </label>
                                <input type="text" id="position" name="position" value="{{ old('position', $user->position) }}"
                                       class="form-control @error('position') is-invalid @enderror" maxlength="100"
                                       placeholder="{{ $isAdmin ? 'e.g. Student Council Adviser' : 'e.g. Secretary' }}">
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Account type</label>
                                <input type="text" class="form-control" value="{{ $roleLabel }}" disabled>
                            </div>
                        </div>

                        @if ($isAdmin)
                            {{-- ===== Admin (teacher / adviser) details ===== --}}
                            <hr class="my-4">
                            <h3 class="h6 mb-3" style="color: var(--maroon-800)">Teacher details</h3>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="department" class="form-label">Department <span class="text-muted fw-normal">(optional)</span></label>
                                    <input type="text" id="department" name="department" value="{{ old('department', $user->department) }}"
                                           class="form-control @error('department') is-invalid @enderror" maxlength="100">
                                    @error('department')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <div class="form-label">Courses handled</div>
                                    @foreach ($courses as $i => $course)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="handled_courses[]"
                                                   id="course_{{ $i }}" value="{{ $course }}"
                                                   @checked(in_array($course, $checkedCourses, true))>
                                            <label class="form-check-label" for="course_{{ $i }}">{{ $course }}</label>
                                        </div>
                                    @endforeach
                                    @error('handled_courses')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    @error('handled_courses.*')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <div class="form-label">Year levels handled</div>
                                    @foreach ($yearLevels as $i => $year)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="handled_year_levels[]"
                                                   id="year_{{ $i }}" value="{{ $year }}"
                                                   @checked(in_array($year, $checkedYears, true))>
                                            <label class="form-check-label" for="year_{{ $i }}">{{ $year }}</label>
                                        </div>
                                    @endforeach
                                    @error('handled_year_levels')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    @error('handled_year_levels.*')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @else
                            {{-- ===== Officer (student) details ===== --}}
                            <hr class="my-4">
                            <h3 class="h6 mb-3" style="color: var(--maroon-800)">Student details</h3>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="student_number" class="form-label">Student ID <span class="text-muted fw-normal">(optional)</span></label>
                                    <input type="text" id="student_number" name="student_number" value="{{ old('student_number', $user->student_number) }}"
                                           class="form-control @error('student_number') is-invalid @enderror" maxlength="50">
                                    @error('student_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="section" class="form-label">Section <span class="text-muted fw-normal">(optional)</span></label>
                                    <input type="text" id="section" name="section" value="{{ old('section', $user->section) }}"
                                           class="form-control @error('section') is-invalid @enderror" maxlength="50">
                                    @error('section')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="course" class="form-label">Course <span class="text-muted fw-normal">(optional)</span></label>
                                    <select id="course" name="course" class="form-select @error('course') is-invalid @enderror">
                                        <option value="">— Select course —</option>
                                        @foreach ($courses as $course)
                                            <option value="{{ $course }}" @selected(old('course', $user->course) === $course)>{{ $course }}</option>
                                        @endforeach
                                    </select>
                                    @error('course')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="year_level" class="form-label">Year level <span class="text-muted fw-normal">(optional)</span></label>
                                    <select id="year_level" name="year_level" class="form-select @error('year_level') is-invalid @enderror">
                                        <option value="">— Select year level —</option>
                                        @foreach ($yearLevels as $year)
                                            <option value="{{ $year }}" @selected(old('year_level', $user->year_level) === $year)>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                    @error('year_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        <hr class="my-4">

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="photo" class="form-label">Profile picture <span class="text-muted fw-normal">(optional)</span></label>
                                <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png"
                                       class="form-control @error('photo') is-invalid @enderror">
                                @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">JPG or PNG, up to 3 MB.</div>
                                @enderror

                                @if ($user->photo_path)
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="remove_photo" id="remove_photo" value="1">
                                        <label class="form-check-label" for="remove_photo">Remove current picture</label>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-navy">
                                <i class="bi bi-check-lg me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ===== Change password ===== --}}
            <div class="card">
                <div class="card-header">Change Password</div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('profile.password') }}" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="current_password" class="form-label">Current password</label>
                                <input type="password" id="current_password" name="current_password"
                                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                       required autocomplete="current-password">
                                @error('current_password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="new_password" class="form-label">New password</label>
                                <input type="password" id="new_password" name="password"
                                       class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                       required autocomplete="new-password">
                                @error('password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">At least 8 characters, with letters and numbers.</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm new password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       class="form-control" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-navy">
                                <i class="bi bi-key me-1"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Friendly early warning for pictures over 3 MB (the server checks again).
        document.getElementById('photo').addEventListener('change', function () {
            var file = this.files[0];
            if (file && file.size > 3 * 1024 * 1024) {
                alert('That picture is larger than 3 MB. Please choose a smaller one.');
                this.value = '';
            }
        });
    </script>
@endpush