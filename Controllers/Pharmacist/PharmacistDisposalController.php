<?php
// ============================================
// FILE 1: PharmacistDisposalController.php
// Location: app/Http/Controllers/Pharmacist/PharmacistDisposalController.php
// ============================================

namespace App\Http\Controllers\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\MedicineDisposal;
use App\Models\MedicineInventory;
use App\Models\RestockRequest;
use App\Models\StockReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PharmacistDisposalController extends Controller
{
    public function index()
    {
        $pharmacist = auth()->user()->pharmacist;

        $disposals = MedicineDisposal::with(['medicine', 'disposedBy', 'witnessedBy'])
            ->where('disposed_by', $pharmacist->pharmacist_id)
            ->latest('disposed_at')
            ->paginate(15);

        return view('pharmacist.pharmacist_disposals', compact('disposals'));
    }

    public function create(Request $request)
    {
        $expiredMedicines = MedicineInventory::where(function ($q) {
            $q->where('status', 'Expired')
                ->orWhere(function ($subQ) {
                    $subQ->where('expiry_date', '<=', now()->addDays(90))
                        ->where('expiry_date', '>', now())
                        ->where('status', '!=', 'Discontinued');
                });
        })
            ->orderBy('expiry_date')
            ->get();

        $selectedMedicine = null;
        if ($request->filled('medicine_id')) {
            $selectedMedicine = MedicineInventory::find($request->medicine_id);

            if ($selectedMedicine && !$expiredMedicines->contains('medicine_id', $selectedMedicine->medicine_id)) {
                $expiredMedicines->prepend($selectedMedicine);
            }
        }

        return view('pharmacist.pharmacist_disposalCreate', compact('expiredMedicines', 'selectedMedicine'));
    }

    public function store(Request $request)
    {
        // Clean up empty witnessed_by field
        if ($request->witnessed_by === '' || $request->witnessed_by === null) {
            $request->merge(['witnessed_by' => null]);
        }
        
        try {
            $validated = $request->validate([
                'medicine_id' => 'required|exists:medicine_inventory,medicine_id',
                'quantity_disposed' => 'required|integer|min:1',
                'batch_number' => 'nullable|string|max:100',
                'reason' => 'required|in:Expired,Near Expiry,Damaged,Contaminated,Recalled by Manufacturer,Quality Issue,Other',
                'reason_details' => 'nullable|string|max:1000',
                'disposal_method' => 'required|in:Incineration,Chemical Treatment,Encapsulation,Landfill (Non-hazardous),Return to Supplier,Other',
                'disposal_details' => 'nullable|string|max:1000',
                'documentation_notes' => 'nullable|string|max:1000',
                'witnessed_by' => [
                    'nullable',
                    'exists:users,id',
                    function ($attribute, $value, $fail) use ($request) {
                        $medicine = MedicineInventory::find($request->medicine_id);
                        
                        if ($medicine && $medicine->is_controlled_substance && !$value) {
                            $fail('⚠️ Witness is required for controlled substances.');
                        }
                    }
                ],
            ]);
            
            $pharmacist = auth()->user()->pharmacist;
            $medicine = MedicineInventory::findOrFail($validated['medicine_id']);

            // ✅ CRITICAL FIX: Check if enough stock
            if ($medicine->quantity_in_stock < $validated['quantity_disposed']) {
                return back()->with('error', 'Insufficient stock to dispose. Current stock: ' . $medicine->quantity_in_stock);
            }

            DB::beginTransaction();
            try {
                $estimatedLoss = $validated['quantity_disposed'] * $medicine->unit_price;

                // Create disposal record
                $disposal = MedicineDisposal::create([
                    'medicine_id' => $validated['medicine_id'],
                    'disposed_by' => $pharmacist->pharmacist_id,
                    'quantity_disposed' => $validated['quantity_disposed'],
                    'batch_number' => $validated['batch_number'],
                    'reason' => $validated['reason'],
                    'reason_details' => $validated['reason_details'],
                    'disposal_method' => $validated['disposal_method'],
                    'disposal_details' => $validated['disposal_details'],
                    'witnessed_by' => $validated['witnessed_by'],
                    'documentation_notes' => $validated['documentation_notes'],
                    'estimated_loss' => $estimatedLoss,
                    'disposed_at' => now(),
                ]);

                // ✅ CRITICAL FIX: SUBTRACT the quantity (don't use negative)
                $oldStock = $medicine->quantity_in_stock;
                $medicine->quantity_in_stock = $medicine->quantity_in_stock - $validated['quantity_disposed'];
                $medicine->save();

                // Update status
                if ($medicine->quantity_in_stock == 0) {
                    $medicine->update(['status' => 'Out of Stock']);
                } else {
                    $medicine->updateStatus();
                }

                // High-value disposal alert
                if ($estimatedLoss > 1000) {
                    \App\Models\StaffAlert::create([
                        'sender_id' => auth()->id(),
                        'sender_type' => 'pharmacist',
                        'recipient_id' => \App\Models\User::where('role', 'admin')->first()->id,
                        'recipient_type' => 'admin',
                        'medicine_id' => $medicine->medicine_id,
                        'alert_type' => 'High-Value Disposal',
                        'priority' => 'High',
                        'alert_title' => '💰 High-Value Medicine Disposal Alert',
                        'alert_message' => "RM " . number_format($estimatedLoss, 2) . " worth of {$medicine->medicine_name} has been disposed.",
                        'action_url' => route('admin.restock.disposals.show', $disposal->disposal_id),
                    ]);
                }

                DB::commit();

                return redirect()
                    ->route('pharmacist.disposals.index')
                    ->with('success', "✅ Disposal recorded! Stock reduced from {$oldStock} to {$medicine->quantity_in_stock}.");
                    
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Failed to record disposal: ' . $e->getMessage());
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function show($id)
    {
        $pharmacist = auth()->user()->pharmacist;

        $disposal = MedicineDisposal::with(['medicine', 'disposedBy.user', 'witnessedBy'])
            ->where('disposed_by', $pharmacist->pharmacist_id)
            ->findOrFail($id);

        $relatedReceipts = StockReceipt::where('medicine_id', $disposal->medicine_id)
            ->where('batch_number', $disposal->batch_number)
            ->latest()
            ->limit(3)
            ->get();

        $relatedRestocks = RestockRequest::where('medicine_id', $disposal->medicine_id)
            ->latest()
            ->limit(3)
            ->get();

        return view('pharmacist.pharmacist_disposalShow', compact('disposal', 'relatedReceipts', 'relatedRestocks'));
    }
}