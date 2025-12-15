<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\QueueController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\PatientNotificationController;

// =======================
// PUBLIC ROUTES
// =======================

Route::post('/patient/register', [AuthController::class, 'patientRegister']);
Route::post('/patient/login', [AuthController::class, 'patientLogin']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);

// Services (public)
Route::get('/services', [ServiceController::class, 'index']);

// =======================
// PROTECTED ROUTES (Bearer Token)
// =======================
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::patch('/appointments/{id}', [AppointmentController::class, 'update']);


    // -----------------------
    // PATIENT SELF-SERVICE
    // -----------------------
    Route::get('/patient/profile', [PatientController::class, 'profile']);
    Route::put('/patient/profile', [PatientController::class, 'updateProfile']);
    Route::put('/patient/change-password', [PatientController::class, 'changePassword']);

    // Notifications
    Route::get('/patient/notifications', [PatientNotificationController::class, 'index']);
    Route::patch('/patient/notifications/{id}/read', [PatientNotificationController::class, 'markRead']);

    // -----------------------
    // PATIENT MANAGEMENT (ADMIN SIDE)
    // -----------------------
    Route::get('/patients', [PatientController::class, 'index']);
    Route::get('/patients/{id}', [PatientController::class, 'show']);
    Route::patch('/patients/{id}', [PatientController::class, 'update']);
    Route::delete('/patients/{id}', [PatientController::class, 'destroy']);

    // If you still use these:
    Route::get('/patients/{id}/appointments', [PatientController::class, 'appointmentHistory']);
    Route::get('/patients/{id}/statistics', [PatientController::class, 'statistics']);


    // -----------------------
    // SERVICES MANAGEMENT (ADMIN SIDE)
    // -----------------------
    Route::post('/services', [ServiceController::class, 'store']);
    Route::patch('/services/{id}', [ServiceController::class, 'update']);
    Route::delete('/services/{id}', [ServiceController::class, 'destroy']);


    // -----------------------
    // DASHBOARD
    // -----------------------
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/upcoming', [DashboardController::class, 'upcoming']);
    Route::get('/dashboard/services', [DashboardController::class, 'services']);
    Route::get('/dashboard/my', [DashboardController::class, 'my']);
    Route::get('/analytics/trends', [DashboardController::class, 'trends']);
    Route::get('/appointments/today-queue', [QueueController::class, 'todayQueue']);
    
    // -----------------------
    // ANALYTICS
    // -----------------------
    Route::get('/analytics/appointments', [AnalyticsController::class, 'appointments']);
    Route::get('/analytics/demographics', [AnalyticsController::class, 'demographics']);
    Route::get('/analytics/appointments/forecast', [AnalyticsController::class, 'appointmentsForecast']);
    Route::get('/analytics/calendar-density', [AnalyticsController::class, 'calendarDensity']);


    // -----------------------
    // QUEUE MANAGEMENT
    // -----------------------
    Route::get('/queue/next', [QueueController::class, 'getNext']);
    Route::post('/queue/call-next', [QueueController::class, 'callNext']);
    Route::get('/queue/my-position/{appointmentId}', [QueueController::class, 'myPosition']);
    Route::patch('/appointments/{id}', [AppointmentController::class, 'update']);



    // -----------------------
    // AUDIT LOGS
    // -----------------------
    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/audit-logs/stats', [AuditLogController::class, 'stats']);
});
