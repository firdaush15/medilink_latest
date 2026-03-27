<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// ✅ ALL MODEL IMPORTS AT THE TOP (CLEAN & ORGANIZED)
use App\Models\User;
use App\Models\Admin;              // ✅ Admin
use App\Models\Receptionist;       // ✅ Receptionist
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Pharmacist;
use App\Models\Appointment;
use App\Models\VitalRecord;
use App\Models\PatientAllergy;
use App\Models\LeaveEntitlement;   // ✅ If you use this

use Carbon\Carbon;
use App\Helpers\PhoneHelper;

class CompleteTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Starting complete test seeder...');

        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Admin::truncate();            // ✅ Added
        Receptionist::truncate();     // ✅ Added
        Doctor::truncate();
        Nurse::truncate();
        Patient::truncate();
        Appointment::truncate();
        Pharmacist::truncate();
        VitalRecord::truncate();
        PatientAllergy::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ========================================
        // 🧑‍💼 1️⃣ ADMINS
        // ========================================
        $this->command->info('👤 Creating admins...');

        $superAdminUser = User::create([
            'name' => 'Admin One',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'address' => '123 Admin Street, Kuala Lumpur',
        ]);

        $superAdmin = Admin::create([  // ✅ Clean usage
            'user_id' => $superAdminUser->id,
            'phone_number' => PhoneHelper::standardize('+60123456789'),
            'employee_id' => 'ADM-0001',
            'admin_level' => 'Super Admin',
            'department' => 'Administration',
            'hire_date' => now()->subYears(2),
            'can_manage_staff' => true,
            'can_manage_inventory' => true,
            'can_manage_billing' => true,
            'can_view_reports' => true,
            'can_manage_system_settings' => true,
            'status' => 'Active',
            'total_logins' => rand(100, 500),
        ]);

        $this->command->info("✅ Created Super Admin: {$superAdminUser->name}");

        // Regular Admin
        $regularAdminUser = User::create([
            'name' => 'Admin Two',
            'email' => 'admin2@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'address' => '124 Admin Street, Kuala Lumpur',
        ]);

        $regularAdmin = Admin::create([  // ✅ Clean usage
            'user_id' => $regularAdminUser->id,
            'phone_number' => PhoneHelper::standardize('+60123456790'),
            'employee_id' => 'ADM-0002',
            'admin_level' => 'Admin',
            'department' => 'Administration',
            'hire_date' => now()->subMonths(8),
            'can_manage_staff' => true,
            'can_manage_inventory' => true,
            'can_manage_billing' => true,
            'can_view_reports' => true,
            'can_manage_system_settings' => false,
            'status' => 'Active',
            'total_logins' => rand(50, 200),
        ]);

        $this->command->info("✅ Created Regular Admin: {$regularAdminUser->name}");

        // ========================================
        // 💁‍♀️ 2️⃣ RECEPTIONISTS
        // ========================================
        $this->command->info('📋 Creating receptionists...');

        $receptionists = [];
        $receptionistData = [
            [
                'name' => 'Sarah Johnson',
                'email' => 'receptionist@medilink.com',
                'address' => '123 Hospital Street, Rawang, Selangor',
                'shift' => 'Morning',
                'department' => 'Front Desk',
            ],
            [
                'name' => 'Emily Chen',
                'email' => 'receptionist2@medilink.com',
                'address' => '456 Medical Avenue, Rawang, Selangor',
                'shift' => 'Afternoon',
                'department' => 'Front Desk',
            ],
        ];

        foreach ($receptionistData as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'receptionist',
                'address' => $data['address'],
            ]);

            $receptionists[] = Receptionist::create([  // ✅ Clean usage (no \App\Models\ needed)
                'user_id' => $user->id,
                'phone_number' => PhoneHelper::standardize('013' . rand(10000000, 99999999)),
                'employee_id' => 'REC-' . str_pad(count($receptionists) + 1, 4, '0', STR_PAD_LEFT),
                'department' => $data['department'],
                'shift' => $data['shift'],
                'hire_date' => now()->subMonths(rand(6, 24)),
                'availability_status' => 'Available',
                'patients_checked_in_today' => 0,
                'total_patients_checked_in' => rand(100, 500),
            ]);

            $this->command->info("✅ Created Receptionist: {$user->name}");
        }

        // ========================================
        // 👨‍⚕️ 3️⃣ DOCTORS
        // ========================================
        $this->command->info('👨‍⚕️ Creating doctors...');

        $doctorData = [
            ['name' => 'Dr. John Doe', 'email' => 'drjohn@example.com', 'specialization' => 'General Practitioner'],
            ['name' => 'Dr. Ahmad Zulkifli', 'email' => 'drahmadzulkifli@example.com', 'specialization' => 'Pediatrics'],
            ['name' => 'Dr. Sarah Lee', 'email' => 'drsarah@example.com', 'specialization' => 'Cardiology'],
            ['name' => 'Dr. Mus', 'email' => 'drmus@example.com', 'specialization' => 'General Practitioner'],
            ['name' => 'Dr. Ahmad', 'email' => 'drahmad@example.com', 'specialization' => 'General Practitioner'],
        ];

        $doctors = [];
        foreach ($doctorData as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'doctor',
                'address' => '1 Doctor Street, Selangor',
            ]);

            $doctors[] = Doctor::create([
                'user_id' => $user->id,
                'phone_number' => PhoneHelper::standardize('011' . rand(10000000, 99999999)),
                'specialization' => $data['specialization'],
                'availability_status' => 'Available',
            ]);

            $this->command->info("✅ Created Doctor: {$user->name}");
        }

        // ========================================
        // 👩‍⚕️ 4️⃣ NURSES
        // ========================================
        $this->command->info('👩‍⚕️ Creating nurses...');

        $nurseData = [
            ['name' => 'Nurse Maria Lim', 'email' => 'nursemaria@example.com', 'department' => 'Outpatient', 'shift' => 'Day Shift'],
            ['name' => 'Nurse Amy Wong', 'email' => 'nurseamy@example.com', 'department' => 'Outpatient', 'shift' => 'Evening Shift'],
            ['name' => 'Nurse Rachel Tan', 'email' => 'nurserachel@example.com', 'department' => 'Outpatient', 'shift' => 'Day Shift'],
        ];

        $nurses = [];
        foreach ($nurseData as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'nurse',
                'address' => '25 Health Avenue, Kuala Lumpur',
            ]);

            $nurses[] = Nurse::create([
                'user_id' => $user->id,
                'phone_number' => PhoneHelper::standardize('012' . rand(10000000, 99999999)),
                'department' => $data['department'],
                'shift' => $data['shift'],
                'availability_status' => 'Available',
            ]);

            $this->command->info("✅ Created Nurse: {$user->name}");
        }

        // ========================================
        // 💊 5️⃣ PHARMACIST
        // ========================================
        $this->command->info('💊 Creating pharmacist...');

        $pharmacistUser = User::create([
            'name' => 'Sarah Johnson (Pharmacist)',
            'email' => 'pharmacist@medilink.com',
            'password' => Hash::make('password'),
            'role' => 'pharmacist',
            'address' => '123 Pharmacy St, Medical District',
        ]);

        $pharmacist = Pharmacist::create([
            'user_id' => $pharmacistUser->id,
            'phone_number' => PhoneHelper::standardize('+60123456789'),
            'license_number' => 'RP12345',
            'license_expiry' => now()->addYears(2),
            'specialization' => 'Clinical Pharmacy',
            'availability_status' => 'Available',
        ]);

        $this->command->info("✅ Created Pharmacist: {$pharmacistUser->name}");

        // ========================================
        // 🧍‍♂️ 6️⃣ PATIENTS
        // ========================================
        $this->command->info('🧑‍🦱 Creating patients...');

        $patientData = [
            ['Ali Hassan', 'ali@example.com', 'Male'],
            ['Nur Aisyah', 'aisyah@example.com', 'Female'],
            ['Ahmad Zaki', 'zaki@example.com', 'Male'],
            ['Siti Aminah', 'aminah@example.com', 'Female'],
            ['Lee Wei Ming', 'leewei@example.com', 'Male'],
            ['Tan Mei Ling', 'tanmei@example.com', 'Female'],
            ['Wong Kar Wai', 'wongkw@example.com', 'Male'],
            ['Lim Siew Li', 'limsiew@example.com', 'Female'],
            ['Raja Kumar', 'rajakumar@example.com', 'Male'],
            ['Fatimah Zahra', 'fatimah@example.com', 'Female'],
        ];

        $patients = [];
        foreach ($patientData as $data) {
            $user = User::create([
                'name' => $data[0],
                'email' => $data[1],
                'password' => Hash::make('password'),
                'role' => 'patient',
                'address' => 'Outpatient Area, Malaysia',
            ]);

            $patient = Patient::create([
                'user_id' => $user->id,
                'ic_number' => rand(100000, 999999) . '-' . rand(10, 99) . '-' . rand(1000, 9999),
                'phone_number' => PhoneHelper::standardize('01' . rand(10000000, 99999999)),
                'gender' => $data[2],
                'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years'),
                'emergency_contact' => PhoneHelper::standardize('01' . rand(10000000, 99999999)),
            ]);

            // Create allergy
            PatientAllergy::create([
                'patient_id' => $patient->patient_id,
                'allergy_type' => 'Drug/Medication',
                'allergen_name' => 'Paracetamol',
                'severity' => 'Severe',
                'reaction_description' => 'Skin rash and hives',
                'is_active' => true,
            ]);

            $patients[] = $patient;
            $this->command->info("✅ Created Patient: {$user->name}");
        }

        // ========================================
        // 🩺 7️⃣ PAST APPOINTMENTS
        // ========================================
        $this->command->info('📅 Creating past appointments...');

        $reasons = [
            'General checkup',
            'Follow-up consultation',
            'Fever and cough',
            'Regular health screening',
        ];

        $appointmentCount = 0;
        foreach ($patients as $patient) {
            $numPastAppointments = rand(3, 6);

            for ($i = 0; $i < $numPastAppointments; $i++) {
                $daysAgo = rand(7, 60);
                $appointmentDate = Carbon::now()->subDays($daysAgo);

                $doctor = $doctors[array_rand($doctors)];
                $receptionist = $receptionists[array_rand($receptionists)];
                $nurse = $nurses[array_rand($nurses)];

                $appointmentTime = $appointmentDate->copy()->setTime(rand(8, 16), [0, 15, 30, 45][rand(0, 3)]);
                $arrivedAt = $appointmentTime->copy()->addMinutes(rand(-5, 15));
                $vitalsAt = $arrivedAt->copy()->addMinutes(rand(5, 15));
                $consultationStart = $vitalsAt->copy()->addMinutes(rand(5, 20));
                $consultationEnd = $consultationStart->copy()->addMinutes(rand(10, 30));

                $appointment = Appointment::create([
                    'doctor_id' => $doctor->doctor_id,
                    'patient_id' => $patient->patient_id,
                    'appointment_date' => $appointmentDate,
                    'appointment_time' => $appointmentTime,
                    'status' => Appointment::STATUS_COMPLETED,
                    'reason' => $reasons[array_rand($reasons)],
                    'arrived_at' => $arrivedAt,
                    'checked_in_by' => $receptionist->user_id,
                    'vitals_completed_at' => $vitalsAt,
                    'vitals_recorded_by' => $nurse->nurse_id,
                    'vitals_verified_at' => $vitalsAt->copy()->addMinutes(2),
                    'consultation_started_at' => $consultationStart,
                    'consultation_ended_at' => $consultationEnd,
                ]);

                VitalRecord::create([
                    'patient_id' => $patient->patient_id,
                    'nurse_id' => $nurse->nurse_id,
                    'appointment_id' => $appointment->appointment_id,
                    'temperature' => rand(361, 375) / 10,
                    'blood_pressure' => rand(115, 130) . '/' . rand(75, 85),
                    'heart_rate' => rand(65, 90),
                    'respiratory_rate' => rand(14, 20),
                    'oxygen_saturation' => rand(96, 99),
                    'weight' => rand(50, 90),
                    'height' => rand(150, 180),
                    'recorded_at' => $vitalsAt,
                    'notes' => 'Routine vital signs check',
                    'is_critical' => false,
                ]);

                $appointmentCount++;
            }
        }

        $this->command->info("✅ Created {$appointmentCount} past appointments");

        // ========================================
        // 🩺 8️⃣ TODAY'S APPOINTMENTS
        // ========================================
        $this->command->info('📅 Creating today\'s appointments...');

        $today = Carbon::today();
        $timeSlots = [];
        $start = Carbon::parse($today->format('Y-m-d') . ' 08:00:00');
        $end = Carbon::parse($today->format('Y-m-d') . ' 22:00:00');

        while ($start <= $end) {
            $timeSlots[] = $start->format('H:i:s');
            $start->addMinutes(30);
        }

        $shuffledPatients = collect($patients)->shuffle()->values();
        $totalAppointments = min(count($timeSlots), count($shuffledPatients));

        for ($i = 0; $i < $totalAppointments; $i++) {
            $patient = $shuffledPatients[$i];
            $doctor = $doctors[$i % count($doctors)];
            $slot = $timeSlots[$i];

            Appointment::create([
                'doctor_id' => $doctor->doctor_id,
                'patient_id' => $patient->patient_id,
                'appointment_date' => $today,
                'appointment_time' => $slot,
                'status' => Appointment::STATUS_CONFIRMED,
                'reason' => $reasons[array_rand($reasons)],
            ]);
        }

        $this->command->info("📅 Created {$totalAppointments} today's appointments");

        // ========================================
        // 📊 SUMMARY
        // ========================================
        $this->command->info("");
        $this->command->info("✅ Seeding complete!");
        $this->command->info("📊 Summary:");
        $this->command->info("   - 2 Admins (1 Super, 1 Regular)");
        $this->command->info("   - 2 Receptionists");
        $this->command->info("   - 5 Doctors");
        $this->command->info("   - 3 Nurses");
        $this->command->info("   - 1 Pharmacist");
        $this->command->info("   - " . count($patients) . " Patients");
        $this->command->info("   - {$appointmentCount} Past Appointments");
        $this->command->info("   - {$totalAppointments} Today's Appointments");
        $this->command->info("");
        $this->command->info("🔑 Login Credentials:");
        $this->command->info("   Super Admin: admin@example.com / password");
        $this->command->info("   Regular Admin: admin2@example.com / password");
        $this->command->info("   Receptionist: receptionist@medilink.com / password");
        $this->command->info("   Doctor: drjohn@example.com / password");
        $this->command->info("   Nurse: nursemaria@example.com / password");
        $this->command->info("   Pharmacist: pharmacist@medilink.com / password");
        $this->command->info("   Patient: ali@example.com / password");
    }
}