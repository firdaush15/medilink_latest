<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\DoctorRating;
use App\Models\Doctor;
use Carbon\Carbon;

class DoctorDashboardController extends Controller
{
    public function index()
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();

        if (!$doctor) {
            abort(404, 'Doctor profile not found.');
        }

        $doctorId = $doctor->doctor_id;

        // 🩺 Count today's appointments
        $todayAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', today())
            ->count();

        // ✅ Count completed appointments today
        $completedAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', today())
            ->where('status', Appointment::STATUS_COMPLETED)
            ->count();

        // 👥 Count total unique patients this doctor has seen
        $totalPatients = Appointment::where('doctor_id', $doctorId)
            ->distinct('patient_id')
            ->count('patient_id');

        // 💊 Pending prescriptions (appointments without prescriptions)
        $pendingPrescriptions = Appointment::where('doctor_id', $doctorId)
            ->where('status', Appointment::STATUS_COMPLETED)
            ->whereDoesntHave('prescriptions')
            ->count();

        // 📩 Placeholder for unread messages
        $unreadMessages = 7;

        // ⭐ Average patient rating and count
        $rating = DoctorRating::where('doctor_id', $doctorId)->avg('rating') ?? 0;
        $ratingCount = DoctorRating::where('doctor_id', $doctorId)->count();

        // 📅 Paginated schedule for today's appointments with PROPER STATUS DISPLAY
        $todaySchedule = Appointment::where('appointments.doctor_id', $doctorId)
            ->whereDate('appointment_date', Carbon::today())
            ->join('patients', 'appointments.patient_id', '=', 'patients.patient_id')
            ->join('users', 'patients.user_id', '=', 'users.id')
            ->select(
                'appointments.appointment_id',
                'appointments.status',
                'users.name as patient',
                'appointments.appointment_time as time',
                'appointments.reason as type'
            )
            ->orderBy('appointments.appointment_time', 'asc')
            ->paginate(10);

        // 🩺 Mocked recent activities
        $recentActivities = [
            ['icon' => '🩺', 'text' => 'Aliyah Nadhira updated health record.'],
            ['icon' => '🆕', 'text' => 'Farid Iqmal registered and booked appointment.'],
            ['icon' => '💊', 'text' => 'Prescription refill request from Nora Binti Azlan.'],
        ];

        $notifications = [
            '❌ Appointment cancelled by Adam Ali.',
            '⚙️ System update: New lab report format available.',
            '📋 Reminder: Complete pending prescriptions.',
        ];

        $messages = [
            'From: Nurse Aina — Patient Farah Husna waiting at Room 3.',
            'From: Admin — Submit your weekly consultation summary.',
            'From: Patient Lisa Wong — Follow-up appointment query.',
        ];

        return view('doctor.doctor_dashboard', compact(
            'todayAppointments',
            'completedAppointments',
            'totalPatients',
            'pendingPrescriptions',
            'unreadMessages',
            'rating',
            'ratingCount',
            'todaySchedule',
            'recentActivities',
            'notifications',
            'messages'
        ));
    }
}