<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\StaffAlert;
use App\Models\SystemNotification;
use App\Services\QueueManagementService;
use App\Services\NurseAssignmentService;
use App\Services\CheckInValidationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(QueueManagementService::class, function ($app) {
            return new QueueManagementService();
        });

        $this->app->singleton(NurseAssignmentService::class, function ($app) {
            return new NurseAssignmentService();
        });

        $this->app->singleton(CheckInValidationService::class, function ($app) {
            return new CheckInValidationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Helper closure shared by all role sidebars ───────────────────────
        $getAlertCounts = function (string $role): array {
            if (!Auth::check() || Auth::user()->role !== $role) {
                return ['unreadAlerts' => 0, 'criticalAlerts' => 0, 'totalBadge' => 0];
            }

            $userId = Auth::id();

            $unreadAlerts = StaffAlert::where('recipient_id', $userId)
                ->where('recipient_type', $role)
                ->where('is_read', false)
                ->count();

            $criticalAlerts = StaffAlert::where('recipient_id', $userId)
                ->where('recipient_type', $role)
                ->where('priority', 'Critical')
                ->where('is_acknowledged', false)
                ->count();

            // ✅ FIX: Doctor badge also counts SystemNotification
            // (task completion feedback still lives there via AppointmentWorkflowService)
            $systemUnread = 0;
            if ($role === 'doctor') {
                $systemUnread = SystemNotification::where('user_id', $userId)
                    ->where('is_read', false)
                    ->count();
            }

            return [
                'unreadAlerts'  => $unreadAlerts,
                'criticalAlerts' => $criticalAlerts,
                'totalBadge'    => $unreadAlerts + $systemUnread,
            ];
        };

        // ── Receptionist sidebar ─────────────────────────────────────────────
        View::composer('receptionist.sidebar.receptionist_sidebar', function ($view) use ($getAlertCounts) {
            $counts = $getAlertCounts('receptionist');
            $view->with('unreadCount', $counts['totalBadge']);         // legacy key
            $view->with('unreadAlerts', $counts['unreadAlerts']);
            $view->with('criticalAlerts', $counts['criticalAlerts']);
        });

        // ── Doctor sidebar ───────────────────────────────────────────────────
        View::composer('doctor.sidebar.doctor_sidebar', function ($view) use ($getAlertCounts) {
            $counts = $getAlertCounts('doctor');
            $view->with('unreadCount', $counts['totalBadge']);
            $view->with('unreadAlerts', $counts['unreadAlerts']);
            $view->with('criticalAlerts', $counts['criticalAlerts']);
        });

        // ── Nurse sidebar ────────────────────────────────────────────────────
        View::composer('nurse.sidebar.nurse_sidebar', function ($view) use ($getAlertCounts) {
            $counts = $getAlertCounts('nurse');
            $view->with('unreadCount', $counts['totalBadge']);
            $view->with('unreadAlerts', $counts['unreadAlerts']);
            $view->with('criticalAlerts', $counts['criticalAlerts']);
        });

        // ── Pharmacist sidebar ───────────────────────────────────────────────
        View::composer('pharmacist.sidebar.pharmacist_sidebar', function ($view) use ($getAlertCounts) {
            $counts = $getAlertCounts('pharmacist');
            $view->with('unreadCount', $counts['totalBadge']);
            $view->with('unreadAlerts', $counts['unreadAlerts']);
            $view->with('criticalAlerts', $counts['criticalAlerts']);
        });
    }
}