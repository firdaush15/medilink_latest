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
use App\Models\VitalRecord;
use Carbon\Carbon;
use App\Helpers\PhoneHelper;

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

        // 👨‍⚕️ 3️⃣ Doctors - FIXED: Unique email addresses
        $doctorData = [
            ['name' => 'Dr. John Doe', 'email' => 'drjohn@example.com', 'specialization' => 'General Practitioner'],
            ['name' => 'Dr. Ahmad Zulkifli', 'email' => 'drahmadzulkifli@example.com', 'specialization' => 'Pediatrics'], // ✅ Changed
            ['name' => 'Dr. Sarah Lee', 'email' => 'drsarah@example.com', 'specialization' => 'Cardiology'],
            ['name' => 'Dr. Mus', 'email' => 'drmus@example.com', 'specialization' => 'General Practitioner'],
            ['name' => 'Dr. Ahmad', 'email' => 'drahmad@example.com', 'specialization' => 'General Practitioner'], // ✅ Kept original
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
        'phone_number' => PhoneHelper::standardize('011' . rand(10000000, 99999999)), // ✅ Standardized
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
                'phone_number' => PhoneHelper::standardize('012' . rand(10000000, 99999999)), // ✅ Standardized
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
    'phone_number' => PhoneHelper::standardize('+60123456789'), // ✅ Standardized
    'license_number' => 'RP12345',
    'license_expiry' => now()->addYears(2),
    'specialization' => 'Clinical Pharmacy',
    'availability_status' => 'Available',
]);

        // NOTE: Medicine seeding is now handled by MedicineInventorySeeder
        $this->command->info('💊 Medicines will be seeded by MedicineInventorySeeder...');

        // 🧍‍♂️ 6️⃣ Patients
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
                'phone_number' => PhoneHelper::standardize('01' . rand(10000000, 99999999)), // ✅ Standardized
                'gender' => $data[2],
                'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years'),
                'emergency_contact' => PhoneHelper::standardize('01' . rand(10000000, 99999999)), // ✅ Standardized
            ]);
        }

        // 🩺 7️⃣ PAST APPOINTMENTS
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

        // 🩺 8️⃣ TODAY'S APPOINTMENTS
        $this->command->info('📅 Creating today\'s appointments (ONE per patient, 8 AM to 10 PM)...');

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
        $todayAppointments = [];

        for ($i = 0; $i < $totalAppointments; $i++) {
            $patient = $shuffledPatients[$i];
            $doctor = $doctors[$i % count($doctors)];
            $slot = $timeSlots[$i];

            $appointment = Appointment::create([
                'doctor_id' => $doctor->doctor_id,
                'patient_id' => $patient->patient_id,
                'appointment_date' => $today,
                'appointment_time' => $slot,
                'status' => Appointment::STATUS_CONFIRMED,
                'reason' => $reasons[array_rand($reasons)],
                'arrived_at' => null,
                'checked_in_by' => null,
                'vitals_completed_at' => null,
                'vitals_recorded_by' => null,
                'vitals_verified_at' => null,
            ]);

            $todayAppointments[] = $appointment;
        }

        $this->command->info("📅 Created " . count($todayAppointments) . " today's appointments");

        $this->command->info("✅ Seeding complete!");
        $this->command->info("📊 Summary:");
        $this->command->info("   - 1 Admin");
        $this->command->info("   - 2 Receptionists");
        $this->command->info("   - 5 Doctors"); // ✅ Updated count
        $this->command->info("   - 3 Nurses");
        $this->command->info("   - 1 Pharmacist");
        $this->command->info("   - " . count($patients) . " Patients");
        $this->command->info("   - {$appointmentCount} Past Appointments");
        $this->command->info("   - " . count($todayAppointments) . " Today's Appointments");
        $this->command->info("");
        $this->command->info("🔑 Login Credentials:");
        $this->command->info("   Admin: admin@example.com / password");
        $this->command->info("   Doctor: drjohn@example.com / password");
        $this->command->info("   Nurse: nursemaria@example.com / password");
        $this->command->info("   Pharmacist: pharmacist@medilink.com / password");
        $this->command->info("   Receptionist: receptionist@medilink.com / password");
        $this->command->info("   Patient: ali@example.com / password");
    }
}
