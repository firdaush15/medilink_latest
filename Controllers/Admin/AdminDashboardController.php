<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    // Summary statistics
    $totalDoctors = Doctor::count();
    $newDoctors = Doctor::where('created_at', '>=', now()->subWeek())->count();

    $totalPatients = Patient::count();
    $newPatients = Patient::where('created_at', '>=', now()->subWeek())->count();

    $todayAppointments = Appointment::whereDate('appointment_date', today())
        ->where('status', 'confirmed')
        ->count();

    $completedToday = Appointment::whereDate('appointment_date', today())
        ->where('status', 'completed')
        ->count();

    $cancelledAppointments = Appointment::where('status', 'cancelled')->count();
    $cancelledThisWeek = Appointment::where('status', 'cancelled')
        ->where('created_at', '>=', now()->subWeek())
        ->count();

    // Table data
    $pendingAppointments = Appointment::where('status', 'pending')
        ->with(['doctor.user', 'patient.user'])
        ->orderBy('appointment_date', 'asc')
        ->take(5)
        ->get();

    $upcomingAppointments = Appointment::whereDate('appointment_date', '>=', today())
        ->where('status', 'confirmed')
        ->with(['doctor.user', 'patient.user'])
        ->orderBy('appointment_date', 'asc')
        ->paginate(5);

    // ✅ NEW: Doctor activity this week
    $doctorActivities = Doctor::with(['user', 'appointments' => function ($query) {
        $query->whereBetween('appointment_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }])->get();

    return view('admin.admin_dashboard', compact(
        'totalDoctors',
        'newDoctors',
        'totalPatients',
        'newPatients',
        'todayAppointments',
        'completedToday',
        'cancelledAppointments',
        'cancelledThisWeek',
        'pendingAppointments',
        'upcomingAppointments',
        'doctorActivities' // ✅ include it here
    ));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
