<?php

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\MedicineInventory;
use App\Models\MedicineBatch;
use App\Models\StockMovement;
use App\Models\StaffAlert;
use App\Models\RestockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $query = MedicineInventory::query()->with('activeBatches');

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('medicine_name', 'LIKE', "%{$search}%")
                    ->orWhere('generic_name', 'LIKE', "%{$search}%")
                    ->orWhere('brand_name', 'LIKE', "%{$search}%");
            });
        }

        // Apply category filter
        if ($category) {
            $query->where('category', $category);
        }

        // Apply status filter
        if ($status) {
            if ($status === 'low') {
                $query->whereRaw('quantity_in_stock <= reorder_level')
                    ->where('quantity_in_stock', '>', 0);
            } elseif ($status === 'expiring') {
                // Medicines with batches expiring within 6 months
                $query->whereHas('batches', function ($q) {
                    $q->where('expiry_date', '<=', now()->addDays(180))
                        ->where('expiry_date', '>', now())
                        ->where('quantity', '>', 0);
                });
            } elseif ($status === 'expired') {
                // Medicines with expired batches
                $query->whereHas('batches', function ($q) {
                    $q->where('expiry_date', '<=', now())
                        ->where('quantity', '>', 0);
                });
            } else {
                $query->where('status', $status);
            }
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $medicines = $query->paginate(20);

        // Get accurate statistics
        $stats = [
            'total_items' => MedicineInventory::count(),
            'low_stock' => MedicineInventory::whereRaw('quantity_in_stock <= reorder_level')
                ->where('quantity_in_stock', '>', 0)
                ->count(),
            'out_of_stock' => MedicineInventory::where('quantity_in_stock', '=', 0)->count(),
            'expiring_soon' => MedicineInventory::whereHas('batches', function ($q) {
                $q->where('expiry_date', '<=', now()->addDays(180))
                    ->where('expiry_date', '>', now())
                    ->where('quantity', '>', 0);
            })->count(),
            'expired' => MedicineInventory::whereHas('batches', function ($q) {
                $q->where('expiry_date', '<=', now())
                    ->where('quantity', '>', 0);
            })->count(),
            'total_value' => DB::table('medicine_batches')
                ->where('status', 'active')
                ->selectRaw('SUM(quantity * unit_price) as total')
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
     * Store new medicine with initial batch
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
            'reorder_level' => 'required|integer|min:0',
            'storage_instructions' => 'nullable|string',
            'side_effects' => 'nullable|string',
            'contraindications' => 'nullable|string',
            'requires_prescription' => 'nullable|boolean',
            'is_controlled_substance' => 'nullable|boolean',
            // Initial batch information
            'quantity_in_stock' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'batch_number' => 'required|string|max:255|unique:medicine_batches,batch_number',
            'supplier' => 'nullable|string|max:255',
            'manufacture_date' => 'nullable|date',
            'expiry_date' => 'required|date|after:today',
        ]);

        $pharmacist = Auth::user()->pharmacist;

        DB::beginTransaction();
        try {
            // Check for duplicate medicine (same name + strength + form)
            $existingMedicine = MedicineInventory::where('medicine_name', $validated['medicine_name'])
                ->where('strength', $validated['strength'])
                ->where('form', $validated['form'])
                ->first();

            if ($existingMedicine) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('error', 'This medicine already exists in inventory! Please add a new batch instead using the "Receive Stock" feature.');
            }

            // Handle checkboxes
            $validated['requires_prescription'] = $request->has('requires_prescription') ? 1 : 0;
            $validated['is_controlled_substance'] = $request->has('is_controlled_substance') ? 1 : 0;

            // Calculate status
            $initialStock = $validated['quantity_in_stock'];
            if ($initialStock == 0) {
                $validated['status'] = 'Out of Stock';
            } elseif ($initialStock <= $validated['reorder_level']) {
                $validated['status'] = 'Low Stock';
            } else {
                $validated['status'] = 'Active';
            }

            // Create medicine record (without batch-specific fields)
            $medicine = MedicineInventory::create([
                'medicine_name' => $validated['medicine_name'],
                'generic_name' => $validated['generic_name'],
                'brand_name' => $validated['brand_name'],
                'category' => $validated['category'],
                'form' => $validated['form'],
                'strength' => $validated['strength'],
                'quantity_in_stock' => $initialStock,
                'reorder_level' => $validated['reorder_level'],
                'unit_price' => $validated['unit_price'], // Average price
                'storage_instructions' => $validated['storage_instructions'],
                'side_effects' => $validated['side_effects'],
                'contraindications' => $validated['contraindications'],
                'requires_prescription' => $validated['requires_prescription'],
                'is_controlled_substance' => $validated['is_controlled_substance'],
                'status' => $validated['status'],
            ]);

            // Create initial batch if stock > 0
            if ($initialStock > 0) {
                $batch = MedicineBatch::create([
                    'medicine_id' => $medicine->medicine_id,
                    'batch_number' => $validated['batch_number'],
                    'quantity' => $initialStock,
                    'supplier' => $validated['supplier'],
                    'manufacture_date' => $validated['manufacture_date'],
                    'expiry_date' => $validated['expiry_date'],
                    'received_date' => now(),
                    'unit_price' => $validated['unit_price'],
                    'status' => 'active',
                    'notes' => 'Initial stock registration',
                ]);

                // Create stock movement record
                StockMovement::create([
                    'medicine_id' => $medicine->medicine_id,
                    'batch_id' => $batch->batch_id,
                    'pharmacist_id' => $pharmacist->pharmacist_id,
                    'movement_type' => 'Stock In',
                    'quantity' => $initialStock,
                    'balance_after' => $initialStock,
                    'notes' => 'Initial stock registration',
                ]);
            }

            DB::commit();

            return redirect()->route('pharmacist.inventory')
                ->with('success', "Medicine '{$medicine->medicine_name}' registered successfully with {$initialStock} units in batch {$validated['batch_number']}!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to register medicine: ' . $e->getMessage());
        }
    }

    /**
     * Show medicine details with batch information
     */
    public function show($id)
    {
        $medicine = MedicineInventory::with(['batches' => function ($query) {
            $query->orderBy('expiry_date', 'asc');
        }])->findOrFail($id);

        return view('pharmacist.pharmacist_inventoryShow', compact('medicine'));
    }

    /**
     * Show edit medicine form (metadata only)
     */
    public function edit($id)
    {
        $medicine = MedicineInventory::with('batches')->findOrFail($id);

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
            ->with(['pharmacist.user', 'batch'])
            ->latest('created_at')
            ->take(10)
            ->get();

        return view('pharmacist.inventory.edit', compact('medicine', 'categories', 'forms', 'recentMovements'));
    }

    /**
     * Update medicine metadata (NOT stock quantity)
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
            'storage_instructions' => 'nullable|string',
            'side_effects' => 'nullable|string',
            'contraindications' => 'nullable|string',
            'requires_prescription' => 'nullable|boolean',
            'is_controlled_substance' => 'nullable|boolean',
        ]);

        // Check if name/strength/form changed would create duplicate
        if (
            $validated['medicine_name'] != $medicine->medicine_name ||
            $validated['strength'] != $medicine->strength ||
            $validated['form'] != $medicine->form
        ) {

            $duplicate = MedicineInventory::where('medicine_name', $validated['medicine_name'])
                ->where('strength', $validated['strength'])
                ->where('form', $validated['form'])
                ->where('medicine_id', '!=', $id)
                ->first();

            if ($duplicate) {
                return back()
                    ->withInput()
                    ->with('error', 'Another medicine with the same name, strength, and form already exists!');
            }
        }

        $validated['requires_prescription'] = $request->has('requires_prescription') ? 1 : 0;
        $validated['is_controlled_substance'] = $request->has('is_controlled_substance') ? 1 : 0;

        $medicine->update($validated);
        $medicine->updateStatus();

        return redirect()->route('pharmacist.inventory')
            ->with('success', 'Medicine information updated successfully!');
    }

    /**
     * Delete medicine (soft delete by marking as discontinued)
     */
    public function destroy($id)
    {
        $medicine = MedicineInventory::findOrFail($id);

        // Check if medicine has active batches
        if ($medicine->activeBatches()->exists()) {
            return back()->with('error', 'Cannot discontinue medicine with active stock batches!');
        }

        $medicine->update(['status' => 'Discontinued']);

        return redirect()->route('pharmacist.inventory')
            ->with('success', 'Medicine marked as discontinued');
    }

    /**
     * View stock movement history
     */
    public function stockHistory($id)
    {
        $medicine = MedicineInventory::findOrFail($id);

        $movements = $medicine->stockMovements()
            ->with(['pharmacist.user', 'batch'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('pharmacist.inventory.stock-history', compact('medicine', 'movements'));
    }

    /**
     * Export inventory to CSV
     */
    public function export(Request $request)
    {
        $status = $request->get('status');

        $query = MedicineInventory::with('activeBatches');

        if ($status) {
            if ($status === 'Low Stock') {
                $query->whereRaw('quantity_in_stock <= reorder_level')
                    ->where('quantity_in_stock', '>', 0);
            } else {
                $query->where('status', $status);
            }
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
                'Total Stock',
                'Reorder Level',
                'Avg Unit Price',
                'Active Batches',
                'Next Expiry',
                'Status'
            ]);

            // Data rows
            foreach ($medicines as $medicine) {
                $nextBatch = $medicine->getNextExpiringBatch();

                fputcsv($file, [
                    $medicine->medicine_name,
                    $medicine->generic_name,
                    $medicine->category,
                    $medicine->form,
                    $medicine->strength,
                    $medicine->quantity_in_stock,
                    $medicine->reorder_level,
                    number_format($medicine->unit_price, 2),
                    $medicine->activeBatches()->count(),
                    $nextBatch ? $nextBatch->expiry_date->format('Y-m-d') : 'N/A',
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
        $lowStockMedicines = MedicineInventory::with('activeBatches')
            ->where(function ($query) {
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
            'action' => 'required|in:discontinue,activate',
        ]);

        $count = 0;

        foreach ($validated['medicine_ids'] as $id) {
            $medicine = MedicineInventory::find($id);
            if (!$medicine) continue;

            switch ($validated['action']) {
                case 'discontinue':
                    if ($medicine->activeBatches()->exists()) {
                        continue 2; // ✅ FIXED: continue 2 to skip to next foreach iteration
                    }
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

    /**
     * Quick create restock request from inventory page
     */
    public function quickRestock($id)
    {
        return redirect()->route('pharmacist.restock.create', ['medicine_id' => $id]);
    }
}
