<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaffTask;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use Carbon\Carbon;

class StaffTaskSeeder extends Seeder
{
    public function run()
    {
        // Get some users
        $doctors = User::where('role', 'doctor')->get();
        $nurses = User::where('role', 'nurse')->get();
        $pharmacists = User::where('role', 'pharmacist')->get();
        $receptionists = User::where('role', 'receptionist')->get();
        
        $patients = Patient::with('appointments')->limit(5)->get();

        // Sample tasks for testing
        $tasks = [];

        // Doctor → Nurse tasks (replaces old NurseTask)
        if ($doctors->isNotEmpty() && $nurses->isNotEmpty()) {
            foreach ($patients as $patient) {
                $appointment = $patient->appointments->first();
                
                // ✅ FIX: Properly combine date and time
                $appointmentDateTime = $appointment 
                    ? Carbon::parse($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time->format('H:i:s'))
                    : now()->addHours(2);
                
                $tasks[] = [
                    'assigned_by_id' => $doctors->random()->id,
                    'assigned_by_type' => 'doctor',
                    'assigned_to_id' => $nurses->random()->id,
                    'assigned_to_type' => 'nurse',
                    'patient_id' => $patient->patient_id,
                    'appointment_id' => $appointment?->appointment_id,
                    'task_type' => 'Vital Signs Check',
                    'priority' => ['High', 'Urgent', 'Normal'][rand(0, 2)],
                    'task_title' => 'Check vitals before appointment',
                    'task_description' => 'Patient has history of hypertension. Please record BP, pulse, temp.',
                    'due_at' => $appointment ? $appointmentDateTime->copy()->subMinutes(15) : now()->addHours(2),
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $tasks[] = [
                    'assigned_by_id' => $doctors->random()->id,
                    'assigned_by_type' => 'doctor',
                    'assigned_to_id' => $nurses->random()->id,
                    'assigned_to_type' => 'nurse',
                    'patient_id' => $patient->patient_id,
                    'appointment_id' => $appointment?->appointment_id,
                    'task_type' => 'Prepare Patient',
                    'priority' => 'Normal',
                    'task_title' => 'Prepare patient for consultation',
                    'task_description' => 'Ensure patient has filled out medical history form.',
                    'due_at' => $appointment ? $appointmentDateTime->copy()->subMinutes(30) : now()->addHours(3),
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Doctor → Pharmacist tasks
        if ($doctors->isNotEmpty() && $pharmacists->isNotEmpty()) {
            $tasks[] = [
                'assigned_by_id' => $doctors->random()->id,
                'assigned_by_type' => 'doctor',
                'assigned_to_id' => $pharmacists->random()->id,
                'assigned_to_type' => 'pharmacist',
                'patient_id' => $patients->first()->patient_id ?? null,
                'task_type' => 'Prescription Verification',
                'priority' => 'Urgent',
                'task_title' => 'Verify prescription for drug interactions',
                'task_description' => 'Patient allergic to penicillin. Please verify alternatives.',
                'due_at' => now()->addHours(2),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $tasks[] = [
                'assigned_by_id' => $doctors->random()->id,
                'assigned_by_type' => 'doctor',
                'assigned_to_id' => $pharmacists->random()->id,
                'assigned_to_type' => 'pharmacist',
                'patient_id' => $patients->last()->patient_id ?? null,
                'task_type' => 'Medication Availability',
                'priority' => 'Normal',
                'task_title' => 'Check stock for prescribed medication',
                'task_description' => 'Please confirm availability of Amoxicillin 500mg.',
                'due_at' => now()->addHours(4),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Doctor → Receptionist tasks
        if ($doctors->isNotEmpty() && $receptionists->isNotEmpty()) {
            $tasks[] = [
                'assigned_by_id' => $doctors->random()->id,
                'assigned_by_type' => 'doctor',
                'assigned_to_id' => $receptionists->random()->id,
                'assigned_to_type' => 'receptionist',
                'patient_id' => $patients->first()->patient_id ?? null,
                'task_type' => 'Schedule Follow-up',
                'priority' => 'Normal',
                'task_title' => 'Schedule follow-up appointment',
                'task_description' => 'Patient needs follow-up in 2 weeks for blood test results.',
                'due_at' => now()->addDay(),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Sample completed tasks
        if ($doctors->isNotEmpty() && $nurses->isNotEmpty()) {
            $tasks[] = [
                'assigned_by_id' => $doctors->random()->id,
                'assigned_by_type' => 'doctor',
                'assigned_to_id' => $nurses->random()->id,
                'assigned_to_type' => 'nurse',
                'patient_id' => $patients->first()->patient_id ?? null,
                'task_type' => 'Vital Signs Check',
                'priority' => 'High',
                'task_title' => 'Emergency vitals check',
                'task_description' => 'Patient complained of chest pain.',
                'due_at' => now()->subHours(2),
                'status' => 'completed',
                'started_at' => now()->subHours(2),
                'completed_at' => now()->subHours(1),
                'completion_notes' => 'Vitals recorded: BP 130/85, HR 78, Temp 37.1°C. Patient stable.',
                'task_data' => json_encode([
                    'blood_pressure' => '130/85',
                    'heart_rate' => 78,
                    'temperature' => 37.1,
                    'oxygen_saturation' => 97,
                ]),
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(1),
            ];
        }

        // Insert all tasks
        foreach ($tasks as $task) {
            StaffTask::create($task);
        }

        $this->command->info('✅ Created ' . count($tasks) . ' staff tasks');
    }
}