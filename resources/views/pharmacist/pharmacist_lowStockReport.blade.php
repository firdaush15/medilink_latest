<!-- resources/views/pharmacist/pharmacist_lowStockReport.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Low Stock Report - MediLink</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css', 'resources/css/pharmacist/pharmacist_lowStockReport.css'])
</head>
<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')

    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="top-bar-left">
                <a href="{{ route('pharmacist.inventory') }}" class="back-btn">
                    ← Back to Inventory
                </a>
                <h1>⚠️ Low Stock Report</h1>
            </div>
            <div class="user-info">
                <span>{{ auth()->user()->name }}</span>
                <img src="{{ auth()->user()->profile_photo ?? asset('assets/default-avatar.png') }}" alt="Profile" class="profile-pic">
            </div>
        </div>

        <!-- Report Summary -->
        <div class="report-summary">
            <div class="summary-card critical">
                <div class="summary-icon">❌</div>
                <div class="summary-info">
                    <h3>{{ $lowStockMedicines->where('status', 'Out of Stock')->count() }}</h3>
                    <p>Out of Stock</p>
                </div>
            </div>

            <div class="summary-card warning">
                <div class="summary-icon">⚠️</div>
                <div class="summary-info">
                    <h3>{{ $lowStockMedicines->where('status', 'Low Stock')->count() }}</h3>
                    <p>Low Stock</p>
                </div>
            </div>

            <div class="summary-card info">
                <div class="summary-icon">📊</div>
                <div class="summary-info">
                    <h3>{{ $lowStockMedicines->count() }}</h3>
                    <p>Total Items</p>
                </div>
            </div>

            <div class="summary-card value">
                <div class="summary-icon">💰</div>
                <div class="summary-info">
                    <h3>RM {{ number_format($lowStockMedicines->sum(function($med) { 
                        return ($med->reorder_level - $med->quantity_in_stock) * $med->unit_price; 
                    }), 2) }}</h3>
                    <p>Estimated Restock Value</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button onclick="window.print()" class="btn btn-print">
                🖨️ Print Report
            </button>
            <a href="{{ route('pharmacist.inventory.export') }}?status=Low Stock" class="btn btn-export">
                📥 Export CSV
            </a>
            <button onclick="generatePurchaseOrder()" class="btn btn-primary">
                📋 Generate Purchase Order
            </button>
        </div>

        <!-- Report Table -->
        <div class="report-container">
            @if($lowStockMedicines->count() > 0)
            <table class="report-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Priority</th>
                        <th>Medicine Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Reorder Level</th>
                        <th>Shortage</th>
                        <th>Unit Price</th>
                        <th>Restock Cost</th>
                        <th>Supplier</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockMedicines as $medicine)
                    <tr class="medicine-row {{ $medicine->quantity_in_stock == 0 ? 'critical-row' : 'warning-row' }}">
                        <td><input type="checkbox" class="medicine-checkbox" value="{{ $medicine->medicine_id }}"></td>
                        <td>
                            @if($medicine->quantity_in_stock == 0)
                            <span class="priority-badge critical">URGENT</span>
                            @elseif($medicine->quantity_in_stock <= ($medicine->reorder_level * 0.5))
                            <span class="priority-badge high">HIGH</span>
                            @else
                            <span class="priority-badge medium">MEDIUM</span>
                            @endif
                        </td>
                        <td>
                            <div class="medicine-info">
                                <strong>{{ $medicine->medicine_name }}</strong>
                                <small>{{ $medicine->form }} - {{ $medicine->strength }}</small>
                                @if($medicine->brand_name)
                                <small class="text-muted">{{ $medicine->brand_name }}</small>
                                @endif
                            </div>
                        </td>
                        <td><span class="category-badge">{{ $medicine->category }}</span></td>
                        <td>
                            <span class="stock-number {{ $medicine->quantity_in_stock == 0 ? 'critical' : 'warning' }}">
                                {{ $medicine->quantity_in_stock }}
                            </span>
                        </td>
                        <td>{{ $medicine->reorder_level }}</td>
                        <td>
                            <span class="shortage-number">
                                {{ $medicine->reorder_level - $medicine->quantity_in_stock }}
                            </span>
                        </td>
                        <td>RM {{ number_format($medicine->unit_price, 2) }}</td>
                        <td>
                            <strong class="restock-cost">
                                RM {{ number_format(($medicine->reorder_level - $medicine->quantity_in_stock) * $medicine->unit_price, 2) }}
                            </strong>
                        </td>
                        <td>{{ $medicine->supplier ?? 'N/A' }}</td>
                        <td>
                            <small>{{ $medicine->updated_at->format('d M Y') }}</small>
                            <br>
                            <small class="text-muted">{{ $medicine->updated_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="action-btns">
                                <button onclick="quickRestock({{ $medicine->medicine_id }})" 
                                        class="btn-action btn-restock" 
                                        title="Quick Restock">
                                    📦
                                </button>
                                <a href="{{ route('pharmacist.inventory.edit', $medicine->medicine_id) }}" 
                                   class="btn-action btn-edit" 
                                   title="Edit">
                                    ✏️
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="8" class="text-right"><strong>Total Restock Cost:</strong></td>
                        <td colspan="4">
                            <strong class="total-cost">
                                RM {{ number_format($lowStockMedicines->sum(function($med) { 
                                    return ($med->reorder_level - $med->quantity_in_stock) * $med->unit_price; 
                                }), 2) }}
                            </strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
            @else
            <div class="empty-state">
                <div class="empty-icon">✅</div>
                <h3>All Stock Levels Healthy!</h3>
                <p>No medicines are currently low on stock.</p>
                <a href="{{ route('pharmacist.inventory') }}" class="btn btn-primary">
                    Back to Inventory
                </a>
            </div>
            @endif
        </div>

        <!-- Bulk Actions Bar -->
        @if($lowStockMedicines->count() > 0)
        <div id="bulk-actions" class="bulk-actions" style="display: none;">
            <span id="selected-count">0 items selected</span>
            <button onclick="bulkRestock()" class="btn btn-primary">📦 Bulk Restock Selected</button>
            <button onclick="exportSelected()" class="btn btn-secondary">📥 Export Selected</button>
        </div>
        @endif
    </div>

    <!-- Quick Restock Modal -->
    <div id="restockModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRestockModal()">&times;</span>
            <h2>📦 Quick Restock</h2>
            <form id="restockForm" method="POST">
                @csrf
                <input type="hidden" id="restock_medicine_id">

                <div class="form-group">
                    <label><strong>Medicine:</strong></label>
                    <p id="restock_medicine_name" class="info-display"></p>
                </div>

                <div class="form-group">
                    <label><strong>Current Stock:</strong></label>
                    <p id="restock_current_stock" class="info-display critical-text"></p>
                </div>

                <div class="form-group">
                    <label><strong>Reorder Level:</strong></label>
                    <p id="restock_reorder_level" class="info-display"></p>
                </div>

                <div class="form-group">
                    <label><strong>Shortage:</strong></label>
                    <p id="restock_shortage" class="info-display warning-text"></p>
                </div>

                <div class="form-group">
                    <label>Restock Quantity <span class="required">*</span></label>
                    <input type="number" 
                           id="restock_quantity" 
                           name="quantity" 
                           min="1" 
                           required>
                    <small class="form-hint">Recommended: <span id="recommended_qty"></span> units</small>
                </div>

                <div class="form-group">
                    <label>Batch Number</label>
                    <input type="text" name="batch_number" placeholder="e.g., BAT2024-001">
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" rows="3" placeholder="Optional notes..."></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" onclick="closeRestockModal()" class="btn btn-cancel">Cancel</button>
                    <button type="submit" class="btn btn-submit">
                        💾 Confirm Restock
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ========================================
        // SELECT ALL FUNCTIONALITY
        // ========================================
        document.getElementById('select-all')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.medicine-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });

        document.querySelectorAll('.medicine-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActions);
        });

        function updateBulkActions() {
            const selected = document.querySelectorAll('.medicine-checkbox:checked');
            const bulkActions = document.getElementById('bulk-actions');
            const selectedCount = document.getElementById('selected-count');

            if (selected.length > 0) {
                bulkActions.style.display = 'flex';
                selectedCount.textContent = `${selected.length} item(s) selected`;
            } else {
                bulkActions.style.display = 'none';
            }
        }

        // ========================================
        // QUICK RESTOCK MODAL
        // ========================================
        function quickRestock(medicineId) {
            // Find medicine row
            const row = document.querySelector(`input.medicine-checkbox[value="${medicineId}"]`).closest('tr');
            const medicineName = row.querySelector('.medicine-info strong').textContent;
            const currentStock = parseInt(row.querySelectorAll('td')[4].textContent.trim());
            const reorderLevel = parseInt(row.querySelectorAll('td')[5].textContent.trim());
            const shortage = reorderLevel - currentStock;

            // Populate modal
            document.getElementById('restock_medicine_id').value = medicineId;
            document.getElementById('restock_medicine_name').textContent = medicineName;
            document.getElementById('restock_current_stock').textContent = currentStock + ' units';
            document.getElementById('restock_reorder_level').textContent = reorderLevel + ' units';
            document.getElementById('restock_shortage').textContent = shortage + ' units';
            document.getElementById('recommended_qty').textContent = shortage;
            document.getElementById('restock_quantity').value = shortage;

            // Set form action
            document.getElementById('restockForm').action = `/pharmacist/inventory/${medicineId}/adjust-stock`;

            // Show modal
            document.getElementById('restockModal').style.display = 'block';
        }

        function closeRestockModal() {
            document.getElementById('restockModal').style.display = 'none';
            document.getElementById('restockForm').reset();
        }

        // Handle form submission
        document.getElementById('restockForm').addEventListener('submit', function(e) {
            // Add adjustment_type and reason as hidden fields
            const adjustmentType = document.createElement('input');
            adjustmentType.type = 'hidden';
            adjustmentType.name = 'adjustment_type';
            adjustmentType.value = 'add';
            this.appendChild(adjustmentType);

            const reason = document.createElement('input');
            reason.type = 'hidden';
            reason.name = 'reason';
            reason.value = 'purchase';
            this.appendChild(reason);
        });

        // ========================================
        // BULK ACTIONS
        // ========================================
        function bulkRestock() {
            const selected = Array.from(document.querySelectorAll('.medicine-checkbox:checked'));
            
            if (selected.length === 0) {
                alert('Please select at least one medicine');
                return;
            }

            if (confirm(`Restock ${selected.length} selected medicine(s)?`)) {
                // TODO: Implement bulk restock logic
                alert('Bulk restock functionality coming soon!');
            }
        }

        function exportSelected() {
            const selected = Array.from(document.querySelectorAll('.medicine-checkbox:checked'))
                .map(cb => cb.value);
            
            if (selected.length === 0) {
                alert('Please select at least one medicine');
                return;
            }

            // TODO: Implement export selected
            alert('Export selected functionality coming soon!');
        }

        function generatePurchaseOrder() {
            if (confirm('Generate purchase order for all low stock items?')) {
                // TODO: Implement purchase order generation
                alert('Purchase order generation coming soon!');
            }
        }

        // ========================================
        // PRINT STYLES
        // ========================================
        window.onbeforeprint = function() {
            document.querySelector('.bulk-actions').style.display = 'none';
            document.querySelectorAll('.btn-action').forEach(btn => btn.style.display = 'none');
        };

        window.onafterprint = function() {
            updateBulkActions();
        };

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('restockModal');
            if (event.target == modal) {
                closeRestockModal();
            }
        };
    </script>
</body>
</html>