<!-- resources\views\pharmacist\pharmacist_receiptCreate.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Stock Receipt - Pharmacist</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        
        .page-header { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e2e8f0; }
        .page-header h1 { font-size: 28px; color: #1a202c; }
        
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
        
        .form-group { display: flex; flex-direction: column; }
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
        
        .form-control.is-invalid {
            border-color: #dc2626;
        }
        
        .invalid-feedback {
            color: #dc2626;
            font-size: 13px;
            margin-top: 6px;
            display: block;
        }
        
        .help-text { font-size: 13px; color: #6b7280; margin-top: 6px; }
        
        .quality-checks {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .checkbox-group:last-child { border-bottom: none; }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .checkbox-group label { cursor: pointer; flex: 1; }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
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
        
        .alert-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }
        
        .alert ul {
            margin: 0;
            padding-left: 20px;
        }
        
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
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-cancel {
            background: #f3f4f6;
            color: #374151;
        }
        
        .btn-submit {
            background: #3b82f6;
            color: white;
        }
        
        .btn-submit:hover { background: #2563eb; }
        .btn-submit:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.6s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')
    
    <div class="container">
        <div class="page-header">
            <h1>📥 Record Stock Receipt</h1>
        </div>

        {{-- ✅ Display All Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <span style="font-size: 20px;">❌</span>
                <div style="flex: 1;">
                    <strong>Please fix the following errors:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- ✅ Display Success Message --}}
        @if (session('success'))
            <div class="alert alert-success">
                <span style="font-size: 20px;">✅</span>
                <div style="flex: 1;">
                    <strong>Success!</strong> {{ session('success') }}
                </div>
            </div>
        @endif

        {{-- ✅ Display Error Message --}}
        @if (session('error'))
            <div class="alert alert-danger">
                <span style="font-size: 20px;">❌</span>
                <div style="flex: 1;">
                    <strong>Error!</strong> {{ session('error') }}
                </div>
            </div>
        @endif

        <form action="{{ route('pharmacist.receipts.store') }}" method="POST" class="form-card" id="receiptForm" onsubmit="return validateForm()">
            @csrf

            <!-- Section 1: Link to Restock Request (Optional) -->
            <div class="form-section">
                <h3 class="section-title">
                    <span>🔗</span>
                    <span>Link to Restock Request (Optional)</span>
                </h3>

                <div class="form-group">
                    <label>Restock Request</label>
                    <select name="restock_request_id" id="requestSelect" class="form-control" onchange="autoFillFromRequest()">
                        <option value="">-- Not linked to any request --</option>
                        @foreach($pendingRequests as $request)
                            <option value="{{ $request->request_id }}" 
                                    data-medicine-id="{{ $request->medicine_id }}"
                                    data-quantity="{{ $request->quantity_requested }}"
                                    data-supplier="{{ $request->preferred_supplier }}"
                                    {{ old('restock_request_id') == $request->request_id ? 'selected' : '' }}>
                                {{ $request->request_number }} - {{ $request->medicine->medicine_name }} ({{ $request->quantity_requested }} units)
                            </option>
                        @endforeach
                    </select>
                    <span class="help-text">Select if this receipt is for an approved restock request</span>
                </div>
            </div>

            <!-- Section 2: Medicine & Quantity -->
            <div class="form-section">
                <h3 class="section-title">
                    <span>💊</span>
                    <span>Medicine & Quantity</span>
                </h3>

                <div class="form-group">
                    <label>Medicine <span class="required">*</span></label>
                    <select name="medicine_id" id="medicineSelect" class="form-control @error('medicine_id') is-invalid @enderror" required>
                        <option value="">-- Select Medicine --</option>
                        @foreach(\App\Models\MedicineInventory::orderBy('medicine_name')->get() as $medicine)
                            <option value="{{ $medicine->medicine_id }}" {{ old('medicine_id') == $medicine->medicine_id ? 'selected' : '' }}>
                                {{ $medicine->medicine_name }} ({{ $medicine->form }} {{ $medicine->strength }})
                            </option>
                        @endforeach
                    </select>
                    @error('medicine_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Quantity Received <span class="required">*</span></label>
                        <input type="number" 
                               name="quantity_received" 
                               id="quantityInput" 
                               class="form-control @error('quantity_received') is-invalid @enderror" 
                               value="{{ old('quantity_received') }}"
                               min="1" 
                               required 
                               oninput="calculateTotal()">
                        @error('quantity_received')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Unit Price (RM) <span class="required">*</span></label>
                        <input type="number" 
                               name="unit_price" 
                               id="unitPriceInput" 
                               class="form-control @error('unit_price') is-invalid @enderror" 
                               value="{{ old('unit_price') }}"
                               step="0.01" 
                               min="0" 
                               required 
                               oninput="calculateTotal()">
                        @error('unit_price')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Total Cost</label>
                    <div style="padding: 12px; background: #f0f9ff; border-radius: 8px; font-size: 20px; font-weight: 700; color: #3b82f6;">
                        RM <span id="totalCost">0.00</span>
                    </div>
                </div>
            </div>

            <!-- Section 3: Batch & Expiry -->
            <div class="form-section">
                <h3 class="section-title">
                    <span>📦</span>
                    <span>Batch & Expiry Information</span>
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>Batch Number <span class="required">*</span></label>
                        <input type="text" 
                               name="batch_number" 
                               class="form-control @error('batch_number') is-invalid @enderror" 
                               value="{{ old('batch_number') }}"
                               required>
                        @error('batch_number')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Manufacture Date</label>
                        <input type="date" 
                               name="manufacture_date" 
                               class="form-control @error('manufacture_date') is-invalid @enderror" 
                               value="{{ old('manufacture_date') }}"
                               max="{{ date('Y-m-d') }}">
                        @error('manufacture_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Expiry Date <span class="required">*</span></label>
                    <input type="date" 
                           name="expiry_date" 
                           id="expiryInput" 
                           class="form-control @error('expiry_date') is-invalid @enderror" 
                           value="{{ old('expiry_date') }}"
                           required 
                           min="{{ date('Y-m-d') }}" 
                           onchange="checkExpiryDate()">
                    <span class="help-text">Must be at least 1 year from today for quality acceptance</span>
                    @error('expiry_date')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div id="expiryWarning" class="alert alert-warning" style="display: none;">
                    <span>⚠️</span>
                    <div>
                        <strong>Short Expiry Warning:</strong> This medicine expires in less than 1 year. Quality status will be marked as "On Hold".
                    </div>
                </div>
            </div>

            <!-- Section 4: Supplier -->
            <div class="form-section">
                <h3 class="section-title">
                    <span>🏢</span>
                    <span>Supplier Information</span>
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>Supplier Name <span class="required">*</span></label>
                        <input type="text" 
                               name="supplier" 
                               id="supplierInput" 
                               class="form-control @error('supplier') is-invalid @enderror" 
                               value="{{ old('supplier') }}"
                               required>
                        @error('supplier')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Invoice Number</label>
                        <input type="text" 
                               name="supplier_invoice_number" 
                               class="form-control @error('supplier_invoice_number') is-invalid @enderror"
                               value="{{ old('supplier_invoice_number') }}">
                        @error('supplier_invoice_number')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 5: Quality Control Checks -->
            <div class="form-section">
                <h3 class="section-title">
                    <span>✅</span>
                    <span>Quality Control Checks</span>
                </h3>

                <div class="quality-checks">
                    {{-- ✅ FIXED: Added hidden inputs for unchecked state --}}
                    <input type="hidden" name="packaging_intact" value="0">
                    <div class="checkbox-group">
                        <input type="checkbox" 
                               name="packaging_intact" 
                               id="check1" 
                               value="1" 
                               {{ old('packaging_intact', '1') == '1' ? 'checked' : '' }}>
                        <label for="check1">
                            <strong>Packaging Intact</strong><br>
                            <small style="color: #6b7280;">No damage, tears, or tampering visible</small>
                        </label>
                    </div>

                    <input type="hidden" name="temperature_maintained" value="0">
                    <div class="checkbox-group">
                        <input type="checkbox" 
                               name="temperature_maintained" 
                               id="check2" 
                               value="1" 
                               {{ old('temperature_maintained', '1') == '1' ? 'checked' : '' }}>
                        <label for="check2">
                            <strong>Temperature Maintained</strong><br>
                            <small style="color: #6b7280;">Proper storage conditions during transport</small>
                        </label>
                    </div>

                    <input type="hidden" name="expiry_acceptable" value="0">
                    <div class="checkbox-group">
                        <input type="checkbox" 
                               name="expiry_acceptable" 
                               id="check3" 
                               value="1" 
                               {{ old('expiry_acceptable', '1') == '1' ? 'checked' : '' }}>
                        <label for="check3">
                            <strong>Expiry Date Acceptable</strong><br>
                            <small style="color: #6b7280;">At least 1 year from receipt date</small>
                        </label>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label>Quality Check Notes</label>
                    <textarea name="quality_check_notes" 
                              class="form-control @error('quality_check_notes') is-invalid @enderror" 
                              rows="4" 
                              placeholder="Any issues or observations during quality inspection...">{{ old('quality_check_notes') }}</textarea>
                    @error('quality_check_notes')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('pharmacist.receipts.index') }}" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn btn-submit" id="submitBtn">
                    <span id="submitText">Record Receipt</span>
                    <span id="submitSpinner" class="spinner" style="display: none;"></span>
                </button>
            </div>
        </form>
    </div>

    <script>
        // ✅ Preserve form values and auto-calculate on page load
        window.addEventListener('DOMContentLoaded', function() {
            calculateTotal();
            checkExpiryDate();
            
            // If request was pre-selected, auto-fill
            if (document.getElementById('requestSelect').value) {
                autoFillFromRequest();
            }
        });

        function autoFillFromRequest() {
            const select = document.getElementById('requestSelect');
            const option = select.options[select.selectedIndex];
            
            if (option.value) {
                // Only auto-fill if fields are empty
                if (!document.getElementById('medicineSelect').value) {
                    document.getElementById('medicineSelect').value = option.dataset.medicineId;
                }
                if (!document.getElementById('quantityInput').value) {
                    document.getElementById('quantityInput').value = option.dataset.quantity;
                }
                if (!document.getElementById('supplierInput').value) {
                    document.getElementById('supplierInput').value = option.dataset.supplier || '';
                }
                calculateTotal();
            }
        }
        
        function calculateTotal() {
            const qty = parseFloat(document.getElementById('quantityInput').value) || 0;
            const price = parseFloat(document.getElementById('unitPriceInput').value) || 0;
            const total = qty * price;
            document.getElementById('totalCost').textContent = total.toFixed(2);
        }
        
        function checkExpiryDate() {
            const expiryInput = document.getElementById('expiryInput');
            const warning = document.getElementById('expiryWarning');
            const check3 = document.getElementById('check3');
            
            if (expiryInput.value) {
                const expiryDate = new Date(expiryInput.value);
                const oneYearFromNow = new Date();
                oneYearFromNow.setFullYear(oneYearFromNow.getFullYear() + 1);
                
                if (expiryDate < oneYearFromNow) {
                    warning.style.display = 'flex';
                    check3.checked = false;
                } else {
                    warning.style.display = 'none';
                    check3.checked = true;
                }
            }
        }
        
        // ✅ Client-side validation before submit
        function validateForm() {
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');
            
            // Basic validation
            const medicineId = document.getElementById('medicineSelect').value;
            const quantity = document.getElementById('quantityInput').value;
            const unitPrice = document.getElementById('unitPriceInput').value;
            const batchNumber = document.querySelector('input[name="batch_number"]').value;
            const expiryDate = document.getElementById('expiryInput').value;
            const supplier = document.getElementById('supplierInput').value;
            
            if (!medicineId || !quantity || !unitPrice || !batchNumber || !expiryDate || !supplier) {
                alert('❌ Please fill in all required fields marked with *');
                return false;
            }
            
            if (parseFloat(quantity) <= 0) {
                alert('❌ Quantity must be greater than 0');
                return false;
            }
            
            if (parseFloat(unitPrice) < 0) {
                alert('❌ Unit price cannot be negative');
                return false;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            submitText.style.display = 'none';
            submitSpinner.style.display = 'inline-block';
            
            return true;
        }
    </script>
</body>
</html>