<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\MedicineInventory;
use App\Models\StockMovement;
use App\Models\StaffAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PharmacistInventoryController extends Controller
{
    /**
     * Display medication inventory list
     */
    public function index(Request $request)
    {
        $pharmacist = Auth::user()->pharmacist;

        // Get filter parameters
        $search = $request->get('search');
        $category = $request->get('category');
        $status = $request->get('status');
        $sortBy = $request->get('sort', 'medicine_name');
        $sortOrder = $request->get('order', 'asc');

        // Base query
        $query = MedicineInventory::query();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('medicine_name', 'LIKE', "%{$search}%")
                    ->orWhere('generic_name', 'LIKE', "%{$search}%")
                    ->orWhere('brand_name', 'LIKE', "%{$search}%")
                    ->orWhere('batch_number', 'LIKE', "%{$search}%");
            });
        }

        // Apply category filter
        if ($category) {
            $query->where('category', $category);
        }

        // ✅ FIXED: Apply status filter with real-world hospital expiry thresholds
        if ($status) {
            if ($status === 'low') {
                // Low stock items (not including out of stock)
                $query->whereRaw('quantity_in_stock <= reorder_level')
                    ->where('quantity_in_stock', '>', 0);
            } elseif ($status === 'expiring') {
                // Expiring critically (within 90 days)
                $query->where('expiry_date', '<=', now()->addDays(90))
                    ->where('expiry_date', '>', now());
            } elseif ($status === 'expiring-soon') {
                // Expiring soon (91-180 days)
                $query->where('expiry_date', '<=', now()->addDays(180))
                    ->where('expiry_date', '>', now()->addDays(90));
            } else {
                // Direct status match
                $query->where('status', $status);
            }
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $medicines = $query->paginate(20);

        // ✅ FIXED: Get accurate statistics with real-world hospital standards
        $stats = [
            'total_items' => MedicineInventory::count(),
            'low_stock' => MedicineInventory::whereRaw('quantity_in_stock <= reorder_level')
                ->where('quantity_in_stock', '>', 0)
                ->count(),
            'out_of_stock' => MedicineInventory::where('quantity_in_stock', '=', 0)->count(),
            // Expiring critically (≤90 days) + Expiring soon (91-180 days)
            'expiring_soon' => MedicineInventory::where('expiry_date', '<=', now()->addDays(180))
                ->where('expiry_date', '>', now())
                ->count(),
            'expired' => MedicineInventory::where('expiry_date', '<=', now())->count(),
            'total_value' => MedicineInventory::selectRaw('SUM(quantity_in_stock * unit_price) as total')
                ->value('total') ?? 0,
        ];

        // Get unique categories for filter dropdown
        $categories = MedicineInventory::distinct()->pluck('category')->sort();

        return view('pharmacist.pharmacist_inventory', compact(
            'medicines',
            'stats',
            'categories',
            'search',
            'category',
            'status',
            'sortBy',
            'sortOrder'
        ));
    }

    /**
     * Show create medicine form
     */
    public function create()
    {
        $categories = [
            'Antibiotic',
            'Analgesic',
            'Antihypertensive',
            'Antidiabetic',
            'Antihistamine',
            'Antacid',
            'Cardiovascular',
            'Respiratory',
            'Gastrointestinal',
            'Dermatological',
            'Neurological',
            'Vitamins & Supplements',
            'Other'
        ];

        $forms = [
            'Tablet',
            'Capsule',
            'Syrup',
            'Injection',
            'Cream',
            'Drops',
            'Inhaler',
            'Suppository',
            'Patch',
            'Other'
        ];

        return view('pharmacist.pharmacist_inventoryCreate', compact('categories', 'forms'));
    }

    /**
     * Store new medicine
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'medicine_name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'category' => 'required|string',
            'form' => 'required|string',
            'strength' => 'required|string|max:100',
            'quantity_in_stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'batch_number' => 'nullable|string|max:100',
            'manufacture_date' => 'nullable|date',
            'expiry_date' => 'required|date|after:today',
            'storage_instructions' => 'nullable|string',
            'side_effects' => 'nullable|string',
            'contraindications' => 'nullable|string',
            'requires_prescription' => 'required|boolean',
            'is_controlled_substance' => 'required|boolean',
        ]);

        $pharmacist = Auth::user()->pharmacist;

        // Create medicine
        $medicine = MedicineInventory::create($validated);

        // Update status based on quantity
        $medicine->updateStatus();

        // Create stock movement record
        StockMovement::create([
            'medicine_id' => $medicine->medicine_id,
            'pharmacist_id' => $pharmacist->pharmacist_id,
            'movement_type' => 'Stock In',
            'quantity' => $validated['quantity_in_stock'],
            'balance_after' => $validated['quantity_in_stock'],
            'batch_number' => $validated['batch_number'],
            'notes' => 'Initial stock entry',
        ]);

        // Create alert using StaffAlert with proper structure
        if ($medicine->isLowStock()) {
            StaffAlert::createAlert([
                'sender_id' => auth()->id(),
                'sender_type' => 'system',
                'recipient_id' => $pharmacist->user_id,
                'recipient_type' => 'pharmacist',
                'medicine_id' => $medicine->medicine_id,
                'alert_type' => 'Low Stock',
                'priority' => 'High',
                'alert_title' => 'Low Stock Alert',
                'alert_message' => "Medicine '{$medicine->medicine_name}' is running low (Current: {$medicine->quantity_in_stock}, Reorder Level: {$medicine->reorder_level})",
                'action_url' => route('pharmacist.inventory.edit', $medicine->medicine_id),
            ]);
        }

        return redirect()->route('pharmacist.inventory')
            ->with('success', 'Medicine added successfully!');
    }

    /**
     * Show edit medicine form
     */
    public function edit($id)
    {
        $medicine = MedicineInventory::findOrFail($id);

        $categories = [
            'Antibiotic',
            'Analgesic',
            'Antihypertensive',
            'Antidiabetic',
            'Antihistamine',
            'Antacid',
            'Cardiovascular',
            'Respiratory',
            'Gastrointestinal',
            'Dermatological',
            'Neurological',
            'Vitamins & Supplements',
            'Other'
        ];

        $forms = [
            'Tablet',
            'Capsule',
            'Syrup',
            'Injection',
            'Cream',
            'Drops',
            'Inhaler',
            'Other'
        ];

        // Get recent stock movements
        $recentMovements = $medicine->stockMovements()
            ->with('pharmacist.user')
            ->latest('created_at')
            ->take(10)
            ->get();

        return view('pharmacist.inventory.edit', compact('medicine', 'categories', 'forms', 'recentMovements'));
    }

    /**
     * Update medicine
     */
    public function update(Request $request, $id)
    {
        $medicine = MedicineInventory::findOrFail($id);

        $validated = $request->validate([
            'medicine_name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'category' => 'required|string',
            'form' => 'required|string',
            'strength' => 'required|string|max:100',
            'reorder_level' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'batch_number' => 'nullable|string|max:100',
            'manufacture_date' => 'nullable|date',
            'expiry_date' => 'required|date',
            'storage_instructions' => 'nullable|string',
            'side_effects' => 'nullable|string',
            'contraindications' => 'nullable|string',
            'requires_prescription' => 'required|boolean',
            'is_controlled_substance' => 'required|boolean',
            'status' => 'required|string',
        ]);

        $medicine->update($validated);
        $medicine->updateStatus();

        return redirect()->route('pharmacist.inventory')
            ->with('success', 'Medicine updated successfully!');
    }

    /**
     * Delete medicine (soft delete by marking as discontinued)
     */
    public function destroy($id)
    {
        $medicine = MedicineInventory::findOrFail($id);

        // Instead of hard delete, mark as discontinued
        $medicine->update(['status' => 'Discontinued']);

        return redirect()->route('pharmacist.inventory')
            ->with('success', 'Medicine marked as discontinued');
    }

    /**
     * Adjust stock (add or reduce)
     */
    public function adjustStock(Request $request, $id)
    {
        $validated = $request->validate([
            'adjustment_type' => 'required|in:add,reduce',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $medicine = MedicineInventory::findOrFail($id);
        $pharmacist = Auth::user()->pharmacist;

        $quantity = $validated['quantity'];

        if ($validated['adjustment_type'] === 'add') {
            $medicine->addStock($quantity, $pharmacist->pharmacist_id, $validated['notes']);
            $message = "Added {$quantity} units to stock";
        } else {
            if ($medicine->quantity_in_stock < $quantity) {
                return back()->with('error', 'Cannot reduce stock below zero');
            }
            $medicine->reduceStock($quantity, $pharmacist->pharmacist_id, $validated['notes']);
            $message = "Reduced {$quantity} units from stock";
        }

        // Update status after stock adjustment
        $medicine->refresh();
        $medicine->updateStatus();

        return back()->with('success', $message);
    }

    /**
     * View stock movement history
     */
    public function stockHistory($id)
    {
        $medicine = MedicineInventory::findOrFail($id);

        $movements = $medicine->stockMovements()
            ->with('pharmacist.user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('pharmacist.inventory.stock-history', compact('medicine', 'movements'));
    }

    /**
     * Mark medicine as expired
     */
    public function markExpired($id)
    {
        $medicine = MedicineInventory::findOrFail($id);
        $pharmacist = Auth::user()->pharmacist;

        $expiredQuantity = $medicine->quantity_in_stock;

        // Create stock movement for expired items
        StockMovement::create([
            'medicine_id' => $medicine->medicine_id,
            'pharmacist_id' => $pharmacist->pharmacist_id,
            'movement_type' => 'Expired',
            'quantity' => -$expiredQuantity,
            'balance_after' => 0,
            'notes' => 'Marked as expired on ' . now()->format('Y-m-d'),
        ]);

        $medicine->update([
            'quantity_in_stock' => 0,
            'status' => 'Expired',
        ]);

        return back()->with('success', 'Medicine marked as expired and stock cleared');
    }

    /**
     * Export inventory to CSV
     */
    public function export(Request $request)
    {
        $status = $request->get('status');

        $query = MedicineInventory::query();

        if ($status) {
            $query->where('status', $status);
        }

        $medicines = $query->orderBy('medicine_name')->get();

        $filename = 'inventory_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($medicines) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Medicine Name',
                'Generic Name',
                'Category',
                'Form',
                'Strength',
                'Stock',
                'Reorder Level',
                'Unit Price',
                'Batch Number',
                'Expiry Date',
                'Status'
            ]);

            // Data rows
            foreach ($medicines as $medicine) {
                fputcsv($file, [
                    $medicine->medicine_name,
                    $medicine->generic_name,
                    $medicine->category,
                    $medicine->form,
                    $medicine->strength,
                    $medicine->quantity_in_stock,
                    $medicine->reorder_level,
                    number_format($medicine->unit_price, 2),
                    $medicine->batch_number,
                    $medicine->expiry_date->format('Y-m-d'),
                    $medicine->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Low stock report
     */
    public function lowStockReport()
    {
        $lowStockMedicines = MedicineInventory::where(function ($query) {
            $query->whereRaw('quantity_in_stock <= reorder_level')
                ->orWhere('status', 'Low Stock')
                ->orWhere('status', 'Out of Stock');
        })
            ->orderBy('quantity_in_stock', 'asc')
            ->get();

        return view('pharmacist.pharmacist_lowStockReport', compact('lowStockMedicines'));
    }

    /**
     * Bulk update medicines
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'medicine_ids' => 'required|array',
            'medicine_ids.*' => 'exists:medicine_inventory,medicine_id',
            'action' => 'required|in:mark_expired,discontinue,activate',
        ]);

        $count = 0;

        foreach ($validated['medicine_ids'] as $id) {
            $medicine = MedicineInventory::find($id);

            if (!$medicine) continue;

            switch ($validated['action']) {
                case 'mark_expired':
                    $medicine->update(['status' => 'Expired', 'quantity_in_stock' => 0]);
                    break;
                case 'discontinue':
                    $medicine->update(['status' => 'Discontinued']);
                    break;
                case 'activate':
                    $medicine->updateStatus();
                    break;
            }

            $count++;
        }

        return back()->with('success', "{$count} medicines updated successfully");
    }
}