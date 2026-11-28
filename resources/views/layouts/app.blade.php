<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') &middot; {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<div class="d-flex">

    {{-- ===== Sidebar (becomes a slide-in menu on phones/tablets) ===== --}}
    <aside id="sidebar" class="sidebar offcanvas-lg offcanvas-start d-flex flex-column" tabindex="-1">
        <div class="brand d-flex justify-content-between align-items-start">
            <div>
                <div class="fw-bold fs-6"><i class="bi bi-shield-check me-1"></i> Violation Reports</div>
                <small>Student Council</small>
            </div>
            <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Close"></button>
        </div>

        <nav class="nav flex-column p-3 flex-grow-1 overflow-auto">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            {{-- Links appear automatically as each module is added in later steps --}}
            @if (Route::has('students.index'))
                <a class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}" href="{{ route('students.index') }}">
                    <i class="bi bi-people"></i> Students
                </a>
            @endif

            @if (Route::has('reports.create') && auth()->user()->isOfficer())
                <a class="nav-link {{ request()->routeIs('reports.create') ? 'active' : '' }}" href="{{ route('reports.create') }}">
                    <i class="bi bi-plus-circle"></i> New Report
                </a>
            @endif

            @if (Route::has('reports.index'))
                <a class="nav-link {{ request()->routeIs('reports.index', 'reports.show') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <i class="bi bi-journal-text"></i>
                    {{ auth()->user()->isAdmin() ? 'All Reports' : 'My Reports' }}
                </a>
            @endif

            @if (auth()->user()->isAdmin() && Route::has('violations.index'))
                <a class="nav-link {{ request()->routeIs('violations.*') ? 'active' : '' }}" href="{{ route('violations.index') }}">
                    <i class="bi bi-clipboard-data"></i> Student Violations
                </a>
            @endif

            @if (auth()->user()->isAdmin() && Route::has('categories.index'))
                <div class="nav-heading">Administration</div>
                <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                    <i class="bi bi-tags"></i> Violation Categories
                </a>
            @endif
        </nav>
    </aside>

    {{-- ===== Main column ===== --}}
    <div class="flex-grow-1 min-vw-0" style="min-width:0">
        <header class="topbar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button"
                        data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Open menu">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <h1 class="h5 mb-0 fw-semibold" style="color: var(--navy-800)">@yield('title', 'Dashboard')</h1>
            </div>

            <div class="dropdown">
                <button class="btn btn-light border d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @if (auth()->user()->photo_path)
                        <img src="{{ route('users.photo', ['user' => auth()->user(), 'v' => auth()->user()->updated_at?->timestamp]) }}"
                             alt="" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                    @else
                        <i class="bi bi-person-circle fs-5"></i>
                    @endif
                    <span class="d-none d-sm-inline text-start lh-sm">
                        <span class="d-block small fw-semibold">{{ auth()->user()->name }}</span>
                        <span class="d-block text-muted" style="font-size:.72rem">{{ ucfirst(auth()->user()->role) }}</span>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header">{{ auth()->user()->email }}</li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>My Profile</a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Log out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="main-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>