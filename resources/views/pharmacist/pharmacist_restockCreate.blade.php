<!-- resources\views\pharmacist\pharmacist_restockCreate.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Restock Request - Pharmacist</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .page-header h1 { font-size: 28px; color: #1a202c; }
        
        .breadcrumb {
            display: flex;
            gap: 8px;
            align-items: center;
            font-size: 14px;
            color: #64748b;
            margin-bottom: 20px;
        }
        
        .breadcrumb a { color: #3182ce; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        
        .form-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 30px;
        }
        
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .form-section:last-child { border-bottom: none; }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .required { color: #dc2626; }
        
        .form-control {
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }
        
        .help-text {
            font-size: 13px;
            color: #6b7280;
            margin-top: 6px;
        }
        
        .medicine-info-box {
            background: #f0f9ff;
            border: 2px solid #bae6fd;
            border-radius: 8px;
            padding: 16px;
            margin-top: 10px;
            display: none;
        }
        
        .medicine-info-box.show { display: block; }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0f2fe;
        }
        
        .info-row:last-child { border-bottom: none; }
        
        .info-label { font-weight: 600; color: #0369a1; }
        .info-value { color: #0c4a6e; }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }
        
        .alert-danger {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            color: #991b1b;
        }
        
        .priority-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        
        .priority-option {
            position: relative;
        }
        
        .priority-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }
        
        .priority-label {
            display: block;
            padding: 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .priority-option input:checked + .priority-label {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        
        .priority-label.critical { border-color: #fca5a5; }
        .priority-option input:checked + .priority-label.critical {
            border-color: #dc2626;
            background: #fee2e2;
        }
        
        .priority-label.urgent { border-color: #fcd34d; }
        .priority-option input:checked + .priority-label.urgent {
            border-color: #f59e0b;
            background: #fef3c7;
        }
        
        .priority-icon { font-size: 24px; margin-bottom: 8px; }
        .priority-name { font-weight: 600; }
        .priority-desc { font-size: 12px; color: #6b7280; margin-top: 4px; }
        
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 20px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-cancel {
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
        }
        
        .btn-cancel:hover { background: #e5e7eb; }
        
        .btn-submit {
            background: #3b82f6;
            color: white;
        }
        
        .btn-submit:hover { background: #2563eb; }
        
        .low-stock-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="/pharmacist/dashboard">Dashboard</a>
            <span>/</span>
            <a href="/pharmacist/restock">Restock Requests</a>
            <span>/</span>
            <span>Create New</span>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1>📦 Create Restock Request</h1>
        </div>

        <!-- Form Card -->
        <form action="{{ route('pharmacist.restock.store') }}" method="POST" class="form-card">
            @csrf

            <!-- Section 1: Medicine Selection -->
            <div class="form-section">
                <h3 class="section-title">
                    <span>💊</span>
                    <span>Select Medicine</span>
                </h3>

                <div class="form-group">
                    <label>
                        Medicine <span class="required">*</span>
                    </label>
                    <select name="medicine_id" id="medicineSelect" class="form-control" required onchange="updateMedicineInfo()">
                        <option value="">-- Select Medicine --</option>
                        @foreach($lowStockMedicines as $medicine)
                            <option value="{{ $medicine->medicine_id }}" 
                                    data-name="{{ $medicine->medicine_name }}"
                                    data-current-stock="{{ $medicine->quantity_in_stock }}"
                                    data-reorder-level="{{ $medicine->reorder_level }}"
                                    data-unit-price="{{ $medicine->unit_price }}"
                                    data-supplier="{{ $medicine->supplier }}"
                                    data-status="{{ $medicine->status }}"
                                    {{ $selectedMedicine && $selectedMedicine->medicine_id == $medicine->medicine_id ? 'selected' : '' }}>
                                {{ $medicine->medicine_name }} ({{ $medicine->form }} {{ $medicine->strength }})
                                - Current: {{ $medicine->quantity_in_stock }} units
                                @if($medicine->status == 'Low Stock')
                                    ⚠️ LOW STOCK
                                @elseif($medicine->status == 'Out of Stock')
                                    🚨 OUT OF STOCK
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <span class="help-text">Select from medicines that need restocking</span>
                </div>

                <!-- Medicine Info Display (shown after selection) -->
                <div id="medicineInfoBox" class="medicine-info-box">
                    <div class="info-row">
                        <span class="info-label">Current Stock:</span>
                        <span class="info-value" id="infoCurrentStock">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Reorder Level:</span>
                        <span class="info-value" id="infoReorderLevel">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Unit Price:</span>
                        <span class="info-value" id="infoUnitPrice">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Preferred Supplier:</span>
                        <span class="info-value" id="infoSupplier">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value" id="infoStatus">-</span>
                    </div>
                </div>
            </div>

            <!-- Section 2: Request Details -->
            <div class="form-section">
                <h3 class="section-title">
                    <span>📋</span>
                    <span>Request Details</span>
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>
                            Quantity Requested <span class="required">*</span>
                        </label>
                        <input type="number" 
                               name="quantity_requested" 
                               id="quantityInput"
                               class="form-control" 
                               min="1" 
                               required 
                               placeholder="Enter quantity"
                               oninput="calculateEstimatedCost()">
                        <span class="help-text" id="quantityHelp">Recommended: Based on reorder level</span>
                    </div>

                    <div class="form-group">
                        <label>Estimated Unit Price (RM)</label>
                        <input type="number" 
                               name="estimated_unit_price" 
                               id="unitPriceInput"
                               class="form-control" 
                               step="0.01" 
                               min="0"
                               placeholder="Auto-filled from inventory"
                               oninput="calculateEstimatedCost()">
                        <span class="help-text">Leave blank to use current price</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Estimated Total Cost</label>
                    <div style="padding: 12px; background: #f9fafb; border-radius: 8px; font-size: 24px; font-weight: 700; color: #3b82f6;">
                        RM <span id="estimatedTotal">0.00</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Preferred Supplier</label>
                    <input type="text" 
                           name="preferred_supplier" 
                           id="supplierInput"
                           class="form-control" 
                           placeholder="Enter supplier name">
                    <span class="help-text">Optional: Specify if you have a preferred supplier</span>
                </div>
            </div>

            <!-- Section 3: Priority & Justification -->
            <div class="form-section">
                <h3 class="section-title">
                    <span>⚡</span>
                    <span>Priority & Justification</span>
                </h3>

                <div class="form-group">
                    <label>
                        Priority Level <span class="required">*</span>
                    </label>
                    <div class="priority-selector">
                        <div class="priority-option">
                            <input type="radio" name="priority" value="Critical" id="priorityCritical" required>
                            <label for="priorityCritical" class="priority-label critical">
                                <div class="priority-icon">🚨</div>
                                <div class="priority-name">Critical</div>
                                <div class="priority-desc">Out of stock / Patient waiting</div>
                            </label>
                        </div>
                        
                        <div class="priority-option">
                            <input type="radio" name="priority" value="Urgent" id="priorityUrgent">
                            <label for="priorityUrgent" class="priority-label urgent">
                                <div class="priority-icon">⚠️</div>
                                <div class="priority-name">Urgent</div>
                                <div class="priority-desc">Low stock / High demand</div>
                            </label>
                        </div>
                        
                        <div class="priority-option">
                            <input type="radio" name="priority" value="Normal" id="priorityNormal" checked>
                            <label for="priorityNormal" class="priority-label">
                                <div class="priority-icon">📋</div>
                                <div class="priority-name">Normal</div>
                                <div class="priority-desc">Standard restocking</div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        Justification <span class="required">*</span>
                    </label>
                    <textarea name="justification" 
                              class="form-control" 
                              required 
                              maxlength="1000"
                              placeholder="Explain why this restock is needed. Be specific about urgency and impact on operations."></textarea>
                    <span class="help-text">Provide clear justification for admin approval</span>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('pharmacist.restock.index') }}" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn btn-submit">Submit Request</button>
            </div>
        </form>
    </div>

    <script>
        function updateMedicineInfo() {
            const select = document.getElementById('medicineSelect');
            const option = select.options[select.selectedIndex];
            const infoBox = document.getElementById('medicineInfoBox');
            
            if (option.value) {
                // Show info box
                infoBox.classList.add('show');
                
                // Update info
                document.getElementById('infoCurrentStock').textContent = option.dataset.currentStock + ' units';
                document.getElementById('infoReorderLevel').textContent = option.dataset.reorderLevel + ' units';
                document.getElementById('infoUnitPrice').textContent = 'RM ' + parseFloat(option.dataset.unitPrice).toFixed(2);
                document.getElementById('infoSupplier').textContent = option.dataset.supplier || 'Not specified';
                document.getElementById('infoStatus').textContent = option.dataset.status;
                
                // Auto-fill fields
                document.getElementById('unitPriceInput').value = option.dataset.unitPrice;
                document.getElementById('supplierInput').value = option.dataset.supplier || '';
                
                // Suggest quantity
                const currentStock = parseInt(option.dataset.currentStock);
                const reorderLevel = parseInt(option.dataset.reorderLevel);
                const suggestedQty = Math.max(reorderLevel - currentStock, reorderLevel);
                document.getElementById('quantityInput').value = suggestedQty;
                document.getElementById('quantityHelp').textContent = `Recommended: ${suggestedQty} units (to reach reorder level)`;
                
                // Auto-select priority based on stock
                if (option.dataset.status === 'Out of Stock') {
                    document.getElementById('priorityCritical').checked = true;
                } else if (option.dataset.status === 'Low Stock') {
                    document.getElementById('priorityUrgent').checked = true;
                }
                
                calculateEstimatedCost();
            } else {
                infoBox.classList.remove('show');
            }
        }
        
        function calculateEstimatedCost() {
            const quantity = parseFloat(document.getElementById('quantityInput').value) || 0;
            const unitPrice = parseFloat(document.getElementById('unitPriceInput').value) || 0;
            const total = quantity * unitPrice;
            
            document.getElementById('estimatedTotal').textContent = total.toFixed(2);
        }
        
        // Initialize if medicine is pre-selected
        if (document.getElementById('medicineSelect').value) {
            updateMedicineInfo();
        }
    </script>
</body>
</html>