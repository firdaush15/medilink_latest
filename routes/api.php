<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\MentalHealthController;

// ========================================
// AUTHENTICATION ROUTES
// ========================================

// ✅ NEW: Token verification for staff-registered patients
Route::post('/auth/verify-token', [AuthController::class, 'verifyToken']);

// Registration & Login
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);

// ========================================
// USER PROFILE ROUTES
// ========================================
Route::post('/profile', [AuthController::class, 'profile']);
Route::post('/profile/update', [AuthController::class, 'updateProfile']);

// ========================================
// APPOINTMENT ROUTES
// ========================================
Route::get('/doctors', [AppointmentController::class, 'getDoctors']);
Route::post('/appointments/available-slots', [AppointmentController::class, 'getAvailableTimeSlots']);
Route::post('/appointments/book', [AppointmentController::class, 'bookAppointment']);
Route::post('/appointments/patient', [AppointmentController::class, 'getPatientAppointments']);
Route::post('/appointments/cancel', [AppointmentController::class, 'cancelAppointment']);

// Mental Health Assessment Routes
Route::prefix('mental-health')->group(function () {
    Route::post('/assessment/store', [MentalHealthController::class, 'storeAssessment']);
    Route::post('/assessment/history', [MentalHealthController::class, 'getPatientAssessments']);
    Route::post('/assessment/detail', [MentalHealthController::class, 'getAssessmentDetail']);
    Route::post('/assessment/stats', [MentalHealthController::class, 'getAssessmentStats']);
});

// ========================================
// HEALTH CHECK ENDPOINT
// ========================================
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'MediLink API is running',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// ========================================
// BACKWARD COMPATIBILITY (OPTIONAL)
// ========================================
// If you want to keep old routes working, uncomment these:
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/logout', [AuthController::class, 'logout']);