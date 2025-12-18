<?php
// database/seeders/CancelledAppointmentsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\User;
use App\Models\StaffAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CancelledAppointmentsSeeder extends Seeder
{
    /**
     * Map cancellation source to valid sender_type
     */
    private function getSenderType($cancelledBy)
    {
        // Map to valid sender types in your staff_alerts table
        $mapping = [
            'patient' => 'system',      // Patient cancellations are system-generated alerts
            'doctor' => 'doctor',
            'admin' => 'admin',
        ];
        
        return $mapping[$cancelledBy] ?? 'system';
    }
    
    /**
     * Real-world cancellation scenarios with proper notifications
     */
    public function run(): void
    {
        $this->command->info('🚫 Seeding cancelled appointments (real-world scenarios)...');
        
        // Get necessary data
        $patients = Patient::with('user')->get();
        $doctors = Doctor::with('user')->get();
        $receptionists = User::where('role', 'receptionist')->get();
        $admin = User::where('role', 'admin')->first();
        
        if ($patients->isEmpty() || $doctors->isEmpty()) {
            $this->command->error('❌ No patients or doctors found. Run CompleteTestSeeder first.');
            return;
        }
        
        if ($receptionists->isEmpty()) {
            $this->command->error('❌ No receptionists found. Creating alerts will fail.');
        }
        
        $cancellationReasons = [
            'patient' => [
                'Feeling better, no longer need consultation',
                'Work emergency, cannot make it',
                'Traffic jam, running very late',
                'Family emergency',
                'Forgot about prior commitment',
                'Weather too bad (heavy rain)',
                'Transportation issue',
                'Child sick at home',
                'Changed mind, will reschedule later',
                'Double booked by mistake',
            ],
            'doctor' => [
                'Doctor called for emergency surgery',
                'Doctor on sudden sick leave',
                'Medical conference schedule conflict',
                'Family emergency',
                'Hospital emergency meeting',
            ],
            'admin' => [
                'Power outage - clinic closed',
                'Equipment malfunction',
                'Administrative error - duplicate booking',
                'Doctor running severely behind schedule',
            ],
        ];
        
        $cancelledCount = 0;
        
        // ========================================
        // SCENARIO 1: Patient cancellations (PAST - last 30 days)
        // ========================================
        $this->command->info('📅 Creating past patient cancellations...');
        
        for ($i = 0; $i < 15; $i++) {
            $daysAgo = rand(1, 30);
            $appointmentDate = Carbon::now()->subDays($daysAgo);
            
            $patient = $patients->random();
            $doctor = $doctors->random();
            
            $appointmentTime = $appointmentDate->copy()->setTime(rand(8, 17), [0, 30][rand(0, 1)]);
            $cancelledAt = $appointmentTime->copy()->subHours(rand(2, 48));
            
            $appointment = Appointment::create([
                'patient_id' => $patient->patient_id,
                'doctor_id' => $doctor->doctor_id,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime,
                'status' => Appointment::STATUS_CANCELLED,
                'reason' => 'Regular checkup',
                'cancelled_reason' => $cancellationReasons['patient'][array_rand($cancellationReasons['patient'])],
                'created_at' => $appointmentTime->copy()->subDays(5),
                'updated_at' => $cancelledAt,
            ]);
            
            $this->createCancellationAlerts($appointment, 'patient', true);
            $cancelledCount++;
        }
        
        // ========================================
        // SCENARIO 2: TODAY's cancellations
        // ========================================
        $this->command->info('📅 Creating TODAY\'s cancellations (some unread)...');
        
        $todayAppointments = Appointment::with('patient', 'doctor')
            ->whereDate('appointment_date', today())
            ->where('status', Appointment::STATUS_CONFIRMED)
            ->take(8)
            ->get();
        
        if ($todayAppointments->isEmpty()) {
            $this->command->warn('⚠️ No confirmed appointments today to cancel.');
        } else {
            foreach ($todayAppointments->take(5) as $appointment) {
                $reason = $cancellationReasons['patient'][array_rand($cancellationReasons['patient'])];
                
                $appointment->update([
                    'status' => Appointment::STATUS_CANCELLED,
                    'cancelled_reason' => $reason,
                ]);
                
                $isUnread = rand(1, 100) <= 30;
                $this->createCancellationAlerts($appointment, 'patient', !$isUnread);
                $cancelledCount++;
            }
        }
        
        // ========================================
        // SCENARIO 3: FUTURE cancellations
        // ========================================
        $this->command->info('📅 Creating FUTURE appointment cancellations...');
        
        for ($i = 0; $i < 10; $i++) {
            $daysAhead = rand(1, 14);
            $appointmentDate = Carbon::now()->addDays($daysAhead);
            
            $patient = $patients->random();
            $doctor = $doctors->random();
            
            $appointmentTime = $appointmentDate->copy()->setTime(rand(8, 17), [0, 30][rand(0, 1)]);
            
            $rand = rand(1, 100);
            if ($rand <= 80) {
                $cancelledBy = 'patient';
            } elseif ($rand <= 95) {
                $cancelledBy = 'doctor';
            } else {
                $cancelledBy = 'admin';
            }
            
            $appointment = Appointment::create([
                'patient_id' => $patient->patient_id,
                'doctor_id' => $doctor->doctor_id,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime,
                'status' => Appointment::STATUS_CANCELLED,
                'reason' => 'Regular consultation',
                'cancelled_reason' => $cancellationReasons[$cancelledBy][array_rand($cancellationReasons[$cancelledBy])],
                'created_at' => now()->subDays(rand(3, 10)),
                'updated_at' => now()->subHours(rand(1, 12)),
            ]);
            
            $isUnread = rand(1, 100) <= 60;
            $this->createCancellationAlerts($appointment, $cancelledBy, !$isUnread);
            $cancelledCount++;
        }
        
        // ========================================
        // SCENARIO 4: No-show appointments
        // ========================================
        $this->command->info('⚠️ Creating no-show appointments...');
        
        for ($i = 0; $i < 5; $i++) {
            $daysAgo = rand(1, 7);
            $appointmentDate = Carbon::now()->subDays($daysAgo);
            
            $patient = $patients->random();
            $doctor = $doctors->random();
            
            $appointmentTime = $appointmentDate->copy()->setTime(rand(8, 17), [0, 30][rand(0, 1)]);
            
            $appointment = Appointment::create([
                'patient_id' => $patient->patient_id,
                'doctor_id' => $doctor->doctor_id,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime,
                'status' => Appointment::STATUS_NO_SHOW,
                'reason' => 'Follow-up consultation',
                'cancelled_reason' => 'Patient did not show up and did not call',
            ]);
            
            DB::table('patients')
                ->where('patient_id', $patient->patient_id)
                ->increment('no_show_count');
            
            $noShowCount = DB::table('patients')
                ->where('patient_id', $appointment->patient_id)
                ->value('no_show_count');
            
            if ($noShowCount >= 3) {
                DB::table('patients')
                    ->where('patient_id', $patient->patient_id)
                    ->update([
                        'is_flagged' => true,
                        'flag_reason' => "Multiple no-shows ({$noShowCount} times)",
                    ]);
            }
            
            $this->createNoShowAlert($appointment);
            $cancelledCount++;
        }
        
        // ========================================
        // SCENARIO 5: Emergency cancellations
        // ========================================
        $this->command->info('🚨 Creating emergency last-minute cancellations...');
        
        $receptionistUser = $receptionists->isNotEmpty() ? $receptionists->first() : null;
        
        for ($i = 0; $i < 2; $i++) {
            $appointmentDate = Carbon::today();
            $patient = $patients->random();
            $doctor = $doctors->random();
            
            $appointmentTime = $appointmentDate->copy()->setTime(rand(9, 15), [0, 30][rand(0, 1)]);
            $arrivedAt = $appointmentTime->copy()->addMinutes(5);
            
            $appointment = Appointment::create([
                'patient_id' => $patient->patient_id,
                'doctor_id' => $doctor->doctor_id,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime,
                'status' => Appointment::STATUS_CANCELLED,
                'reason' => 'Regular consultation',
                'cancelled_reason' => 'Family emergency after check-in - had to leave immediately',
                'arrived_at' => $arrivedAt,
                'checked_in_by' => $receptionistUser ? $receptionistUser->id : null,
            ]);
            
            $this->createUrgentCancellationAlerts($appointment);
            $cancelledCount++;
        }
        
        $this->command->info("✅ Created {$cancelledCount} cancelled/no-show appointments");
        $this->command->info("📊 Breakdown:");
        $this->command->info("   - 15 Past patient cancellations (30 days ago)");
        $this->command->info("   - " . min(5, $todayAppointments->count()) . " Today's cancellations (some unread alerts)");
        $this->command->info("   - 10 Future appointment cancellations");
        $this->command->info("   - 5 No-show appointments (patient flagging)");
        $this->command->info("   - 2 Emergency last-minute cancellations (after check-in)");
    }
    
    /**
     * Create cancellation alerts with proper sender_type
     */
    private function createCancellationAlerts($appointment, $cancelledBy, $isRead = false)
    {
        $patientName = $appointment->patient->user->name;
        $doctorName = $appointment->doctor->user->name;
        $appointmentTime = $appointment->appointment_time->format('h:i A');
        $appointmentDate = $appointment->appointment_date->format('M d, Y');
        
        $message = "{$patientName} cancelled appointment with {$doctorName} on {$appointmentDate} at {$appointmentTime}. Reason: {$appointment->cancelled_reason}";
        
        // ✅ Get valid sender type
        $senderType = $this->getSenderType($cancelledBy);
        $senderId = $cancelledBy === 'patient' ? $appointment->patient->user_id : 1;
        
        // Alert doctor
        StaffAlert::create([
            'sender_id' => $senderId,
            'sender_type' => $senderType, // ✅ Now uses valid type
            'recipient_id' => $appointment->doctor->user_id,
            'recipient_type' => 'doctor',
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->appointment_id,
            'alert_type' => 'Appointment Cancelled',
            'priority' => 'High',
            'alert_title' => '❌ Patient Cancelled Appointment',
            'alert_message' => $message,
            'is_read' => $isRead,
            'read_at' => $isRead ? now() : null,
        ]);
        
        // Alert receptionists
        $receptionists = User::where('role', 'receptionist')->get();
        foreach ($receptionists as $receptionist) {
            StaffAlert::create([
                'sender_id' => $senderId,
                'sender_type' => $senderType, // ✅ Now uses valid type
                'recipient_id' => $receptionist->id,
                'recipient_type' => 'receptionist',
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->appointment_id,
                'alert_type' => 'Appointment Cancelled',
                'priority' => 'Normal',
                'alert_title' => '📅 Slot Available - Appointment Cancelled',
                'alert_message' => $message . " Time slot now available for rescheduling.",
                'is_read' => $isRead,
                'read_at' => $isRead ? now() : null,
            ]);
        }
    }
    
    /**
     * Create urgent cancellation alerts
     */
    private function createUrgentCancellationAlerts($appointment)
    {
        $message = "🚨 URGENT: {$appointment->patient->user->name} cancelled AFTER check-in due to emergency. Already in queue!";
        
        $receptionists = User::where('role', 'receptionist')->get();
        foreach ($receptionists as $receptionist) {
            StaffAlert::create([
                'sender_id' => $appointment->patient->user_id,
                'sender_type' => 'system', // ✅ Changed from 'patient'
                'recipient_id' => $receptionist->id,
                'recipient_type' => 'receptionist',
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->appointment_id,
                'alert_type' => 'Emergency Cancellation',
                'priority' => 'Critical',
                'alert_title' => '🚨 EMERGENCY: Patient Left After Check-In',
                'alert_message' => $message,
                'is_read' => false,
            ]);
        }
        
        StaffAlert::create([
            'sender_id' => $appointment->patient->user_id,
            'sender_type' => 'system', // ✅ Changed from 'patient'
            'recipient_id' => $appointment->doctor->user_id,
            'recipient_type' => 'doctor',
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->appointment_id,
            'alert_type' => 'Emergency Cancellation',
            'priority' => 'Critical',
            'alert_title' => '🚨 Patient Emergency Cancellation',
            'alert_message' => $message,
            'is_read' => false,
        ]);
    }
    
    /**
     * Create no-show alert
     */
    private function createNoShowAlert($appointment)
    {
        $patientName = $appointment->patient->user->name;
        $noShowCount = DB::table('patients')
            ->where('patient_id', $appointment->patient_id)
            ->value('no_show_count');
        
        $message = "{$patientName} did not show up for appointment. Total no-shows: {$noShowCount}";
        
        if ($noShowCount >= 3) {
            $message .= " ⚠️ PATIENT FLAGGED - Multiple no-shows!";
        }
        
        $receptionists = User::where('role', 'receptionist')->get();
        foreach ($receptionists as $receptionist) {
            StaffAlert::create([
                'sender_id' => 1,
                'sender_type' => 'system',
                'recipient_id' => $receptionist->id,
                'recipient_type' => 'receptionist',
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->appointment_id,
                'alert_type' => 'No-Show Alert',
                'priority' => $noShowCount >= 3 ? 'High' : 'Normal',
                'alert_title' => '⚠️ Patient No-Show',
                'alert_message' => $message,
                'is_read' => false,
            ]);
        }
    }
}