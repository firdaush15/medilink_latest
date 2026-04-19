<?php
// routes/api.php  — Disease Prediction section (add this to your existing api.php)
// Only showing the prediction routes — keep all other routes unchanged

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\MentalHealthController;
use App\Http\Controllers\Api\DoctorApiController;
use App\Http\Controllers\Api\ArticleApiController;
use App\Http\Controllers\Api\DiseasePredictionController;

// ========================================
// AUTHENTICATION ROUTES
// ========================================
Route::post('/auth/verify-token', [AuthController::class, 'verifyToken']);
Route::post('/auth/register',     [AuthController::class, 'register']);
Route::post('/auth/login',        [AuthController::class, 'login']);
Route::post('/auth/logout',       [AuthController::class, 'logout']);

// ========================================
// USER PROFILE ROUTES
// ========================================
Route::post('/profile',        [AuthController::class, 'profile']);
Route::post('/profile/update', [AuthController::class, 'updateProfile']);

// ========================================
// APPOINTMENT ROUTES
// ========================================
Route::get('/doctors',                         [AppointmentController::class, 'getDoctors']);
Route::post('/appointments/available-slots',   [AppointmentController::class, 'getAvailableTimeSlots']);
Route::post('/appointments/book',              [AppointmentController::class, 'bookAppointment']);
Route::post('/appointments/patient',           [AppointmentController::class, 'getPatientAppointments']);
Route::post('/appointments/cancel',            [AppointmentController::class, 'cancelAppointment']);

// ========================================
// MENTAL HEALTH ROUTES
// ========================================
Route::prefix('mental-health')->group(function () {
    Route::post('/assessment/store',   [MentalHealthController::class, 'storeAssessment']);
    Route::post('/assessment/history', [MentalHealthController::class, 'getPatientAssessments']);
    Route::post('/assessment/detail',  [MentalHealthController::class, 'getAssessmentDetail']);
    Route::post('/assessment/stats',   [MentalHealthController::class, 'getAssessmentStats']);
});

// ========================================
// DOCTOR MOBILE APP ROUTES
// ========================================
Route::prefix('doctor')->group(function () {
    Route::post('/dashboard',             [DoctorApiController::class, 'dashboard']);
    Route::post('/appointments',          [DoctorApiController::class, 'appointments']);
    Route::post('/appointments/detail',   [DoctorApiController::class, 'appointmentDetail']);
    Route::post('/appointments/start',    [DoctorApiController::class, 'startConsultation']);
    Route::post('/appointments/complete', [DoctorApiController::class, 'completeConsultation']);
    Route::post('/patients',              [DoctorApiController::class, 'patients']);
});

// ========================================
// ARTICLE ROUTES
// ========================================
Route::get('/articles',      [ArticleApiController::class, 'index']);
Route::get('/articles/{id}', [ArticleApiController::class, 'show']);

// ========================================
// DISEASE PREDICTION — ACTIVE DIAGNOSIS
// ========================================
Route::prefix('prediction')->group(function () {
    Route::get('/symptoms',        [DiseasePredictionController::class, 'getSymptoms']);
    Route::post('/predict',        [DiseasePredictionController::class, 'predict']);
    Route::post('/clarify',        [DiseasePredictionController::class, 'clarify']);
    Route::post('/history',        [DiseasePredictionController::class, 'history']);
    Route::post('/analyze_text',   [DiseasePredictionController::class, 'parseText']);   // ← NLP
});

// ========================================
// HEALTH CHECK
// ========================================
Route::get('/health', function () {
    return response()->json([
        'status'    => 'ok',
        'message'   => 'MediLink API is running',
        'timestamp' => now(),
        'version'   => '2.0.0',
    ]);
});