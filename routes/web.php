<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ViolationCategoryController;
use App\Http\Controllers\ViolationReportController;
use App\Http\Controllers\ViolationReportReviewController;
use App\Http\Controllers\ViolationSanctionController;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// ---- Guests only ----
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1') // 5 attempts per minute
        ->name('login.store');

    // Officer self-registration (needs REGISTRATION_CODE in .env, otherwise 404)
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ---- Any logged-in, active user (admin or officer) ----
Route::middleware(['auth', EnsureUserHasRole::class.':admin,officer'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Students: list/search, add, view (with violation history), edit. No delete.
    // The JSON search route must come BEFORE the resource so "search" isn't read as an ID.
    Route::get('students/search', [StudentController::class, 'search'])->name('students.search');
    Route::get('students/{student}/photo', [StudentController::class, 'photo'])
        ->whereNumber('student')->name('students.photo');
    Route::resource('students', StudentController::class)->except(['destroy']);

    // Reports: list (admin = all, officer = own), view, protected evidence file.
    // Access to a single report is checked again in ViolationReportPolicy.
    Route::get('reports', [ViolationReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{report}', [ViolationReportController::class, 'show'])
        ->whereNumber('report')->name('reports.show');
    Route::get('reports/{report}/evidence', [ViolationReportController::class, 'evidence'])
        ->whereNumber('report')->name('reports.evidence');
    Route::get('reports/{report}/evidence/{evidence}', [ViolationReportController::class, 'evidence'])
        ->whereNumber(['report', 'evidence'])->name('reports.evidence.show');
});

// ---- Officer only: submit reports ----
Route::middleware(['auth', EnsureUserHasRole::class.':officer'])->group(function () {
    Route::get('reports/create', [ViolationReportController::class, 'create'])->name('reports.create');
    Route::post('reports', [ViolationReportController::class, 'store'])->name('reports.store');
    Route::get('reports/{report}/edit', [ViolationReportController::class, 'edit'])
        ->whereNumber('report')->name('reports.edit');
    Route::patch('reports/{report}', [ViolationReportController::class, 'update'])
        ->whereNumber('report')->name('reports.update');
});

// ---- Admin only ----
Route::middleware(['auth', EnsureUserHasRole::class.':admin'])->group(function () {
    // Violation categories: add, edit, activate/deactivate. No delete.
    Route::resource('categories', ViolationCategoryController::class)
        ->parameters(['categories' => 'category'])
        ->except(['show', 'destroy']);
    Route::patch('categories/{category}/toggle', [ViolationCategoryController::class, 'toggle'])
        ->name('categories.toggle');

    // Approve / reject a pending report, with remarks.
    Route::patch('reports/{report}/review', ViolationReportReviewController::class)
        ->whereNumber('report')->name('reports.review');
    Route::patch('sanctions/{sanction}/complete', [ViolationSanctionController::class, 'complete'])
        ->whereNumber('sanction')->name('sanctions.complete');
});
