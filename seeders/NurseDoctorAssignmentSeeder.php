<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nurse;
use App\Models\Doctor;
use App\Models\NurseDoctorAssignment;
use App\Models\NurseWorkloadTracking;

class NurseDoctorAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏥 Setting up Nurse-Doctor Team Assignments...');

        $doctors = Doctor::with('user')->get();
        $nurses = Nurse::with('user')->get();

        if ($doctors->isEmpty() || $nurses->isEmpty()) {
            $this->command->error('❌ No doctors or nurses found! Run user seeders first.');
            return;
        }

        $this->command->info("📊 Found {$doctors->count()} doctors and {$nurses->count()} nurses");

        $nurseIndex = 0;
        $assignments = 0;

        foreach ($doctors as $doctor) {
            $this->command->info("👨‍⚕️ Assigning nurses to Dr. {$doctor->user->name}");

            // Assign 2-3 primary nurses
            $primaryNursesCount = rand(2, 3);
            
            for ($i = 0; $i < $primaryNursesCount; $i++) {
                if ($nurseIndex >= $nurses->count()) {
                    $nurseIndex = 0;
                }

                $nurse = $nurses[$nurseIndex];
                
                NurseDoctorAssignment::create([
                    'nurse_id' => $nurse->nurse_id,
                    'doctor_id' => $doctor->doctor_id,
                    'assignment_type' => 'primary',
                    'priority_order' => $i + 1,
                    'working_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                    'shift_start' => '08:00:00',
                    'shift_end' => '17:00:00',
                    'is_active' => true,
                    'assigned_from' => now(),
                ]);

                $this->command->info("  ✓ {$nurse->user->name} as PRIMARY (Priority: " . ($i + 1) . ")");
                
                $assignments++;
                $nurseIndex++;
            }
        }

        // Initialize workload tracking
        $this->command->info('📊 Initializing workload tracking...');
        
        foreach ($nurses as $nurse) {
            NurseWorkloadTracking::firstOrCreate(
                ['nurse_id' => $nurse->nurse_id],
                [
                    'current_patients' => 0,
                    'pending_vitals' => 0,
                    'total_today' => 0,
                    'max_capacity' => 5,
                    'is_available' => true,
                    'current_status' => 'available',
                    'avg_completion_time_minutes' => 0,
                    'efficiency_score' => 100.00,
                ]
            );
        }

        $this->command->info("✅ Created {$assignments} nurse-doctor assignments");
        $this->command->info("✅ Initialized workload tracking for {$nurses->count()} nurses");
    }
}