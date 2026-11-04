<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MissingPersonController;
use App\Http\Controllers\CameraController;
use App\Http\Controllers\PublicSubmissionController;
use App\Http\Controllers\DetectionController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicGalleryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SightingController;
use App\Http\Controllers\PatrolAreaController;
use App\Http\Controllers\VerificationController;
use App\Models\User;
use App\Models\PatrolArea;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\mailtest;
use App\Http\Controllers\MapTileController;
Route::get('/mailtest', [Mailtest::class, 'test'])->name('mailtest');
Route::get('/map-tiles/{z}/{x}/{y}.png', MapTileController::class)
    ->whereNumber(['z', 'x', 'y'])->middleware('throttle:240,1')->name('map-tiles.show');

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
Route::get('/track-case', fn () => view('public.track'))->name('public.track');
Route::post('/track-case', [\App\Http\Controllers\ReportingController::class, 'track'])->middleware('throttle:10,1');
Route::get('/gallery/{missingPerson}/sighting', function (\App\Models\MissingPerson $missingPerson) {
    abort_unless($missingPerson->status === \App\Enums\MissingPersonStatus::Active, 404);
    return view('public.sighting-form', compact('missingPerson'));
})->name('public.sighting');
Route::post('/gallery/{missingPerson}/sighting', function (\Illuminate\Http\Request $request, \App\Models\MissingPerson $missingPerson) {
    app(\App\Http\Controllers\Api\SightingController::class)->store($request, $missingPerson);
    return redirect()->route('public.sighting', $missingPerson)->with('success', __('Thank you! Your report has been submitted.'));
})->middleware('throttle:10,1');
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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/security', [ProfileController::class, 'security'])->name('profile.security');
    Route::put('/profile/security', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/api/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/api/poll', [NotificationController::class, 'poll'])->name('notifications.poll');

    // User Management (admin only)
    Route::middleware('role:super_admin')->resource('patrol-areas', PatrolAreaController::class)->except('show');

    Route::middleware('role:super_admin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', function () {
            $users = User::latest()->paginate(15);
            return view('users.index', compact('users'));
        })->name('index');
        Route::get('/create', function () {
            return view('users.create', ['patrolAreas' => PatrolArea::where('is_active', true)->orderBy('name')->get()]);
        })->name('create');
        Route::post('/', function () {
            $data = request()->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'role' => 'required|string',
                'agency' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'password' => 'required|string|min:8',
                'patrol_area_ids' => 'required_if:role,patrol_unit|array|min:1',
                'patrol_area_ids.*' => 'integer|exists:patrol_areas,id',
            ]);
            $patrolAreaIds = $data['patrol_area_ids'] ?? [];
            unset($data['patrol_area_ids']);
            $user = User::create($data);
            $user->patrolAreas()->sync($user->role->value === 'patrol_unit' ? $patrolAreaIds : []);
            return redirect()->route('users.show', $user)->with('success', 'User created successfully.');
        })->name('store');
        Route::get('/{user}', function (User $user) {
            $auditLogs = $user->auditLogs()->latest()->paginate(10);
            return view('users.show', compact('user', 'auditLogs'));
        })->name('show');
        Route::get('/{user}/edit', function (User $user) {
            $user->load('patrolAreas');
            $patrolAreas = PatrolArea::where('is_active', true)->orderBy('name')->get();
            return view('users.edit', compact('user', 'patrolAreas'));
        })->name('edit');
        Route::put('/{user}', function (User $user) {
            $data = request()->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'role' => 'required|string',
                'agency' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'is_active' => 'boolean',
                'password' => 'nullable|string|min:8',
                'patrol_area_ids' => 'required_if:role,patrol_unit|array|min:1',
                'patrol_area_ids.*' => 'integer|exists:patrol_areas,id',
            ]);
            $patrolAreaIds = $data['patrol_area_ids'] ?? [];
            unset($data['patrol_area_ids']);
            if (empty($data['password'])) {
                unset($data['password']);
            }
            $data['is_active'] = request()->boolean('is_active');
            $user->update($data);
            $user->patrolAreas()->sync($user->role->value === 'patrol_unit' ? $patrolAreaIds : []);
            return redirect()->route('users.show', $user)->with('success', 'User updated successfully.');
        })->name('update');
        Route::delete('/{user}', function (User $user) {
            if ($user->id === auth()->id()) {
                return redirect()->route('users.show', $user)
                    ->with('error', 'You cannot delete your own account.');
            }
            $user->delete();
            return redirect()->route('users.index')
                ->with('success', 'User "' . $user->name . '" has been deleted.');
        })->name('destroy');
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

    // Public sighting reports submitted from the mobile app
    Route::middleware('role:super_admin,pdrm_officer')->prefix('sightings')->name('sightings.')->group(function () {
        Route::get('/evidence/{evidence}', [\App\Http\Controllers\ReportingController::class, 'evidence'])->name('evidence');
        Route::get('/', [SightingController::class, 'index'])->name('index');
        Route::get('/{sighting}', [SightingController::class, 'show'])->name('show');
        Route::patch('/{sighting}/status', [SightingController::class, 'updateStatus'])->name('update-status');
    });

    // Alerts — PDRM Officers & Patrol Unit
    Route::middleware('role:super_admin,pdrm_officer,patrol_unit')->prefix('alerts')->name('alerts.')->group(function () {
        Route::get('/', [AlertController::class, 'index'])->name('index');
        Route::get('/{alert}', [AlertController::class, 'show'])->name('show');
        Route::get('/{alert}/review', [AlertController::class, 'review'])->name('review');
        Route::post('/{alert}/send-alert', [AlertController::class, 'sendAlert'])->name('send-alert');
        Route::patch('/{alert}/update-status', [AlertController::class, 'updateStatus'])->name('update-status');
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
        Route::post('/{camera}/recognition', [CameraController::class, 'recognition'])->name('recognition');
        Route::get('/{camera}', [CameraController::class, 'show'])->name('show');
        Route::get('/{camera}/edit', [CameraController::class, 'edit'])->name('edit');
        Route::put('/{camera}', [CameraController::class, 'update'])->name('update');
        Route::delete('/{camera}', [CameraController::class, 'destroy'])->name('destroy');
        Route::get('/{camera}/live', [CameraController::class, 'live'])->name('live');
    });

    // Audit Logs (admin only)
    Route::get('/privacy-access-logs', function () {
        return view('reports.access-logs', ['logs' => \Illuminate\Support\Facades\DB::table('privacy_access_logs')->latest('created_at')->paginate(50)]);
    })->middleware('role:super_admin')->name('privacy.access-logs');
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

    // Reports & Analysis (admin + PDRM officers)
    Route::middleware('role:super_admin,pdrm_officer')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/analysis', [ReportController::class, 'analysis'])->name('analysis');
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

// Public Missing Persons Gallery (no auth required)
Route::prefix('gallery')->name('public.gallery.')->group(function () {
    Route::get('/', [PublicGalleryController::class, 'index'])->name('index');
    Route::get('/{missingPerson}', [PublicGalleryController::class, 'show'])->name('show');
});

// Redirect root to login or dashboard
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});
