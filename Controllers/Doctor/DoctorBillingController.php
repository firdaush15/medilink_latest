<?php
// app/Http/Controllers/Doctor/DoctorBillingController.php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\BillingItem;
use App\Models\ProcedurePrice;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorBillingController extends Controller
{
    /**
     * Add billing item to appointment (AJAX)
     */
    public function addBillingItem(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,appointment_id',
            'procedure_code' => 'required|exists:procedure_prices,procedure_code',
            'quantity' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Get procedure details
            $procedure = ProcedurePrice::where('procedure_code', $validated['procedure_code'])->firstOrFail();
            $quantity = $validated['quantity'] ?? 1;
            
            // Create billing item
            $billingItem = BillingItem::create([
                'appointment_id' => $validated['appointment_id'],
                'item_type' => $this->mapCategoryToItemType($procedure->category),
                'item_code' => $procedure->procedure_code,
                'description' => $procedure->procedure_name,
                'quantity' => $quantity,
                'unit_price' => $procedure->base_price,
                'amount' => $procedure->base_price * $quantity,
                'added_by' => auth()->id(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Procedure added to billing',
                'item' => [
                    'billing_item_id' => $billingItem->billing_item_id,
                    'description' => $billingItem->description,
                    'quantity' => $billingItem->quantity,
                    'unit_price' => number_format($billingItem->unit_price, 2),
                    'amount' => number_format($billingItem->amount, 2),
                ],
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Add billing item error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add billing item: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Remove billing item
     */
    public function removeBillingItem($itemId)
    {
        try {
            $item = BillingItem::findOrFail($itemId);
            
            // Only allow doctor who added it to remove
            if ($item->added_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to remove this item',
                ], 403);
            }
            
            // Check if appointment is already checked out
            $appointment = Appointment::find($item->appointment_id);
            if ($appointment->isCheckedOut()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot remove item - appointment already checked out',
                ], 400);
            }
            
            $item->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Billing item removed',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get billing summary for appointment
     */
    public function getBillingSummary($appointmentId)
    {
        try {
            $items = BillingItem::where('appointment_id', $appointmentId)
                ->with('addedBy')
                ->get();
            
            $total = $items->sum('amount');
            
            $itemsByType = $items->groupBy('item_type')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('amount'),
                    'items' => $group->map(function ($item) {
                        return [
                            'billing_item_id' => $item->billing_item_id,
                            'description' => $item->description,
                            'quantity' => $item->quantity,
                            'unit_price' => number_format($item->unit_price, 2),
                            'amount' => number_format($item->amount, 2),
                            'added_by' => $item->addedBy->name,
                        ];
                    }),
                ];
            });
            
            return response()->json([
                'success' => true,
                'summary' => [
                    'total_items' => $items->count(),
                    'total_amount' => number_format($total, 2),
                    'by_type' => $itemsByType,
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get billing summary: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Helper: Map procedure category to billing item type
     */
    private function mapCategoryToItemType($category)
    {
        return match ($category) {
            'consultation' => 'consultation',
            'blood_test' => 'lab_test',
            'imaging' => 'imaging',
            'minor_procedure', 'major_procedure' => 'procedure',
            'diagnostic_test' => 'lab_test',
            default => 'other',
        };
    }
}