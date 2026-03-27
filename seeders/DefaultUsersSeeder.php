<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\Appointment;
use App\Models\DoctorLeave;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\DoctorRating;
use Carbon\Carbon;

class DefaultUsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        User::truncate();
        Patient::truncate();
        Doctor::truncate();
        Nurse::truncate();
        Appointment::truncate();
        DoctorLeave::truncate();
        MedicalRecord::truncate();
        Prescription::truncate();
        PrescriptionItem::truncate();
        DoctorRating::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // -----------------------------
        // 1️⃣ Create default Admin
        // -----------------------------
        $admin = User::create([
            'name' => 'Admin One',
            'email' => 'admin1@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'address' => '123 Admin Street',
            'profile_photo' => null,
        ]);

        // -----------------------------
        // 2️⃣ Create TWO Doctors
        // -----------------------------
        $doctorUsers = [
            [
                'name' => 'Dr. John Doe',
                'email' => 'drjohndoe@example.com',
                'specialization' => 'General Medicine',
            ],
            [
                'name' => 'Dr. Ahmad Zulkifli',
                'email' => 'drahmad@example.com',
                'specialization' => 'Pediatrics',
            ],
        ];

        $doctors = [];

        foreach ($doctorUsers as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password123'),
                'role' => 'doctor',
                'address' => '1 Doctor Street',
                'profile_photo' => null,
            ]);

            $doctors[] = Doctor::create([
                'user_id' => $user->id,
                'phone_number' => '011' . rand(10000000, 99999999),
                'profile_photo' => null,
                'specialization' => $data['specialization'],
                'availability_status' => 'Available',
            ]);
        }

        // -----------------------------
        // 3️⃣ Create ONE Nurse
        // -----------------------------
        $nurseUser = User::create([
            'name' => 'Nurse Maria Lim',
            'email' => 'nursemaria@example.com',
            'password' => Hash::make('password123'),
            'role' => 'nurse',
            'address' => '25 Health Avenue, Kuala Lumpur',
            'profile_photo' => null,
        ]);

        $nurse = Nurse::create([
            'user_id' => $nurseUser->id,
            'phone_number' => '012' . rand(10000000, 99999999),
            'department' => 'Outpatient Department',
            'shift' => 'Morning',
            'availability_status' => 'Available',
            'profile_photo' => null,
        ]);

        // -----------------------------
        // 4️⃣ Create 10 Patients
        // -----------------------------
        $patients = [];
        $patientData = [
            ['Patient One','patient1@example.com','789 Patient Road','0198765432','Male','1990-01-01'],
            ['Adam Rahman','adam.rahman@example.com','12 Jalan Mawar, Kuala Lumpur','0112345678','Male','1988-03-12'],
            ['Nur Aisyah Binti Ali','aisyah.ali@example.com','45 Jalan Kenanga, Selangor','0192345678','Female','1992-07-05'],
            ['Daniel Tan','daniel.tan@example.com','88 Jalan Melati, Penang','0179876543','Male','1995-11-22'],
            ['Siti Zulaikha','siti.zulaikha@example.com','21 Lorong Aman, Johor Bahru','0147654321','Female','1989-05-10'],
            ['Hafiz Ismail','hafiz.ismail@example.com','5 Jalan Wawasan, Melaka','0199988776','Male','1990-02-17'],
            ['Farah Nabila','farah.nabila@example.com','33 Taman Sri Indah, Pahang','0173344556','Female','1996-08-09'],
            ['Aiman Zul','aiman.zul@example.com','67 Jalan Damai, Negeri Sembilan','0139988775','Male','1994-10-14'],
            ['Liyana Ahmad','liyana.ahmad@example.com','99 Jalan Mutiara, Perak','0195566778','Female','1991-04-19'],
            ['Rayyan Faiz','rayyan.faiz@example.com','52 Jalan Aman, Terengganu','0118877665','Male','1997-12-01'],
        ];

        foreach ($patientData as $data) {
            $user = User::create([
                'name' => $data[0],
                'email' => $data[1],
                'password' => Hash::make('password123'),
                'role' => 'patient',
                'address' => $data[2],
                'profile_photo' => null,
            ]);

            $patients[] = Patient::create([
                'user_id' => $user->id,
                'phone_number' => $data[3],
                'gender' => $data[4],
                'date_of_birth' => $data[5],
                'emergency_contact' => '01' . rand(10000000, 99999999),
            ]);
        }

        // -----------------------------
        // 5️⃣ Create Appointments
        // -----------------------------
        $appointmentStatuses = ['confirmed', 'completed', 'cancelled'];
        $today = Carbon::today();
        $appointments = [];

        // Track used time slots per doctor
        $usedTimes = [
            $doctors[0]->doctor_id => [],
            $doctors[1]->doctor_id => [],
        ];

        foreach ($patients as $patient) {
            $numAppointments = rand(1, 3);

            for ($i = 0; $i < $numAppointments; $i++) {
                $doctor = $doctors[array_rand($doctors)];
                $status = $appointmentStatuses[array_rand($appointmentStatuses)];

                // Generate unique time for this doctor
                do {
                    $hour = rand(9, 17);
                    $minute = rand(0, 1) ? 0 : 30;
                    $time = Carbon::createFromTime($hour, $minute)->format('H:i:s');
                } while (in_array($time, $usedTimes[$doctor->doctor_id]));

                // Mark this time as used for the selected doctor
                $usedTimes[$doctor->doctor_id][] = $time;

                $appointments[] = Appointment::create([
                    'doctor_id' => $doctor->doctor_id,
                    'patient_id' => $patient->patient_id,
                    'appointment_date' => $today,
                    'appointment_time' => $time,
                    'status' => $status,
                    'reason' => fake()->randomElement(['Routine checkup', 'Follow-up', 'Consultation', 'Health screening']),
                    'cancelled_reason' => $status === 'cancelled' ? 'Patient unavailable' : null,
                ]);
            }
        }

        // -----------------------------
        // 6️⃣ Create Doctor Ratings
        // -----------------------------
        foreach ($appointments as $appointment) {
            if ($appointment->status !== 'cancelled') {
                DoctorRating::create([
                    'doctor_id' => $appointment->doctor_id,
                    'patient_id' => $appointment->patient_id,
                    'appointment_id' => $appointment->appointment_id,
                    'rating' => rand(3, 5),
                    'comment' => fake()->randomElement([
                        'Very professional and kind.',
                        'Great experience, would recommend.',
                        'Quick and efficient consultation.',
                        'Doctor explained everything clearly.',
                        'Satisfied with the treatment.',
                    ]),
                ]);
            }
        }

        $this->command->info('✅ Admin, 2 doctors, 1 nurse, 10 patients, and unique-time appointments seeded successfully.');
    }
}
