<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register &middot; {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
    <div class="card login-card">
        <div class="card-body p-4 p-sm-5">
            <div class="text-center mb-4">
                <div class="mx-auto mb-3" style="width:56px;height:56px;border-radius:.8rem;display:flex;align-items:center;justify-content:center;background:var(--navy-100);color:var(--navy-700);font-size:1.7rem">
                    <i class="bi bi-person-plus"></i>
                </div>
                <h1 class="h4 fw-bold mb-1" style="color: var(--navy-800)">Create an Account</h1>
                <p class="text-muted small mb-0">Student Council &middot; Registration code required</p>
            </div>

            <form method="POST" action="{{ route('register.store') }}" novalidate>
                @csrf

                {{-- Account type --}}
                <div class="mb-3">
                    <label class="form-label d-block">Account type <span class="text-danger">*</span></label>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="role_officer" value="officer"
                               {{ old('role', 'officer') === 'officer' ? 'checked' : '' }}>
                        <label class="form-check-label" for="role_officer">Student Council Officer</label>
                    </div>

                    @if ($adminEnabled)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="role" id="role_admin" value="admin"
                                   {{ old('role') === 'admin' ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_admin">Admin (Teacher / Adviser)</label>
                        </div>
                    @endif

                    @error('role')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Full name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror"
                           required autofocus autocomplete="name">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           required autocomplete="username">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           required autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="form-text">At least 8 characters, with letters and numbers.</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-control" required autocomplete="new-password">
                </div>

                <div class="mb-4">
                    <label for="registration_code" class="form-label">Registration code</label>
                    <input type="password" id="registration_code" name="registration_code"
                           class="form-control @error('registration_code') is-invalid @enderror"
                           required autocomplete="off">
                    @error('registration_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="form-text">Use the code that matches the account type you chose.</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-navy w-100 py-2">Create account</button>
            </form>

            <p class="text-center small text-muted mt-4 mb-0">
                Already have an account? <a href="{{ route('login') }}">Log in</a>
            </p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>