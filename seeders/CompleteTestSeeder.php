<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Pharmacist;
use App\Models\MedicineInventory;
use App\Models\VitalRecord;
use Carbon\Carbon;

class CompleteTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Starting complete test seeder (Core modules only)...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Doctor::truncate();
        Nurse::truncate();
        Patient::truncate();
        Appointment::truncate();
        Pharmacist::truncate();
        MedicineInventory::truncate();
        VitalRecord::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 🧑‍💼 1️⃣ Admin
        $admin = User::create([
            'name' => 'Admin One',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'address' => '123 Admin Street, Kuala Lumpur',
        ]);

        // 💁‍♀️ 2️⃣ Receptionists
        $receptionists = [];
        $receptionistData = [
            [
                'name' => 'Sarah Johnson',
                'email' => 'receptionist@medilink.com',
                'address' => '123 Hospital Street, Rawang, Selangor',
            ],
            [
                'name' => 'Emily Chen',
                'email' => 'receptionist2@medilink.com',
                'address' => '456 Medical Avenue, Rawang, Selangor',
            ],
        ];

        foreach ($receptionistData as $data) {
            $receptionists[] = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'receptionist',
                'address' => $data['address'],
            ]);
        }

        // 👨‍⚕️ 3️⃣ Doctors
        $doctorData = [
            ['name' => 'Dr. John Doe', 'email' => 'drjohn@example.com', 'specialization' => 'General Medicine'],
            ['name' => 'Dr. Ahmad Zulkifli', 'email' => 'drahmad@example.com', 'specialization' => 'Pediatrics'],
            ['name' => 'Dr. Sarah Lee', 'email' => 'drsarah@example.com', 'specialization' => 'Cardiology'],
        ];

        $doctors = [];
        $doctorUsers = [];
        foreach ($doctorData as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'doctor',
                'address' => '1 Doctor Street, Selangor',
            ]);

            $doctorUsers[] = $user;
            $doctors[] = Doctor::create([
                'user_id' => $user->id,
                'phone_number' => '011' . rand(10000000, 99999999),
                'specialization' => $data['specialization'],
                'availability_status' => 'Available',
            ]);
        }

        // 👩‍⚕️ 4️⃣ Nurses
        $nurseData = [
            ['name' => 'Nurse Maria Lim', 'email' => 'nursemaria@example.com', 'department' => 'Outpatient', 'shift' => 'Day Shift'],
            ['name' => 'Nurse Amy Wong', 'email' => 'nurseamy@example.com', 'department' => 'Outpatient', 'shift' => 'Evening Shift'],
            ['name' => 'Nurse Rachel Tan', 'email' => 'nurserachel@example.com', 'department' => 'Outpatient', 'shift' => 'Day Shift'],
        ];

        $nurses = [];
        $nurseUsers = [];
        foreach ($nurseData as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'nurse',
                'address' => '25 Health Avenue, Kuala Lumpur',
            ]);

            $nurseUsers[] = $user;
            $nurses[] = Nurse::create([
                'user_id' => $user->id,
                'phone_number' => '012' . rand(10000000, 99999999),
                'department' => $data['department'],
                'shift' => $data['shift'],
                'availability_status' => 'Available',
            ]);
        }

        // 💊 5️⃣ Pharmacist
        $pharmacistUser = User::create([
            'name' => 'Sarah Johnson (Pharmacist)',
            'email' => 'pharmacist@medilink.com',
            'password' => Hash::make('password'),
            'role' => 'pharmacist',
            'address' => '123 Pharmacy St, Medical District',
        ]);

        $pharmacist = Pharmacist::create([
            'user_id' => $pharmacistUser->id,
            'phone_number' => '+60123456789',
            'license_number' => 'RP12345',
            'license_expiry' => now()->addYears(2),
            'specialization' => 'Clinical Pharmacy',
            'availability_status' => 'Available',
        ]);

        // 💊 6️⃣ Medicines
        $this->command->info('💊 Seeding medicines...');

        $medicines = [
            [
                'medicine_name' => 'Paracetamol',
                'generic_name' => 'Acetaminophen',
                'brand_name' => 'Panadol',
                'category' => 'Analgesic',
                'form' => 'Tablet',
                'strength' => '500mg',
                'quantity_in_stock' => 500,
                'reorder_level' => 100,
                'unit_price' => 0.50,
                'supplier' => 'PharmaCorp',
                'batch_number' => 'BATCH001',
                'manufacture_date' => now()->subMonths(3),
                'expiry_date' => now()->addYears(2),
                'requires_prescription' => false,
                'is_controlled_substance' => false,
                'status' => 'Active',
            ],
            [
                'medicine_name' => 'Amoxicillin',
                'generic_name' => 'Amoxicillin',
                'brand_name' => 'Amoxil',
                'category' => 'Antibiotic',
                'form' => 'Capsule',
                'strength' => '500mg',
                'quantity_in_stock' => 200,
                'reorder_level' => 50,
                'unit_price' => 1.20,
                'supplier' => 'MediSupply',
                'batch_number' => 'BATCH002',
                'manufacture_date' => now()->subMonths(2),
                'expiry_date' => now()->addYears(1),
                'requires_prescription' => true,
                'is_controlled_substance' => false,
                'status' => 'Active',
            ],
            [
                'medicine_name' => 'Ibuprofen',
                'generic_name' => 'Ibuprofen',
                'brand_name' => 'Advil',
                'category' => 'Analgesic',
                'form' => 'Tablet',
                'strength' => '400mg',
                'quantity_in_stock' => 300,
                'reorder_level' => 75,
                'unit_price' => 0.80,
                'supplier' => 'MediSupply',
                'batch_number' => 'IBU2024-087',
                'manufacture_date' => now()->subMonths(5),
                'expiry_date' => now()->addMonths(24),
                'requires_prescription' => false,
                'is_controlled_substance' => false,
                'status' => 'Active',
            ],
            [
                'medicine_name' => 'Ciprofloxacin',
                'generic_name' => 'Ciprofloxacin',
                'brand_name' => 'Cipro',
                'category' => 'Antibiotic',
                'form' => 'Tablet',
                'strength' => '500mg',
                'quantity_in_stock' => 30,
                'reorder_level' => 50,
                'unit_price' => 2.80,
                'supplier' => 'PharmaCorp',
                'batch_number' => 'CIP2024-078',
                'manufacture_date' => now()->subMonths(4),
                'expiry_date' => now()->addYears(2),
                'requires_prescription' => true,
                'is_controlled_substance' => false,
                'status' => 'Low Stock',
            ],
        ];

        $medicineModels = [];
        foreach ($medicines as $medicine) {
            $medicineModels[] = MedicineInventory::create($medicine);
        }

        // 🧍‍♂️ 7️⃣ Patients - CREATE MORE PATIENTS TO FILL ALL TIME SLOTS
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
            ['Chen Wei Jie', 'chenwei@example.com', 'Male'],
            ['Nurul Huda', 'nurulhuda@example.com', 'Female'],
            ['Muthu Selvam', 'muthu@example.com', 'Male'],
            ['Azlina Binti Ahmad', 'azlina@example.com', 'Female'],
            ['Chong Yew Heng', 'chongyh@example.com', 'Male'],
            ['Priya Devi', 'priya@example.com', 'Female'],
            ['Hassan Ibrahim', 'hassani@example.com', 'Male'],
            ['Mei Fen Ng', 'meifen@example.com', 'Female'],
            ['Ravi Shankar', 'ravi@example.com', 'Male'],
            ['Salmah Yusof', 'salmah@example.com', 'Female'],
            ['Daniel Tan', 'daniel@example.com', 'Male'],
            ['Letchumi Devi', 'letchumi@example.com', 'Female'],
            ['Mohd Hafiz', 'hafiz@example.com', 'Male'],
            ['Xiao Ling Wang', 'xiaoling@example.com', 'Female'],
            ['Arjun Singh', 'arjun@example.com', 'Male'],
            ['Nadia Rahman', 'nadia@example.com', 'Female'],
            ['Kevin Liew', 'kevin@example.com', 'Male'],
            ['Rashida Ahmad', 'rashida@example.com', 'Female'],
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

            $patients[] = Patient::create([
                'user_id' => $user->id,
                'phone_number' => '01' . rand(10000000, 99999999),
                'gender' => $data[2],
                'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years'),
                'emergency_contact' => '01' . rand(10000000, 99999999),
            ]);
        }

        // 🩺 8️⃣ PAST APPOINTMENTS (Completed - for history)
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
                    'checked_in_by' => $receptionist->id,
                    'vitals_completed_at' => $vitalsAt,
                    'vitals_recorded_by' => $nurse->nurse_id,
                    'vitals_verified_at' => $vitalsAt->copy()->addMinutes(2),
                    'consultation_started_at' => $consultationStart,
                    'consultation_ended_at' => $consultationEnd,
                ]);

                // Create vital records for past appointments
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

        // 🩺 9️⃣ TODAY'S APPOINTMENTS - ONE APPOINTMENT PER PATIENT ONLY
        $this->command->info('📅 Creating today\'s appointments (ONE per patient, 8 AM to 10 PM)...');

        $today = Carbon::today();

        // Generate time slots every 30 minutes from 08:00 to 22:00 (10 PM)
        $timeSlots = [];
        $start = Carbon::parse($today->format('Y-m-d') . ' 08:00:00');
        $end = Carbon::parse($today->format('Y-m-d') . ' 22:00:00');

        while ($start <= $end) {
            $timeSlots[] = $start->format('H:i:s');
            $start->addMinutes(30);
        }

        // Shuffle patients to randomize appointment distribution
        $shuffledPatients = collect($patients)->shuffle()->values();

        // Each patient gets exactly ONE appointment
        $totalAppointments = min(count($timeSlots), count($shuffledPatients));

        $todayAppointments = [];

        for ($i = 0; $i < $totalAppointments; $i++) {
            $patient = $shuffledPatients[$i]; // Each patient used only once
            $doctor = $doctors[$i % count($doctors)]; // Rotate through doctors
            $slot = $timeSlots[$i];

            $appointment = Appointment::create([
                'doctor_id' => $doctor->doctor_id,
                'patient_id' => $patient->patient_id,
                'appointment_date' => $today,
                'appointment_time' => $slot,
                'status' => Appointment::STATUS_CONFIRMED, // Not checked in
                'reason' => $reasons[array_rand($reasons)],
                'arrived_at' => null,
                'checked_in_by' => null,
                'vitals_completed_at' => null,
                'vitals_recorded_by' => null,
                'vitals_verified_at' => null,
            ]);

            $todayAppointments[] = $appointment;
        }

        $this->command->info("📅 Created " . count($todayAppointments) . " appointments (ONE per patient) throughout the day.");

        $this->command->info("✅ Seeding complete!");
        $this->command->info("📊 Summary:");
        $this->command->info("   - 1 Admin");
        $this->command->info("   - 2 Receptionists");
        $this->command->info("   - 3 Doctors");
        $this->command->info("   - 3 Nurses");
        $this->command->info("   - 1 Pharmacist");
        $this->command->info("   - " . count($patients) . " Patients");
        $this->command->info("   - {$appointmentCount} Past Appointments (Completed)");
        $this->command->info("   - " . count($todayAppointments) . " Today's Appointments (8 AM - 10 PM, ONE per patient)");
        $this->command->info("   - " . count($medicineModels) . " Medicines");
        $this->command->info("");
        $this->command->info("🔑 Login Credentials:");
        $this->command->info("   Admin: admin@example.com / password");
        $this->command->info("   Doctor: drjohn@example.com / password");
        $this->command->info("   Nurse: nursemaria@example.com / password");
        $this->command->info("   Pharmacist: pharmacist@medilink.com / password");
        $this->command->info("   Receptionist: receptionist@medilink.com / password");
        $this->command->info("   Patient: ali@example.com / password");
        $this->command->info("");
        $this->command->info("✅ All today's appointments are in 'confirmed' status (NOT checked in yet)");
        $this->command->info("✅ Each patient has ONLY ONE appointment today - NO DUPLICATES!");
    }
}