<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaffShift;
use App\Models\User;
use App\Models\ShiftTemplate;
use Carbon\Carbon;

class StaffShiftSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📅 Creating 24-HOUR staff shifts for today and this week...');

        // Get templates (Assuming you want to use the Full Day template for the 24h test)
        $fullDayShift = ShiftTemplate::where('template_name', 'Full Day')->first();

        // Get all staff
        $doctors = User::where('role', 'doctor')->get();
        $nurses = User::where('role', 'nurse')->get();
        $pharmacists = User::where('role', 'pharmacist')->get();
        $receptionists = User::where('role', 'receptionist')->get();

        $shiftsCreated = 0;

        // Define 24-Hour Times
        $startTime = '00:00:00';
        $endTime   = '23:59:59';

        // ========================================
        // CREATE SHIFTS FOR TODAY (All checked-in)
        // ========================================
        $today = Carbon::today();

        // Helper to create a shift
        $createShift = function ($user, $role) use ($today, $fullDayShift, $startTime, $endTime, &$shiftsCreated) {
            StaffShift::create([
                'user_id' => $user->id,
                'staff_role' => $role,
                'template_id' => $fullDayShift->template_id,
                'shift_date' => $today,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'checked_in', // ✅ Already checked in
                // Check in around midnight (00:00 to 00:15)
                'actual_check_in' => $today->copy()->startOfDay()->addMinutes(rand(0, 15)),
                'assigned_by' => 1,
            ]);
            $shiftsCreated++;
        };

        // Doctors - 24 Hour
        foreach ($doctors as $doctor) {
            $createShift($doctor, 'doctor');
        }

        // Nurses - 24 Hour (Simplified from rotation to full 24h for testing)
        foreach ($nurses as $nurse) {
            $createShift($nurse, 'nurse');
        }

        // Pharmacists - 24 Hour
        foreach ($pharmacists as $pharmacist) {
            $createShift($pharmacist, 'pharmacist');
        }

        // Receptionists - 24 Hour
        foreach ($receptionists as $receptionist) {
            $createShift($receptionist, 'receptionist');
        }

        // ========================================
        // CREATE SHIFTS FOR REST OF THE WEEK (Scheduled)
        // ========================================
        for ($day = 1; $day <= 6; $day++) {
            $date = $today->copy()->addDays($day);

            // Skip Sundays (optional)
            if ($date->isSunday()) {
                continue;
            }

            // Create scheduled shifts for ALL staff types
            $allStaff = $doctors->concat($nurses)->concat($pharmacists)->concat($receptionists);

            foreach ($allStaff as $staff) {
                StaffShift::create([
                    'user_id' => $staff->id,
                    'staff_role' => $staff->role, // Ensure your User model has a 'role' attribute or accessor
                    'template_id' => $fullDayShift->template_id,
                    'shift_date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => 'scheduled',
                    'assigned_by' => 1,
                ]);
                $shiftsCreated++;
            }
        }

        $this->command->info("✅ Created {$shiftsCreated} 24-hour staff shifts");
        $this->command->info("   - Today: All staff checked in (00:00 - 23:59)");
        $this->command->info("   - This week: Scheduled 24h shifts created");
    }
}