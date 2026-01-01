<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call your custom seeder
        $this->call([
            CompleteTestSeeder::class,
            MedicineInventorySeeder::class,
            NurseDoctorAssignmentSeeder::class,
            ShiftTemplateSeeder::class,
            StaffShiftSeeder::class,
            CancelledAppointmentsSeeder::class,
            //StaffTaskSeeder::class,
            ProcedurePricesSeeder::class,
            DiagnosisCodeSeeder::class,
            LeaveEntitlementSeeder::class
        ]);
    }
}
