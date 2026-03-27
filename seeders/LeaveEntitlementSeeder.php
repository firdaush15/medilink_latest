<?php
// database/seeders/LeaveEntitlementSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LeaveEntitlement;

class LeaveEntitlementSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = date('Y');
        
        // Get all staff members (exclude patients)
        $staffUsers = User::whereIn('role', ['doctor', 'nurse', 'pharmacist', 'receptionist', 'admin'])
            ->get();
        
        foreach ($staffUsers as $user) {
            // Create entitlement for current year if doesn't exist
            LeaveEntitlement::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'year' => $currentYear,
                ],
                [
                    'annual_leave_days' => 14,
                    'sick_leave_days' => 14,
                    'emergency_leave_days' => 5,
                ]
            );
            
            echo "✓ Created leave entitlement for {$user->name} ({$currentYear})\n";
        }
        
        echo "\n✅ Leave entitlements seeded successfully!\n";
    }
}