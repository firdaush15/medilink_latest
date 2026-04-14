<?php
// routes/web.php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\NurseDoctorTeamController;
use App\Http\Controllers\Admin\ShiftManagementController;
use App\Http\Controllers\Admin\AdminLeaveController;
use App\Http\Controllers\Admin\AdminPharmacyController;
use App\Http\Controllers\Admin\AdminDiagnosisController;
use App\Http\Controllers\Admin\AdminRestockController;
use App\Http\Controllers\Admin\AdminMessagesController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminArticleController;  // ✅ NEW
use App\Http\Controllers\Admin\AdminReportsController; // ✅ ADD
use App\Http\Controllers\Admin\AdminAlertController;
use App\Http\Controllers\Doctor\DoctorDashboardController;
use App\Http\Controllers\Doctor\DoctorAppointmentController;
use App\Http\Controllers\Doctor\DoctorPatientController;
use App\Http\Controllers\Doctor\DoctorMedicalRecordController;
use App\Http\Controllers\Doctor\DoctorPrescriptionController;
use App\Http\Controllers\Doctor\DoctorPatientAllergyController;
use App\Http\Controllers\Doctor\DoctorReportsController;
use App\Http\Controllers\Doctor\DoctorMessagesController;
use App\Http\Controllers\Doctor\DoctorAlertNotificationController;
use App\Http\Controllers\Doctor\DoctorTeamScheduleController;
use App\Http\Controllers\Doctor\DoctorBillingController;
use App\Http\Controllers\Doctor\DoctorMedicationController;
use App\Http\Controllers\Doctor\DoctorDiagnosisController;
use App\Http\Controllers\Doctor\DoctorSettingsController;
use App\Http\Controllers\Nurse\NurseDashboardController;
use App\Http\Controllers\Nurse\NurseAlertsController;
use App\Http\Controllers\Nurse\NurseAppointmentsController;
use App\Http\Controllers\Nurse\NursePatientsController;
use App\Http\Controllers\Nurse\NurseQueueController;
use App\Http\Controllers\Nurse\NurseTasksController;
use App\Http\Controllers\Nurse\NurseReportsController;
use App\Http\Controllers\Nurse\NurseTeamScheduleController;
use App\Http\Controllers\Nurse\NurseVitalsAnalyticsController;
use App\Http\Controllers\Nurse\NurseWorkDashboardController;
use App\Http\Controllers\Nurse\NurseMessagesController;
use App\Http\Controllers\Nurse\NurseSettingsController;
use App\Http\Controllers\Pharmacist\PharmacistAlertController;
use App\Http\Controllers\Pharmacist\PharmacistDashboardController;
use App\Http\Controllers\Pharmacist\PharmacistInventoryController;
use App\Http\Controllers\Pharmacist\PharmacistPrescriptionController;
use App\Http\Controllers\Pharmacist\PharmacistReportController;
use App\Http\Controllers\Pharmacist\PharmacistRestockController;
use App\Http\Controllers\Pharmacist\PharmacistStockReceiptController;
use App\Http\Controllers\Pharmacist\PharmacistDisposalController;
use App\Http\Controllers\Pharmacist\PharmacistMessagesController;
use App\Http\Controllers\Pharmacist\PharmacistSettingsController;
use App\Http\Controllers\Receptionist\ReceptionistSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ============================================
// ADMIN ROUTES
// ============================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Doctor Management
    Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors');
    Route::get('/doctors/{id}', [DoctorController::class, 'show'])->name('doctors.show');
    Route::get('/doctors/{id}/edit', [DoctorController::class, 'edit'])->name('doctors.edit');
    Route::put('/doctors/{id}', [DoctorController::class, 'update'])->name('doctors.update');
    Route::post('/doctors/{id}/deactivate', [DoctorController::class, 'deactivate'])->name('doctors.deactivate');
    Route::get('/doctors/{id}/schedule', [DoctorController::class, 'schedule'])->name('doctors.schedule');

    // Patient Management
    Route::get('/patients', [PatientController::class, 'index'])->name('patients');
    Route::get('/patients/{id}', [PatientController::class, 'show'])->name('patients.show');
    Route::get('/patients/{id}/edit', [PatientController::class, 'edit'])->name('patients.edit');
    Route::put('/patients/{id}', [PatientController::class, 'update'])->name('patients.update');
    Route::post('/patients/{id}/flag', [PatientController::class, 'flag'])->name('patients.flag');
    Route::post('/patients/{id}/unflag', [PatientController::class, 'unflag'])->name('patients.unflag');

    // Appointment Management
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments');
    Route::get('/appointments/{id}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('/appointments/{id}/no-show', [AppointmentController::class, 'markNoShow'])->name('appointments.no-show');
    Route::get('/appointments/{id}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::post('/appointments/{id}/reschedule', [AppointmentController::class, 'processReschedule'])->name('appointments.process-reschedule');

    // Team Management
    Route::prefix('teams')->name('teams.')->group(function () {
        Route::get('/', [NurseDoctorTeamController::class, 'index'])->name('index');
        Route::get('/create', [NurseDoctorTeamController::class, 'create'])->name('create');
        Route::post('/', [NurseDoctorTeamController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [NurseDoctorTeamController::class, 'edit'])->name('edit');
        Route::put('/{id}', [NurseDoctorTeamController::class, 'update'])->name('update');
        Route::delete('/{id}', [NurseDoctorTeamController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/deactivate', [NurseDoctorTeamController::class, 'deactivate'])->name('deactivate');
        Route::post('/bulk-assign', [NurseDoctorTeamController::class, 'bulkAssign'])->name('bulk-assign');
    });

    // Shift Management
    Route::prefix('shifts')->name('shifts.')->group(function () {
        Route::get('/', [ShiftManagementController::class, 'index'])->name('index');
        Route::get('/create', [ShiftManagementController::class, 'create'])->name('create');
        Route::post('/', [ShiftManagementController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [ShiftManagementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ShiftManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [ShiftManagementController::class, 'destroy'])->name('destroy');
    });

    // Leave Management
    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/', [AdminLeaveController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminLeaveController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [AdminLeaveController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [AdminLeaveController::class, 'reject'])->name('reject');
    });

    // Diagnosis Management
    Route::prefix('diagnoses')->name('diagnoses.')->group(function () {
        Route::get('/', [AdminDiagnosisController::class, 'index'])->name('index');
        Route::post('/', [AdminDiagnosisController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminDiagnosisController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminDiagnosisController::class, 'update'])->name('update');
        Route::post('/{id}/toggle-status', [AdminDiagnosisController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{id}/usage', [AdminDiagnosisController::class, 'getUsageStats'])->name('usage');
        Route::post('/bulk-import', [AdminDiagnosisController::class, 'bulkImport'])->name('bulk-import');
        Route::get('/export', [AdminDiagnosisController::class, 'export'])->name('export');
    });

    // Pharmacy Inventory
    Route::prefix('pharmacy-inventory')->name('pharmacy-inventory.')->group(function () {
        Route::get('/', [AdminPharmacyController::class, 'index'])->name('index');
        Route::get('/reports', [AdminPharmacyController::class, 'reports'])->name('reports');
        Route::get('/analytics', [AdminPharmacyController::class, 'analytics'])->name('analytics');
        Route::get('/export', [AdminPharmacyController::class, 'export'])->name('export');
        Route::get('/{id}', [AdminPharmacyController::class, 'show'])->name('show');
    });

    // Restock Management
    Route::prefix('restock')->name('restock.')->group(function () {
        Route::get('/', [AdminRestockController::class, 'index'])->name('index');
        Route::get('/receipts', [AdminRestockController::class, 'receiptsIndex'])->name('receipts');
        Route::get('/disposals', [AdminRestockController::class, 'disposalsIndex'])->name('disposals');
        Route::get('/reports', [AdminRestockController::class, 'reports'])->name('reports');
        Route::get('/{id}', [AdminRestockController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [AdminRestockController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [AdminRestockController::class, 'reject'])->name('reject');
        Route::get('/receipts/{id}', [AdminRestockController::class, 'receiptShow'])->name('receipts.show');
        Route::get('/disposals/{id}', [AdminRestockController::class, 'disposalShow'])->name('disposals.show');
    });

    // ✅ Article Management
    Route::prefix('articles')->name('articles.')->group(function () {
        Route::get('/',              [AdminArticleController::class, 'index'])        ->name('index');
        Route::get('/create',        [AdminArticleController::class, 'create'])       ->name('create');
        Route::post('/fetch-url',    [AdminArticleController::class, 'fetchUrl'])     ->name('fetch-url');
        Route::post('/',             [AdminArticleController::class, 'store'])        ->name('store');
        Route::get('/{id}',          [AdminArticleController::class, 'show'])         ->name('show');
        Route::get('/{id}/edit',     [AdminArticleController::class, 'edit'])         ->name('edit');
        Route::put('/{id}',          [AdminArticleController::class, 'update'])       ->name('update');
        Route::delete('/{id}',       [AdminArticleController::class, 'destroy'])      ->name('destroy');
        Route::post('/{id}/restore', [AdminArticleController::class, 'restore'])      ->name('restore');
        Route::post('/{id}/toggle',  [AdminArticleController::class, 'togglePublish'])->name('toggle');
        Route::post('/reorder',      [AdminArticleController::class, 'reorder'])      ->name('reorder');
        Route::patch('/{id}/sort-order', [AdminArticleController::class, 'updateSortOrder'])->name('sort-order');
    });

    // Messages
    Route::get('/messages', [AdminMessagesController::class, 'index'])->name('messages');
    Route::post('/messages/create', [AdminMessagesController::class, 'create'])->name('messages.create');
    Route::post('/messages/send', [AdminMessagesController::class, 'send'])->name('messages.send');
    Route::post('/messages/{id}/toggle-star', [AdminMessagesController::class, 'toggleStar'])->name('messages.toggle-star');
    Route::post('/messages/{id}/archive', [AdminMessagesController::class, 'archive'])->name('messages.archive');
    Route::post('/messages/{id}/mark-as-read', [AdminMessagesController::class, 'markAsRead'])->name('messages.mark-as-read');
    Route::get('/messages/templates', [AdminMessagesController::class, 'getTemplates'])->name('messages.templates');
    Route::get('/messages/inbox-poll', [AdminMessagesController::class, 'pollInbox'])->name('messages.inbox-poll');
    Route::get('/messages/{id}/poll', [AdminMessagesController::class, 'poll'])->name('messages.poll');
    Route::get('/messages/unread-count', [AdminMessagesController::class, 'unreadCount'])->name('messages.unread-count');

    // Placeholders
    Route::get('/medical-records', fn () => view('admin.placeholder', ['title' => 'Medical Records Management']))->name('medical_records');
    Route::get('/reports', [AdminReportsController::class, 'index'])->name('reports');

    // Settings
    Route::get('/settings',           [AdminSettingsController::class, 'index'])          ->name('settings');
    Route::post('/settings/profile',  [AdminSettingsController::class, 'updateProfile'])  ->name('settings.profile');
    Route::post('/settings/password', [AdminSettingsController::class, 'updatePassword']) ->name('settings.password');

    // ── Alerts (static routes MUST come before {id} wildcard routes) ──
    Route::get('/alerts',                   [AdminAlertController::class, 'index'])      ->name('alerts.index');
    Route::post('/alerts/send',             [AdminAlertController::class, 'send'])       ->name('alerts.send');        // ✅ ADDED
    Route::post('/alerts/mark-all-read',    [AdminAlertController::class, 'markAllRead'])->name('alerts.mark-all-read');
    Route::get('/alerts/unread-count',      [AdminAlertController::class, 'unreadCount'])->name('alerts.unread-count');
    Route::post('/alerts/{id}/mark-read',   [AdminAlertController::class, 'markAsRead']) ->name('alerts.mark-read');
    Route::post('/alerts/{id}/acknowledge', [AdminAlertController::class, 'acknowledge'])->name('alerts.acknowledge');
    Route::delete('/alerts/{id}',           [AdminAlertController::class, 'destroy'])    ->name('alerts.destroy');
});

// ============================================
// DOCTOR ROUTES
// ============================================
Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {

    Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');

    // Appointments
    Route::get('/appointments', [DoctorAppointmentController::class, 'index'])->name('appointments');
    Route::get('/appointments/{id}', [DoctorAppointmentController::class, 'show'])->name('appointments.show');
    Route::post('/appointments/{id}/start', [DoctorAppointmentController::class, 'startConsultation'])->name('appointments.start');
    Route::post('/appointments/{id}/complete', [DoctorAppointmentController::class, 'complete'])->name('appointments.complete');
    Route::get('/appointments/{id}/update-patient', [DoctorAppointmentController::class, 'updatePatientPage'])->name('appointments.update-patient');
    Route::post('/appointments/referral', [DoctorAppointmentController::class, 'storeReferral'])->name('appointments.referral');

    // Diagnosis
    Route::get('/diagnoses/search', [DoctorDiagnosisController::class, 'search'])->name('diagnoses.search');
    Route::post('/diagnoses/store', [DoctorDiagnosisController::class, 'store'])->name('diagnoses.store');
    Route::get('/diagnoses/patient/{patientId}', [DoctorDiagnosisController::class, 'getPatientDiagnoses'])->name('diagnoses.patient');
    Route::put('/diagnoses/{id}/status', [DoctorDiagnosisController::class, 'updateStatus'])->name('diagnoses.update-status');

    // Patients
    Route::get('/patients', [DoctorPatientController::class, 'index'])->name('patients');
    Route::get('/patients/{id}', [DoctorPatientController::class, 'show'])->name('patients.show');
    Route::post('/patients/{id}/add-note', [DoctorPatientController::class, 'addNote'])->name('patients.add-note');
    Route::post('/mental-health/review', [DoctorPatientController::class, 'reviewMentalHealthAssessment'])->name('mental-health.review');

    // Medical Records & Prescriptions
    Route::post('/medical-records/store', [DoctorMedicalRecordController::class, 'store'])->name('medical-records.store');
    Route::post('/prescriptions/store', [DoctorPrescriptionController::class, 'store'])->name('prescriptions.store');

    // Patient Allergies
    Route::post('/patient-allergies/store', [DoctorPatientAllergyController::class, 'store'])->name('patient-allergies.store');
    Route::delete('/patient-allergies/{allergyId}', [DoctorPatientAllergyController::class, 'destroy'])->name('patient-allergies.destroy');

    // Billing
    Route::post('/billing/add-item', [DoctorBillingController::class, 'addBillingItem'])->name('billing.add-item');
    Route::delete('/billing/remove-item/{itemId}', [DoctorBillingController::class, 'removeBillingItem'])->name('billing.remove-item');
    Route::get('/billing/summary/{appointmentId}', [DoctorBillingController::class, 'getBillingSummary'])->name('billing.summary');

    // Medications
    Route::get('/medications/search', [DoctorMedicationController::class, 'search'])->name('medications.search');
    Route::get('/medications/{id}/availability', [DoctorMedicationController::class, 'checkAvailability'])->name('medications.availability');
    Route::get('/medications', [DoctorMedicationController::class, 'index'])->name('medications.index');
    Route::get('/medications/{id}', [DoctorMedicationController::class, 'show'])->name('medications.show');

    // Reports
    Route::get('/reports', [DoctorReportsController::class, 'index'])->name('reports');

    // Messages
    Route::get('/messages', [DoctorMessagesController::class, 'index'])->name('messages');
    Route::post('/messages/create', [DoctorMessagesController::class, 'create'])->name('messages.create');
    Route::post('/messages/send', [DoctorMessagesController::class, 'send'])->name('messages.send');
    Route::post('/messages/{id}/toggle-star', [DoctorMessagesController::class, 'toggleStar'])->name('messages.toggle-star');
    Route::post('/messages/{id}/mark-as-read', [DoctorMessagesController::class, 'markAsRead'])->name('messages.mark-as-read');
    Route::get('/messages/templates', [DoctorMessagesController::class, 'getTemplates'])->name('messages.templates');
    Route::get('/messages/inbox-poll', [DoctorMessagesController::class, 'pollInbox'])->name('messages.inbox-poll');
    Route::get('/messages/{id}/poll', [DoctorMessagesController::class, 'poll'])->name('messages.poll');
    Route::get('/messages/unread-count', [DoctorMessagesController::class, 'unreadCount'])->name('messages.unread-count');

    // ── Alerts & Notifications (static before {id}) ──
    Route::get('/alerts/inbox',           [DoctorAlertNotificationController::class, 'inbox'])          ->name('alerts.inbox');
    Route::get('/alerts/outbox',          [DoctorAlertNotificationController::class, 'outbox'])         ->name('alerts.outbox');
    Route::post('/alerts/send',           [DoctorAlertNotificationController::class, 'send'])           ->name('alerts.send');
    Route::post('/alerts/mark-all-read',  [DoctorAlertNotificationController::class, 'markAllRead'])    ->name('alerts.mark-all-read');
    Route::get('/alerts/unread-count',    [DoctorAlertNotificationController::class, 'getUnreadCount']) ->name('alerts.unread-count');
    Route::post('/alerts/{id}/mark-read',   [DoctorAlertNotificationController::class, 'markAsRead'])   ->name('alerts.mark-read');
    Route::post('/alerts/{id}/acknowledge', [DoctorAlertNotificationController::class, 'acknowledge'])  ->name('alerts.acknowledge');
    Route::delete('/alerts/{id}',           [DoctorAlertNotificationController::class, 'destroy'])      ->name('alerts.destroy');

    // Task Management
    Route::post('/tasks/assign', [DoctorAlertNotificationController::class, 'assignTask'])->name('tasks.assign');
    Route::post('/tasks/quick-assign', [DoctorAlertNotificationController::class, 'quickAssignTask'])->name('tasks.quick-assign');
    Route::delete('/tasks/{id}', [DoctorAlertNotificationController::class, 'destroyTask'])->name('tasks.destroy');

    // Team & Schedule
    Route::get('/team-schedule', [DoctorTeamScheduleController::class, 'index'])->name('team-schedule');
    Route::post('/leave/apply', [DoctorTeamScheduleController::class, 'applyLeave'])->name('leave.apply');

    // Settings
    Route::get('/setting',               [DoctorSettingsController::class, 'index'])             ->name('setting');
    Route::post('/setting/profile',      [DoctorSettingsController::class, 'updateProfile'])     ->name('setting.profile');
    Route::post('/setting/password',     [DoctorSettingsController::class, 'updatePassword'])    ->name('setting.password');
    Route::post('/setting/availability', [DoctorSettingsController::class, 'updateAvailability'])->name('setting.availability');
});

// ============================================
// RECEPTIONIST ROUTES
// ============================================
Route::middleware(['auth', 'role:receptionist'])->prefix('receptionist')->name('receptionist.')->group(function () {

    Route::get('/dashboard', [App\Http\Controllers\Receptionist\ReceptionistDashboardController::class, 'index'])->name('dashboard');

    // Patient Management
    Route::get('/patients/register', [App\Http\Controllers\Receptionist\ReceptionistPatientController::class, 'register'])->name('patients.register');
    Route::post('/patients/store', [App\Http\Controllers\Receptionist\ReceptionistPatientController::class, 'store'])->name('patients.store');
    Route::get('/patients/search', [App\Http\Controllers\Receptionist\ReceptionistPatientController::class, 'search'])->name('patients.search');
    Route::get('/patients/{patientId}/history', [App\Http\Controllers\Receptionist\ReceptionistPatientHistoryController::class, 'show'])->name('patients.history');

    // Appointments
    Route::get('/appointments', [App\Http\Controllers\Receptionist\ReceptionistAppointmentController::class, 'index'])->name('appointments');
    Route::get('/appointments/create', [App\Http\Controllers\Receptionist\ReceptionistAppointmentController::class, 'create'])->name('appointments.create');
    Route::get('/appointments/search-checkin', [App\Http\Controllers\Receptionist\ReceptionistCheckInController::class, 'searchForCheckIn'])->name('appointments.search-checkin');
    Route::post('/appointments/store', [App\Http\Controllers\Receptionist\ReceptionistAppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{id}', [App\Http\Controllers\Receptionist\ReceptionistAppointmentController::class, 'show'])->name('appointments.show');
    Route::post('/appointments/{id}/reschedule', [App\Http\Controllers\Receptionist\ReceptionistAppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::post('/appointments/{id}/cancel', [App\Http\Controllers\Receptionist\ReceptionistAppointmentController::class, 'cancel'])->name('appointments.cancel');

    // Walk-In
    Route::get('/walk-in', [App\Http\Controllers\Receptionist\ReceptionistWalkInController::class, 'create'])->name('walk-in.create');
    Route::post('/walk-in/store', [App\Http\Controllers\Receptionist\ReceptionistWalkInController::class, 'store'])->name('walk-in.store');

    // Check-In & Queue
    Route::get('/check-in', [App\Http\Controllers\Receptionist\ReceptionistCheckInController::class, 'index'])->name('check-in');
    Route::post('/check-in/{appointmentId}/process', [App\Http\Controllers\Receptionist\ReceptionistCheckInController::class, 'process'])->name('check-in.process');
    Route::get('/queue-ticket/{appointmentId}', [App\Http\Controllers\Receptionist\ReceptionistCheckInController::class, 'showQueueTicket'])->name('queue-ticket');
    Route::get('/queue-display', [App\Http\Controllers\Receptionist\ReceptionistCheckInController::class, 'queueDisplay'])->name('queue-display');

    // Checkout & Payment
    Route::get('/checkout', [App\Http\Controllers\Receptionist\ReceptionistCheckOutController::class, 'index'])->name('checkout.index');
    Route::get('/checkout/{appointmentId}', [App\Http\Controllers\Receptionist\ReceptionistCheckOutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{appointmentId}/process', [App\Http\Controllers\Receptionist\ReceptionistCheckOutController::class, 'processCheckout'])->name('checkout.process');
    Route::get('/checkout/{appointmentId}/receipt', [App\Http\Controllers\Receptionist\ReceptionistCheckOutController::class, 'receipt'])->name('checkout.receipt');

    // Doctor Availability
    Route::get('/doctor-availability', [App\Http\Controllers\Receptionist\ReceptionistDoctorAvailabilityController::class, 'index'])->name('doctor-availability');

    // Advanced Search
    Route::get('/search/advanced', [App\Http\Controllers\Receptionist\ReceptionistAdvancedSearchController::class, 'index'])->name('search.advanced');
    Route::post('/search/advanced/export', [App\Http\Controllers\Receptionist\ReceptionistAdvancedSearchController::class, 'export'])->name('search.export');

    // Reminders
    Route::get('/reminders', [App\Http\Controllers\Receptionist\ReceptionistReminderController::class, 'index'])->name('reminders.index');
    Route::post('/reminders/{appointmentId}/create', [App\Http\Controllers\Receptionist\ReceptionistReminderController::class, 'create'])->name('reminders.create');
    Route::post('/reminders/{reminderId}/send', [App\Http\Controllers\Receptionist\ReceptionistReminderController::class, 'send'])->name('reminders.send');
    Route::post('/reminders/{reminderId}/cancel', [App\Http\Controllers\Receptionist\ReceptionistReminderController::class, 'cancel'])->name('reminders.cancel');
    Route::post('/reminders/send-all-pending', [App\Http\Controllers\Receptionist\ReceptionistReminderController::class, 'sendAllPending'])->name('reminders.send-all-pending');
    Route::post('/reminders/retry-failed', [App\Http\Controllers\Receptionist\ReceptionistReminderController::class, 'retryFailed'])->name('reminders.retry-failed');

    // ── Alerts (static before {id}) ──
    Route::get('/alerts',                  [App\Http\Controllers\Receptionist\ReceptionistAlertController::class, 'index'])          ->name('alerts.index');
    Route::post('/alerts/send',            [App\Http\Controllers\Receptionist\ReceptionistAlertController::class, 'send'])           ->name('alerts.send');        // ✅ ADDED
    Route::post('/alerts/mark-all-read',   [App\Http\Controllers\Receptionist\ReceptionistAlertController::class, 'markAllRead'])    ->name('alerts.mark-all-read');
    Route::get('/alerts/unread-count',     [App\Http\Controllers\Receptionist\ReceptionistAlertController::class, 'getUnreadCount']) ->name('alerts.unread-count');
    Route::post('/alerts/{id}/mark-read',  [App\Http\Controllers\Receptionist\ReceptionistAlertController::class, 'markAsRead'])     ->name('alerts.mark-read');
    Route::delete('/alerts/{id}',          [App\Http\Controllers\Receptionist\ReceptionistAlertController::class, 'destroy'])        ->name('alerts.destroy');

    // Messages
    Route::get('/messages', [App\Http\Controllers\Receptionist\ReceptionistMessagesController::class, 'index'])->name('messages');
    Route::post('/messages/create', [App\Http\Controllers\Receptionist\ReceptionistMessagesController::class, 'create'])->name('messages.create');
    Route::post('/messages/send', [App\Http\Controllers\Receptionist\ReceptionistMessagesController::class, 'send'])->name('messages.send');
    Route::post('/messages/{id}/toggle-star', [App\Http\Controllers\Receptionist\ReceptionistMessagesController::class, 'toggleStar'])->name('messages.toggle-star');
    Route::post('/messages/{id}/mark-as-read', [App\Http\Controllers\Receptionist\ReceptionistMessagesController::class, 'markAsRead'])->name('messages.mark-as-read');
    Route::get('/messages/templates', [App\Http\Controllers\Receptionist\ReceptionistMessagesController::class, 'getTemplates'])->name('messages.templates');
    Route::get('/messages/inbox-poll', [App\Http\Controllers\Receptionist\ReceptionistMessagesController::class, 'pollInbox'])->name('messages.inbox-poll');
    Route::get('/messages/{id}/poll', [App\Http\Controllers\Receptionist\ReceptionistMessagesController::class, 'poll'])->name('messages.poll');
    Route::get('/messages/unread-count', [App\Http\Controllers\Receptionist\ReceptionistMessagesController::class, 'unreadCount'])->name('messages.unread-count');

    // Settings
    Route::get('/setting',           [ReceptionistSettingsController::class, 'index'])          ->name('setting');
    Route::post('/setting/profile',  [ReceptionistSettingsController::class, 'updateProfile'])  ->name('setting.profile');
    Route::post('/setting/password', [ReceptionistSettingsController::class, 'updatePassword']) ->name('setting.password');
});

// ============================================
// NURSE ROUTES
// ============================================
Route::middleware(['auth', 'role:nurse'])->prefix('nurse')->name('nurse.')->group(function () {

    Route::get('/dashboard', [NurseDashboardController::class, 'index'])->name('dashboard');

    // Work Dashboard
    Route::get('/work-dashboard', [NurseWorkDashboardController::class, 'index'])->name('work-dashboard');
    Route::get('/work/refresh-counts', [NurseWorkDashboardController::class, 'refreshCounts'])->name('work.refresh-counts');
    Route::post('/work/start/{taskId}', [NurseWorkDashboardController::class, 'startTask'])->name('work.start');
    Route::post('/work/complete/{taskId}', [NurseWorkDashboardController::class, 'completeTask'])->name('work.complete');

    // ── Alerts (ALL static routes BEFORE the {id} wildcard) ──
    Route::get('/alerts',                   [NurseAlertsController::class, 'index'])        ->name('alerts');
    Route::post('/alerts/send',             [NurseAlertsController::class, 'send'])         ->name('alerts.send');           // ✅ ADDED
    Route::post('/alerts/send-to-doctor',   [NurseAlertsController::class, 'sendToDoctor']) ->name('alerts.send-to-doctor');
    Route::post('/alerts/send-to-admin',    [NurseAlertsController::class, 'sendToAdmin'])  ->name('alerts.send-to-admin');  // ✅ ADDED
    Route::post('/alerts/mark-all-read',    [NurseAlertsController::class, 'markAllRead'])  ->name('alerts.mark-all-read');
    Route::get('/alerts/unread-count',      [NurseAlertsController::class, 'getUnreadCount'])->name('alerts.unread-count');
    Route::post('/alerts/{id}/mark-read',   [NurseAlertsController::class, 'markAsRead'])   ->name('alerts.mark-read');
    Route::post('/alerts/{id}/acknowledge', [NurseAlertsController::class, 'acknowledge'])  ->name('alerts.acknowledge');

    // Tasks
    Route::get('/tasks', [NurseTasksController::class, 'index'])->name('tasks');
    Route::get('/tasks/{id}', [NurseTasksController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{id}/start', [NurseTasksController::class, 'startTask'])->name('tasks.start');
    Route::post('/tasks/{id}/complete', [NurseTasksController::class, 'completeTask'])->name('tasks.complete');
    Route::post('/tasks/{id}/cancel', [NurseTasksController::class, 'cancelTask'])->name('tasks.cancel');
    Route::post('/tasks/{id}/update-status', [NurseTasksController::class, 'updateStatus'])->name('tasks.update-status');

    // Queue Management
    Route::get('/queue-management', [NurseQueueController::class, 'index'])->name('queue-management');
    Route::post('/call-patient/{appointmentId}', [NurseQueueController::class, 'callPatient'])->name('call-patient');

    // Appointments
    Route::get('/appointments', [NurseAppointmentsController::class, 'index'])->name('appointments');
    Route::post('/appointments/{id}/mark-ready', [NurseAppointmentsController::class, 'markReadyForDoctor'])->name('appointments.mark-ready');
    Route::post('/appointments/{id}/start-vitals', [NurseAppointmentsController::class, 'startVitalsRecording'])->name('appointments.start-vitals');
    Route::post('/appointments/{id}/quick-vitals', [NurseAppointmentsController::class, 'quickRecordVitals'])->name('appointments.quick-vitals');
    Route::get('/appointments/refresh-counts', [NurseAppointmentsController::class, 'refreshCounts'])->name('appointments.refresh-counts');

    // Patients & Vitals
    Route::get('/patients', [NursePatientsController::class, 'index'])->name('patients');
    Route::get('/patients/{id}', [NursePatientsController::class, 'show'])->name('patients.show');
    Route::post('/vitals/store', [NursePatientsController::class, 'storeVitals'])->name('vitals.store');
    Route::post('/vitals/start/{appointmentId}', [NursePatientsController::class, 'startVitalsRecording'])->name('vitals.start');

    // Vitals Analytics
    Route::get('/vitals-analytics', [NurseVitalsAnalyticsController::class, 'index'])->name('vitals-analytics');
    Route::get('/vitals-analytics/export/{patientId}', [NurseVitalsAnalyticsController::class, 'exportReport'])->name('vitals-analytics.export');

    // Reports & Documentation
    Route::get('/reports-documentation', [NurseReportsController::class, 'index'])->name('reports-documentation');
    Route::post('/reports', [NurseReportsController::class, 'store'])->name('reports.store');
    Route::get('/reports/{id}', [NurseReportsController::class, 'show'])->name('reports.show');
    Route::get('/reports/filter/{category}', [NurseReportsController::class, 'filterByCategory'])->name('reports.filter');
    Route::get('/reports/{id}/pdf', [NurseReportsController::class, 'exportPdf'])->name('reports.pdf');

    // Team & Schedule
    Route::get('/team-schedule', [NurseTeamScheduleController::class, 'index'])->name('team-schedule');
    Route::post('/leave/apply', [NurseTeamScheduleController::class, 'applyLeave'])->name('leave.apply');

    // Messages
    Route::get('/messages', [NurseMessagesController::class, 'index'])->name('messages');
    Route::post('/messages/create', [NurseMessagesController::class, 'create'])->name('messages.create');
    Route::post('/messages/send', [NurseMessagesController::class, 'send'])->name('messages.send');
    Route::post('/messages/{id}/toggle-star', [NurseMessagesController::class, 'toggleStar'])->name('messages.toggle-star');
    Route::post('/messages/{id}/mark-as-read', [NurseMessagesController::class, 'markAsRead'])->name('messages.mark-as-read');
    Route::get('/messages/templates', [NurseMessagesController::class, 'getTemplates'])->name('messages.templates');
    Route::get('/messages/inbox-poll', [NurseMessagesController::class, 'pollInbox'])->name('messages.inbox-poll');
    Route::get('/messages/{id}/poll', [NurseMessagesController::class, 'poll'])->name('messages.poll');
    Route::get('/messages/unread-count', [NurseMessagesController::class, 'unreadCount'])->name('messages.unread-count');

    // Settings
    Route::get('/settings',           [NurseSettingsController::class, 'index'])          ->name('settings');
    Route::post('/settings/profile',  [NurseSettingsController::class, 'updateProfile'])  ->name('settings.profile');
    Route::post('/settings/password', [NurseSettingsController::class, 'updatePassword']) ->name('settings.password');
});

// ============================================
// PHARMACIST ROUTES
// ============================================
Route::middleware(['auth', 'role:pharmacist'])->prefix('pharmacist')->name('pharmacist.')->group(function () {

    Route::get('/dashboard', [PharmacistDashboardController::class, 'index'])->name('dashboard');

    // Prescriptions
    Route::get('/prescriptions', [PharmacistPrescriptionController::class, 'index'])->name('prescriptions');
    Route::get('/prescriptions/{id}', [PharmacistPrescriptionController::class, 'show'])->name('prescriptions.show');
    Route::post('/prescriptions/{id}/verify', [PharmacistPrescriptionController::class, 'verify'])->name('prescriptions.verify');
    Route::post('/prescriptions/{id}/reject', [PharmacistPrescriptionController::class, 'reject'])->name('prescriptions.reject');
    Route::post('/prescriptions/{id}/dispense', [PharmacistPrescriptionController::class, 'dispense'])->name('prescriptions.dispense');

    // Inventory — static routes BEFORE {id} routes
    Route::get('/inventory', [PharmacistInventoryController::class, 'index'])->name('inventory');
    Route::get('/inventory/create', [PharmacistInventoryController::class, 'create'])->name('inventory.create');
    Route::get('/inventory/export', [PharmacistInventoryController::class, 'export'])->name('inventory.export');
    Route::get('/inventory/low-stock-report', [PharmacistInventoryController::class, 'lowStockReport'])->name('inventory.low-stock-report');
    Route::post('/inventory', [PharmacistInventoryController::class, 'store'])->name('inventory.store');
    Route::post('/inventory/bulk-update', [PharmacistInventoryController::class, 'bulkUpdate'])->name('inventory.bulk-update');
    Route::get('/inventory/{id}/show', [PharmacistInventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{id}/edit', [PharmacistInventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{id}', [PharmacistInventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{id}', [PharmacistInventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/inventory/{id}/adjust-stock', [PharmacistInventoryController::class, 'adjustStock'])->name('inventory.adjust-stock');
    Route::get('/inventory/{id}/stock-history', [PharmacistInventoryController::class, 'stockHistory'])->name('inventory.stock-history');
    Route::post('/inventory/{id}/mark-expired', [PharmacistInventoryController::class, 'markExpired'])->name('inventory.mark-expired');

    // Restock
    Route::prefix('restock')->name('restock.')->group(function () {
        Route::get('/', [PharmacistRestockController::class, 'index'])->name('index');
        Route::get('/create', [PharmacistRestockController::class, 'create'])->name('create');
        Route::post('/store', [PharmacistRestockController::class, 'store'])->name('store');
        Route::get('/{id}', [PharmacistRestockController::class, 'show'])->name('show');
        Route::post('/{id}/mark-ordered', [PharmacistRestockController::class, 'markAsOrdered'])->name('mark-ordered');
        Route::post('/{id}/cancel', [PharmacistRestockController::class, 'cancel'])->name('cancel');
    });

    // Stock Receipts
    Route::prefix('receipts')->name('receipts.')->group(function () {
        Route::get('/', [PharmacistStockReceiptController::class, 'index'])->name('index');
        Route::get('/create', [PharmacistStockReceiptController::class, 'create'])->name('create');
        Route::post('/store', [PharmacistStockReceiptController::class, 'store'])->name('store');
        Route::get('/{id}', [PharmacistStockReceiptController::class, 'show'])->name('show');
    });

    // Disposals
    Route::prefix('disposals')->name('disposals.')->group(function () {
        Route::get('/', [PharmacistDisposalController::class, 'index'])->name('index');
        Route::get('/create', [PharmacistDisposalController::class, 'create'])->name('create');
        Route::post('/store', [PharmacistDisposalController::class, 'store'])->name('store');
        Route::get('/{id}', [PharmacistDisposalController::class, 'show'])->name('show');
    });

    // ── Alerts (ALL static routes BEFORE {id} wildcard) ──
    Route::get('/alerts',                   [PharmacistAlertController::class, 'index'])         ->name('alerts');
    Route::post('/alerts/send',             [PharmacistAlertController::class, 'send'])          ->name('alerts.send');        // ✅ ADDED
    Route::post('/alerts/mark-all-read',    [PharmacistAlertController::class, 'markAllRead'])   ->name('alerts.mark-all-read');
    Route::get('/alerts/unread-count',      [PharmacistAlertController::class, 'getUnreadCount'])->name('alerts.unread-count');
    Route::post('/alerts/{id}/mark-read',   [PharmacistAlertController::class, 'markAsRead'])    ->name('alerts.mark-read');
    Route::post('/alerts/{id}/resolve',     [PharmacistAlertController::class, 'acknowledge'])   ->name('alerts.resolve');

    // Reports
    Route::get('/reports', [PharmacistReportController::class, 'index'])->name('reports');
    Route::post('/reports/generate', [PharmacistReportController::class, 'generateCustomReport'])->name('reports.generate');

    // Messages
    Route::get('/messages', [PharmacistMessagesController::class, 'index'])->name('messages');
    Route::post('/messages/create', [PharmacistMessagesController::class, 'create'])->name('messages.create');
    Route::post('/messages/send', [PharmacistMessagesController::class, 'send'])->name('messages.send');
    Route::post('/messages/{id}/toggle-star', [PharmacistMessagesController::class, 'toggleStar'])->name('messages.toggle-star');
    Route::post('/messages/{id}/mark-as-read', [PharmacistMessagesController::class, 'markAsRead'])->name('messages.mark-as-read');
    Route::get('/messages/templates', [PharmacistMessagesController::class, 'getTemplates'])->name('messages.templates');
    Route::get('/messages/inbox-poll', [PharmacistMessagesController::class, 'pollInbox'])->name('messages.inbox-poll');
    Route::get('/messages/{id}/poll', [PharmacistMessagesController::class, 'poll'])->name('messages.poll');
    Route::get('/messages/unread-count', [PharmacistMessagesController::class, 'unreadCount'])->name('messages.unread-count');

    // Settings
    Route::get('/setting',               [PharmacistSettingsController::class, 'index'])             ->name('setting');
    Route::post('/setting/profile',      [PharmacistSettingsController::class, 'updateProfile'])     ->name('setting.profile');
    Route::post('/setting/password',     [PharmacistSettingsController::class, 'updatePassword'])    ->name('setting.password');
    Route::post('/setting/availability', [PharmacistSettingsController::class, 'updateAvailability'])->name('setting.availability');
});

require __DIR__ . '/auth.php';