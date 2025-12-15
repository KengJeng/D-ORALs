<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Api\AuthController;

// Fallback for Laravel's auth middleware if ever used
// (route('login') -> /admin/login)
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Redirect root to patient login
Route::get('/', function () {
    return redirect('/patient/login');
});

// ======================
// Patient Routes
// ======================
Route::prefix('patient')->group(function () {
    Route::get('/login', function () {
        return view('patient.login');
    })->name('patient.login');

    Route::post('/login', [AuthController::class, 'patientLogin'])
        ->name('patient.web-login');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('patient.logout');

    Route::get('/register', function () {
        return view('patient.register');
    })->name('patient.register');

    // POST register - route web form submissions to API controller
    Route::post('/register', [AuthController::class, 'patientRegister'])
        ->name('patient.web-register');

    Route::get('/dashboard', function () {
        return view('patient.appointment');
    })->name('patient.dashboard');

    Route::get('/appointments', [\App\Http\Controllers\Patient\DashboardController::class, 'appointments'])
        ->name('patient.appointments')
        ->middleware(\App\Http\Middleware\AuthenticatePatient::class);

    // Patient dashboard (web) - server-rendered to avoid client-side auth issues
    Route::get('/dashboard/view', [\App\Http\Controllers\Patient\DashboardController::class, 'index'])
        ->name('patient.dashboard.view')
        ->middleware(\App\Http\Middleware\AuthenticatePatient::class);
});

// ======================
// Admin Routes
// ======================
Route::prefix('admin')->group(function () {

    // Admin login page
    Route::get('/login', function () {
        return view('admin.login');
    })->name('admin.login');

    // Admin dashboard (SPA shell, JS handles token auth)
    Route::get('/dashboard', [AdminReportController::class, 'index'])
        ->name('admin.dashboard');

    // Login history view
    Route::get('/login-history', [\App\Http\Controllers\Admin\LoginHistoryController::class, 'index'])
        ->name('admin.login-history');

    // Reports & Analytics (admin side)
    // URLs:
    //   /admin/reports/appointments
    //   /admin/reports/appointments/export
    Route::get('/reports/appointments', [ReportController::class, 'appointments'])
        ->name('reports.appointments');

    Route::get('/reports/appointments/export', [ReportController::class, 'exportAppointments'])
        ->name('reports.appointments.export');
});
