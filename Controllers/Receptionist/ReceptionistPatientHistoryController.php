<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Http\Request;

class ReceptionistPatientHistoryController extends Controller
{
    /**
     * Get patient visit history (AJAX)
     */
    public function show($patientId)
    {
        $patient = Patient::with('user')->findOrFail($patientId);
        
        // Get recent visits
        $visits = Appointment::with(['doctor.user'])
            ->where('patient_id', $patientId)
            ->whereIn('status', [
                Appointment::STATUS_COMPLETED, 
                Appointment::STATUS_CANCELLED
            ])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->limit(10)
            ->get();

        // Calculate statistics
        $stats = $this->calculatePatientStats($patientId, $patient);

        return response()->json([
            'patient' => [
                'id' => $patient->patient_id,
                'name' => $patient->user->name,
                'email' => $patient->user->email,
                'phone' => $patient->phone_number,
                'is_flagged' => $patient->is_flagged ?? false,
                'flag_reason' => $patient->flag_reason ?? null,
            ],
            'stats' => $stats,
            'visits' => $visits->map(function ($visit) {
                return [
                    'date' => $visit->appointment_date->format('M d, Y'),
                    'doctor' => $visit->doctor->user->name,
                    'specialization' => $visit->doctor->specialization,
                    'status' => $visit->status,
                    'reason' => $visit->reason,
                    'payment_collected' => $visit->payment_collected ?? false,
                    'payment_amount' => $visit->payment_amount ?? 0,
                ];
            }),
        ]);
    }

    /**
     * Calculate patient statistics
     */
    private function calculatePatientStats($patientId, $patient)
    {
        return [
            'total_visits' => Appointment::where('patient_id', $patientId)
                ->where('status', Appointment::STATUS_COMPLETED)
                ->count(),
            
            'no_shows' => $patient->no_show_count ?? 0,
            
            'late_arrivals' => $patient->late_arrival_count ?? 0,
            
            'total_paid' => Appointment::where('patient_id', $patientId)
                ->where('payment_collected', true)
                ->sum('payment_amount') ?? 0,
        ];
    }

    /**
     * Show full patient history page (optional detailed view)
     */
    public function index($patientId)
    {
        $patient = Patient::with(['user', 'appointments.doctor.user'])
            ->findOrFail($patientId);

        return view('receptionist.patient-history.index', compact('patient'));
    }
}