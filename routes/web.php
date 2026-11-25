<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ViolationCategoryController;
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
Route::middleware(['auth', EnsureUserHasRole::class . ':admin,officer'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Students: list/search, add, view (with violation history), edit. No delete.
    Route::resource('students', StudentController::class)->except(['destroy']);

    // Later steps add: reports (officer + admin) here.
});

// ---- Admin only ----
Route::middleware(['auth', EnsureUserHasRole::class . ':admin'])->group(function () {
    // Violation categories: add, edit, activate/deactivate. No delete.
    Route::resource('categories', ViolationCategoryController::class)
        ->parameters(['categories' => 'category'])
        ->except(['show', 'destroy']);
    Route::patch('categories/{category}/toggle', [ViolationCategoryController::class, 'toggle'])
        ->name('categories.toggle');

    // Later steps add: report review here.
});