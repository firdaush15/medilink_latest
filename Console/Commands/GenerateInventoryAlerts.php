<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MedicineInventory;
use App\Models\StaffAlert;
use App\Models\User;

class GenerateInventoryAlerts extends Command
{
    protected $signature = 'alerts:inventory';
    protected $description = 'Generate low stock and expiry alerts for pharmacists';

    public function handle()
    {
        $pharmacists = User::where('role', 'pharmacist')->get();

        if ($pharmacists->isEmpty()) {
            $this->warn('No pharmacists found.');
            return Command::SUCCESS;
        }

        $lowStockCount   = 0;
        $expiringCount   = 0;
        $expiredCount    = 0;

        // ── LOW STOCK ────────────────────────────────────────────────────────
        MedicineInventory::lowStock()->each(function ($medicine) use ($pharmacists, &$lowStockCount) {
            foreach ($pharmacists as $pharmacist) {
                $alreadySent = StaffAlert::where('recipient_id', $pharmacist->id)
                    ->where('medicine_id', $medicine->medicine_id)
                    ->where('alert_type', 'Low Stock')
                    ->whereDate('created_at', today())
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $priority = $medicine->quantity_in_stock === 0 ? 'Critical' : 'Urgent';

                StaffAlert::create([
                    'sender_id'      => $pharmacist->id,
                    'sender_type'    => 'system',
                    'recipient_id'   => $pharmacist->id,
                    'recipient_type' => 'pharmacist',
                    'medicine_id'    => $medicine->medicine_id,
                    'alert_type'     => 'Low Stock',
                    'priority'       => $priority,
                    'alert_title'    => 'Low Stock: ' . $medicine->medicine_name,
                    'alert_message'  => "Only {$medicine->quantity_in_stock} units remaining "
                                      . "(reorder level: {$medicine->reorder_level}). "
                                      . "Please submit a restock request.",
                    'action_url'     => route('pharmacist.restock.create'),
                ]);

                $lowStockCount++;
            }
        });

        // ── EXPIRING CRITICALLY (≤ 90 days) ─────────────────────────────────
        MedicineInventory::expiringCritical()->each(function ($medicine) use ($pharmacists, &$expiringCount) {
            foreach ($pharmacists as $pharmacist) {
                $alreadySent = StaffAlert::where('recipient_id', $pharmacist->id)
                    ->where('medicine_id', $medicine->medicine_id)
                    ->where('alert_type', 'Expiring Soon')
                    ->whereDate('created_at', today())
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $daysLeft = $medicine->getDaysUntilExpiry();

                StaffAlert::create([
                    'sender_id'      => $pharmacist->id,
                    'sender_type'    => 'system',
                    'recipient_id'   => $pharmacist->id,
                    'recipient_type' => 'pharmacist',
                    'medicine_id'    => $medicine->medicine_id,
                    'alert_type'     => 'Expiring Soon',
                    'priority'       => 'Urgent',
                    'alert_title'    => 'Expiring Soon: ' . $medicine->medicine_name,
                    'alert_message'  => "{$medicine->medicine_name} will expire in {$daysLeft} days. "
                                      . "Current stock: {$medicine->quantity_in_stock} units. "
                                      . "Consider disposal or usage prioritisation.",
                    'action_url'     => route('pharmacist.inventory.show', $medicine->medicine_id),
                ]);

                $expiringCount++;
            }
        });

        // ── EXPIRED MEDICINES ────────────────────────────────────────────────
        MedicineInventory::expired()->each(function ($medicine) use ($pharmacists, &$expiredCount) {
            foreach ($pharmacists as $pharmacist) {
                $alreadySent = StaffAlert::where('recipient_id', $pharmacist->id)
                    ->where('medicine_id', $medicine->medicine_id)
                    ->where('alert_type', 'Expired Medicine')
                    ->whereDate('created_at', today())
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                StaffAlert::create([
                    'sender_id'      => $pharmacist->id,
                    'sender_type'    => 'system',
                    'recipient_id'   => $pharmacist->id,
                    'recipient_type' => 'pharmacist',
                    'medicine_id'    => $medicine->medicine_id,
                    'alert_type'     => 'Expired Medicine',
                    'priority'       => 'Critical',
                    'alert_title'    => 'EXPIRED: ' . $medicine->medicine_name,
                    'alert_message'  => "{$medicine->medicine_name} has expired and must be disposed of immediately. "
                                      . "Stock: {$medicine->quantity_in_stock} units.",
                    'action_url'     => route('pharmacist.disposals.create'),
                ]);

                $expiredCount++;
            }
        });

        $this->info("Inventory alerts generated:");
        $this->info("  Low stock:  {$lowStockCount}");
        $this->info("  Expiring:   {$expiringCount}");
        $this->info("  Expired:    {$expiredCount}");

        return Command::SUCCESS;
    }
}