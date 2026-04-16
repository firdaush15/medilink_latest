<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\StaffAlert;
use App\Models\MedicineInventory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacistAlertController extends Controller
{
    public function index(Request $request)
    {
        $pharmacist = Auth::user()->pharmacist;

        if (!$pharmacist) {
            abort(403, 'Pharmacist profile not found.');
        }

        // ── Proactively generate any missing stock / expiry alerts ──
        $this->generateMissingInventoryAlerts();

        $filter = $request->get('filter', 'all');

        $alertsQuery = StaffAlert::with(['medicine', 'prescription', 'patient.user', 'sender'])
            ->where('recipient_id', auth()->id())
            ->where('recipient_type', 'pharmacist');

        switch ($filter) {
            case 'unread':
                $alertsQuery->where('is_read', false);
                break;
            case 'critical':
                $alertsQuery->where('priority', 'Critical')->where('is_acknowledged', false);
                break;
            case 'urgent':
                $alertsQuery->whereIn('priority', ['Urgent', 'Critical'])->where('is_acknowledged', false);
                break;
            case 'pending':
                $alertsQuery->where('is_acknowledged', false);
                break;
            case 'resolved':
                $alertsQuery->where('is_acknowledged', true);
                break;
            case 'low_stock':
                $alertsQuery->where('alert_type', 'Low Stock');
                break;
            case 'expiring':
                $alertsQuery->where('alert_type', 'Expiring Soon');
                break;
            case 'expired':
                $alertsQuery->where('alert_type', 'Expired Medicine');
                break;
            case 'today':
                $alertsQuery->whereDate('created_at', today());
                break;
            case 'prescription':
                $alertsQuery->where('alert_type', 'New Prescription');
                break;
        }

        $alerts = $alertsQuery
            ->orderByRaw("FIELD(priority, 'Critical', 'Urgent', 'High', 'Normal')")
            ->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $rawCounts = StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'pharmacist')
            ->selectRaw("
                COUNT(*) as total,
                SUM(is_read = 0) as unread,
                SUM(priority = 'Critical' AND is_acknowledged = 0) as critical,
                SUM(priority IN ('Urgent','Critical') AND is_acknowledged = 0) as urgent,
                SUM(is_acknowledged = 0) as pending,
                SUM(is_acknowledged = 1) as resolved,
                SUM(alert_type = 'Low Stock') as low_stock,
                SUM(alert_type = 'Expiring Soon') as expiring,
                SUM(alert_type = 'Expired Medicine') as expired,
                SUM(alert_type = 'New Prescription') as prescriptions,
                SUM(DATE(created_at) = CURDATE()) as today
            ")
            ->first();

        $counts = [
            'total'         => $rawCounts->total         ?? 0,
            'unread'        => $rawCounts->unread         ?? 0,
            'critical'      => $rawCounts->critical       ?? 0,
            'urgent'        => $rawCounts->urgent         ?? 0,
            'pending'       => $rawCounts->pending        ?? 0,
            'resolved'      => $rawCounts->resolved       ?? 0,
            'low_stock'     => $rawCounts->low_stock      ?? 0,
            'expiring'      => $rawCounts->expiring       ?? 0,
            'expired'       => $rawCounts->expired        ?? 0,
            'prescriptions' => $rawCounts->prescriptions  ?? 0,
            'today'         => $rawCounts->today          ?? 0,
        ];

        // Staff the pharmacist can alert
        $doctors       = User::where('role', 'doctor')->orderBy('name')->get();
        $adminUsers    = User::where('role', 'admin')->orderBy('name')->get();
        $nurses        = User::where('role', 'nurse')->orderBy('name')->get();
        $receptionists = User::where('role', 'receptionist')->orderBy('name')->get();

        return view('pharmacist.pharmacist_alerts', compact(
            'alerts',
            'filter',
            'counts',
            'doctors',
            'adminUsers',
            'nurses',
            'receptionists'
        ));
    }

    /**
     * Scan the medicine inventory and create StaffAlert records for any
     * low-stock, out-of-stock, expiring, or expired medicines that do NOT
     * already have an unresolved alert for the current pharmacist.
     */
    protected function generateMissingInventoryAlerts(): void
    {
        $pharmacistUserId = auth()->id();

        // ── Fix existing alerts that have unrounded float day values ──
        StaffAlert::where('recipient_id', $pharmacistUserId)
            ->where('recipient_type', 'pharmacist')
            ->where('alert_type', 'Expiring Soon')
            ->where('is_acknowledged', false)
            ->get()
            ->each(function ($alert) {
                if (preg_match('/(\d+\.\d+) day\(s\)/', $alert->alert_message, $matches)) {
                    $rounded = (int) round((float) $matches[1]);
                    $fixed   = preg_replace('/\d+\.\d+ day\(s\)/', "{$rounded} day(s)", $alert->alert_message);
                    $alert->update(['alert_message' => $fixed]);
                }
            });

        // Resolve the system sender (first admin, or fall back to pharmacist)
        $systemSender = User::where('role', 'admin')->first() ?? auth()->user();

        // ── 1. Out-of-stock medicines ──────────────────────────────────────
        $outOfStock = MedicineInventory::where('quantity_in_stock', 0)->get();
        foreach ($outOfStock as $medicine) {
            $this->createInventoryAlertIfMissing(
                $pharmacistUserId,
                $systemSender->id,
                $medicine,
                'Out of Stock',
                'Critical',
                "🚨 OUT OF STOCK: {$medicine->medicine_name}",
                "Stock for {$medicine->medicine_name} ({$medicine->strength} {$medicine->form}) has reached ZERO. Immediate restock required."
            );
        }

        // ── 2. Low-stock medicines (above 0 but at or below reorder level) ─
        $lowStock = MedicineInventory::whereRaw('quantity_in_stock > 0')
            ->whereRaw('quantity_in_stock <= reorder_level')
            ->get();
        foreach ($lowStock as $medicine) {
            $this->createInventoryAlertIfMissing(
                $pharmacistUserId,
                $systemSender->id,
                $medicine,
                'Low Stock',
                'Urgent',
                "⚠️ LOW STOCK: {$medicine->medicine_name}",
                "Stock for {$medicine->medicine_name} ({$medicine->strength} {$medicine->form}) is low. "
                    . "Current: {$medicine->quantity_in_stock} units / Reorder level: {$medicine->reorder_level} units."
            );
        }

        // ── 3. Medicines with batches expiring within 90 days ─────────────
        $expiringCritical = MedicineInventory::whereHas('batches', function ($q) {
            $q->where('expiry_date', '<=', now()->addDays(90))
                ->where('expiry_date', '>', now())
                ->where('quantity', '>', 0);
        })->get();
        foreach ($expiringCritical as $medicine) {
            $daysLeft = (int) round($medicine->getDaysUntilExpiry());
            $this->createInventoryAlertIfMissing(
                $pharmacistUserId,
                $systemSender->id,
                $medicine,
                'Expiring Soon',
                $daysLeft <= 30 ? 'Urgent' : 'High',
                "⏰ EXPIRING SOON: {$medicine->medicine_name}",
                "{$medicine->medicine_name} ({$medicine->strength} {$medicine->form}) expires in {$daysLeft} day(s). Please review and arrange disposal or return."
            );
        }

        // ── 4. Medicines with expired batches that still have stock ────────
        $expired = MedicineInventory::whereHas('batches', function ($q) {
            $q->where('expiry_date', '<=', now())
                ->where('quantity', '>', 0);
        })->get();
        foreach ($expired as $medicine) {
            $this->createInventoryAlertIfMissing(
                $pharmacistUserId,
                $systemSender->id,
                $medicine,
                'Expired Medicine',
                'Critical',
                "🚫 EXPIRED MEDICINE: {$medicine->medicine_name}",
                "{$medicine->medicine_name} ({$medicine->strength} {$medicine->form}) has EXPIRED batches still in stock. Immediate disposal required."
            );
        }
    }

    /**
     * Create a StaffAlert only if one hasn't been created for this
     * pharmacist + medicine + alert_type within the last 24 hours.
     */
    protected function createInventoryAlertIfMissing(
        int    $recipientId,
        int    $senderId,
        $medicine,
        string $alertType,
        string $priority,
        string $title,
        string $message
    ): void {
        $exists = StaffAlert::where('recipient_id', $recipientId)
            ->where('recipient_type', 'pharmacist')
            ->where('alert_type', $alertType)
            ->where('medicine_id', $medicine->medicine_id)
            ->where('is_acknowledged', false)
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();

        if ($exists) {
            return;
        }

        $actionUrl = null;
        try {
            $actionUrl = route('pharmacist.inventory.show', $medicine->medicine_id);
        } catch (\Exception $e) {
            // Route may not exist; leave null
        }

        StaffAlert::create([
            'sender_id'      => $senderId,
            'sender_type'    => 'system',
            'recipient_id'   => $recipientId,
            'recipient_type' => 'pharmacist',
            'medicine_id'    => $medicine->medicine_id,
            'alert_type'     => $alertType,
            'priority'       => $priority,
            'alert_title'    => $title,
            'alert_message'  => $message,
            'action_url'     => $actionUrl,
        ]);
    }

    /**
     * Pharmacist sends alert to doctor, admin, nurse, or receptionist.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient_id'   => 'required|exists:users,id',
            'recipient_type' => 'required|in:doctor,admin,nurse,receptionist',
            'alert_type'     => 'required|string|max:100',
            'priority'       => 'required|in:Normal,High,Urgent,Critical',
            'alert_title'    => 'required|string|max:255',
            'alert_message'  => 'required|string|max:1000',
            'patient_id'     => 'nullable|exists:patients,patient_id',
            'medicine_id'    => 'nullable|exists:medicine_inventory,medicine_id',
        ]);

        // Verify recipient has the claimed role
        $recipient = User::findOrFail($validated['recipient_id']);
        if ($recipient->role !== $validated['recipient_type']) {
            return redirect()->back()->withErrors(['recipient_id' => 'Recipient role mismatch.']);
        }

        StaffAlert::create([
            'sender_id'      => auth()->id(),
            'sender_type'    => 'pharmacist',
            'recipient_id'   => $validated['recipient_id'],
            'recipient_type' => $validated['recipient_type'],
            'patient_id'     => $validated['patient_id'] ?? null,
            'medicine_id'    => $validated['medicine_id'] ?? null,
            'alert_type'     => $validated['alert_type'],
            'priority'       => $validated['priority'],
            'alert_title'    => $validated['alert_title'],
            'alert_message'  => $validated['alert_message'],
        ]);

        return redirect()->back()->with('success', 'Alert sent successfully.');
    }

    /**
     * Soft-poll endpoint for navbar badge update.
     */
    public function getUnreadCount()
    {
        $rawCounts = StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'pharmacist')
            ->selectRaw("
                SUM(is_read = 0) as unread,
                SUM(priority = 'Critical' AND is_acknowledged = 0) as critical
            ")
            ->first();

        return response()->json([
            'count'    => (int) ($rawCounts->unread   ?? 0),
            'critical' => (int) ($rawCounts->critical ?? 0),
        ]);
    }

    public function markAsRead($id)
    {
        $alert = StaffAlert::findOrFail($id);

        if ($alert->recipient_id !== auth()->id() || $alert->recipient_type !== 'pharmacist') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $alert->update(['is_read' => true, 'read_at' => now()]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Alert marked as read.');
    }

    public function acknowledge($id)
    {
        $alert = StaffAlert::findOrFail($id);

        if ($alert->recipient_id !== auth()->id() || $alert->recipient_type !== 'pharmacist') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $alert->update([
            'is_acknowledged' => true,
            'acknowledged_at' => now(),
            'is_read'         => true,
            'read_at'         => $alert->read_at ?? now(),
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Alert resolved.');
    }

    public function markAllRead()
    {
        StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'pharmacist')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return redirect()->back()->with('success', 'All alerts marked as read.');
    }
}