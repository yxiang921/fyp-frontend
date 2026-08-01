<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MissingPersonController;
use App\Http\Controllers\CameraController;
use App\Http\Controllers\PublicSubmissionController;
use App\Http\Controllers\DetectionController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\VerificationController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\mailtest;
Route::get('/mailtest', [Mailtest::class, 'test'])->name('mailtest');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
    Route::get('/reset-password/{token}', function (string $token) {
        return view('auth.reset-password', ['token' => $token, 'email' => request('email')]);
    })->name('password.reset');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Public Missing Person Submission (no authentication required)
Route::prefix('report-missing')->name('public.')->group(function () {
    Route::get('/', [PublicSubmissionController::class, 'create'])->name('report-missing');
    Route::post('/', [PublicSubmissionController::class, 'store'])
        ->middleware('throttle:5,60')
        ->name('report-missing.store');
    Route::get('/submitted/{caseNumber}', [PublicSubmissionController::class, 'submitted'])
        ->where('caseNumber', 'MP-\d{4}-\d{4}')
        ->name('submitted');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])
        ->middleware('role:super_admin')
        ->name('dashboard.admin');
    Route::get('/dashboard/pdrm', [DashboardController::class, 'pdrm'])
        ->middleware('role:super_admin,pdrm_officer')
        ->name('dashboard.pdrm');
    Route::get('/dashboard/patrol', [DashboardController::class, 'patrol'])
        ->middleware('role:super_admin,patrol_unit')
        ->name('dashboard.patrol');
    Route::get('/dashboard/public', [DashboardController::class, 'public'])
        ->middleware('role:super_admin,public_user')
        ->name('dashboard.public');

    // Profile
    Route::get('/profile', function () {
        return view('profile.index');
    })->name('profile');
    Route::get('/profile/security', function () {
        return view('profile.security');
    })->name('profile.security');

    // Notifications
    Route::get('/notifications', function () {
        return view('notifications.index');
    })->name('notifications.index');

    // User Management (admin only)
    Route::middleware('role:super_admin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', function () {
            $users = User::latest()->paginate(15);
            return view('users.index', compact('users'));
        })->name('index');
        Route::get('/create', function () { return view('users.create'); })->name('create');
        Route::post('/', function () {
            $data = request()->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'role' => 'required|string',
                'agency' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'password' => 'required|string|min:8',
            ]);
            $user = User::create($data);
            return redirect()->route('users.show', $user)->with('success', 'User created successfully.');
        })->name('store');
        Route::get('/{user}', function (User $user) {
            $auditLogs = $user->auditLogs()->latest()->paginate(10);
            return view('users.show', compact('user', 'auditLogs'));
        })->name('show');
        Route::get('/{user}/edit', function (User $user) { return view('users.edit', compact('user')); })->name('edit');
        Route::put('/{user}', function (User $user) {
            $data = request()->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'role' => 'required|string',
                'agency' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'is_active' => 'boolean',
                'password' => 'nullable|string|min:8',
            ]);
            if (empty($data['password'])) {
                unset($data['password']);
            }
            $data['is_active'] = request()->boolean('is_active');
            $user->update($data);
            return redirect()->route('users.show', $user)->with('success', 'User updated successfully.');
        })->name('update');
    });

    // Missing Persons — PDRM Officers & Super Admin
    Route::middleware('role:super_admin,pdrm_officer')->prefix('missing-persons')->name('missing-persons.')->group(function () {
        Route::get('/', [MissingPersonController::class, 'index'])->name('index');
        Route::get('/create', [MissingPersonController::class, 'create'])->name('create');
        Route::post('/', [MissingPersonController::class, 'store'])->name('store');
        Route::get('/{missingPerson}', [MissingPersonController::class, 'show'])->name('show');
        Route::get('/{missingPerson}/edit', [MissingPersonController::class, 'edit'])->name('edit');
        Route::put('/{missingPerson}', [MissingPersonController::class, 'update'])->name('update');
        Route::delete('/{missingPerson}', [MissingPersonController::class, 'destroy'])->name('destroy');
        Route::post('/{missingPerson}/verify', [MissingPersonController::class, 'verify'])->name('verify');
    });

    // Detections
    Route::middleware('role:super_admin,pdrm_officer')->prefix('detections')->name('detections.')->group(function () {
        Route::get('/', [DetectionController::class, 'index'])->name('index');
        Route::get('/{detection}', [DetectionController::class, 'show'])->name('show');
    });

    // Alerts — PDRM Officers & Patrol Unit
    Route::middleware('role:super_admin,pdrm_officer,patrol_unit')->prefix('alerts')->name('alerts.')->group(function () {
        Route::get('/', [AlertController::class, 'index'])->name('index');
        Route::get('/{alert}', [AlertController::class, 'show'])->name('show');
        Route::get('/{alert}/review', [AlertController::class, 'review'])->name('review');
        Route::post('/{alert}/send-alert', [AlertController::class, 'sendAlert'])->name('send-alert');
    });

    // Verifications — PDRM Officers & Super Admin
    Route::middleware('role:super_admin,pdrm_officer')->prefix('verifications')->name('verifications.')->group(function () {
        Route::get('/', [VerificationController::class, 'index'])->name('index');
        Route::get('/{verification}', [VerificationController::class, 'show'])->name('show');
        Route::get('/{verification}/review', [VerificationController::class, 'review'])->name('review');
        Route::post('/{verification}/review', [VerificationController::class, 'submitReview'])->name('review.submit');
    });

    // Cameras
    Route::middleware('role:super_admin,pdrm_officer')->prefix('cameras')->name('cameras.')->group(function () {
        Route::get('/', [CameraController::class, 'index'])->name('index');
        Route::get('/create', [CameraController::class, 'create'])->name('create');
        Route::post('/', [CameraController::class, 'store'])->name('store');
        Route::get('/{camera}', [CameraController::class, 'show'])->name('show');
        Route::get('/{camera}/edit', [CameraController::class, 'edit'])->name('edit');
        Route::put('/{camera}', [CameraController::class, 'update'])->name('update');
        Route::delete('/{camera}', [CameraController::class, 'destroy'])->name('destroy');
        Route::get('/{camera}/live', [CameraController::class, 'live'])->name('live');
    });

    // Audit Logs (admin only)
    Route::middleware('role:super_admin')->prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/', function () {
            $auditLogs = \App\Models\AuditLog::with('user')->latest()->paginate(15);
            return view('audit-logs.index', compact('auditLogs'));
        })->name('index');
        Route::get('/{auditLog}', function (\App\Models\AuditLog $auditLog) {
            $auditLog->load('user');
            return view('audit-logs.show', compact('auditLog'));
        })->name('show');
    });

    // Email Logs (admin + PDRM officers)
    Route::middleware('role:super_admin,pdrm_officer')->prefix('email-logs')->name('email-logs.')->group(function () {
        Route::get('/', [EmailLogController::class, 'index'])->name('index');
        Route::get('/{emailLog}', [EmailLogController::class, 'show'])->name('show');
    });

    // My Cases — public users only
    Route::middleware('role:super_admin,public_user')->prefix('my-cases')->name('public.')->group(function () {
        Route::get('/', [PublicSubmissionController::class, 'myCases'])->name('cases');
        Route::get('/{missingPerson}', [PublicSubmissionController::class, 'showCase'])->name('case.show');
    });
});

// Redirect root to login or dashboard
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});
