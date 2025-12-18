<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\StaffAlert;
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
        // ✅ Queue Management Service
        $this->app->singleton(QueueManagementService::class, function ($app) {
            return new QueueManagementService();
        });

        // ✅ Nurse Assignment Service
        $this->app->singleton(NurseAssignmentService::class, function ($app) {
            return new NurseAssignmentService();
        });

        // ✅ Check-In Validation Service
        $this->app->singleton(CheckInValidationService::class, function ($app) {
            return new CheckInValidationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ✅ Share unreadCount with receptionist sidebar
        View::composer('receptionist.sidebar.receptionist_sidebar', function ($view) {
            $unreadCount = 0;
            
            if (Auth::check() && Auth::user()->role === 'receptionist') {
                $unreadCount = StaffAlert::where('recipient_id', Auth::id())
                    ->where('recipient_type', 'receptionist')
                    ->where('is_read', false)
                    ->count();
            }
            
            $view->with('unreadCount', $unreadCount);
        });
    }
}