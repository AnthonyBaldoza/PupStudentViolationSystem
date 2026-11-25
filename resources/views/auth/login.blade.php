<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in &middot; {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
    <div class="card login-card">
        <div class="card-body p-4 p-sm-5">
            <div class="text-center mb-4">
                <div class="stat-icon mx-auto mb-3" style="width:56px;height:56px;border-radius:.8rem;display:flex;align-items:center;justify-content:center;background:var(--navy-100);color:var(--navy-700);font-size:1.7rem">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h1 class="h4 fw-bold mb-1" style="color: var(--navy-800)">Student Violation Reports</h1>
                <p class="text-muted small mb-0">Student Council &middot; Sign in to continue</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           required autofocus autocomplete="username">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           required autocomplete="current-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                    <label class="form-check-label" for="remember">Keep me signed in</label>
                </div>

                <button type="submit" class="btn btn-navy w-100 py-2">Log in</button>
            </form>

            @if (config('registration.code'))
                <p class="text-center small text-muted mt-4 mb-0">
                    No account yet? <a href="{{ route('register') }}">Create one</a>
                </p>
            @endif
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>