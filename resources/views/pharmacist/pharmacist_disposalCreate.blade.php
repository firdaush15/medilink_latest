<!-- resources\views\pharmacist\pharmacist_disposalCreate.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Medicine Disposal - Pharmacist</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .page-header h1 {
            font-size: 28px;
            color: #1a202c;
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-danger {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            color: #991b1b;
        }

        .form-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 30px;
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f3f4f6;
        }

        .form-section:last-child {
            border-bottom: none;
        }

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

        .required {
            color: #dc2626;
        }

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

        .help-text {
            font-size: 13px;
            color: #6b7280;
            margin-top: 6px;
        }

        .medicine-info-box {
            background: #fef2f2;
            border: 2px solid #fecaca;
            border-radius: 8px;
            padding: 16px;
            margin-top: 10px;
            display: none;
        }

        .medicine-info-box.show {
            display: block;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .info-label {
            font-weight: 600;
            color: #991b1b;
        }

        .info-value {
            color: #7f1d1d;
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
        }

        .btn-cancel {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-submit {
            background: #ef4444;
            color: white;
        }

        .btn-submit:hover {
            background: #dc2626;
        }
    </style>
</head>

<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')

    <div class="container">
        <div class="page-header">
            <h1>🗑️ Record Medicine Disposal</h1>
        </div>

        <!-- Warning Alert -->
        <div class="alert alert-danger">
            <span style="font-size: 24px;">⚠️</span>
            <div>
                <strong>Important:</strong> Disposal records are permanent and used for regulatory compliance. Ensure all information is accurate.
            </div>
        </div>

        <form action="{{ route('pharmacist.disposals.store') }}" method="POST" class="form-card" id="disposalForm">
            @csrf

            <!-- ✅ SHOW VALIDATION ERRORS -->
            @if($errors->any())
            <div style="background: #fee2e2; border: 2px solid #dc2626; padding: 20px; margin-bottom: 20px; border-radius: 8px;">
                <h3 style="color: #991b1b; margin-bottom: 10px;">❌ Validation Errors:</h3>
                <ul style="color: #7f1d1d;">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(session('error'))
            <div style="background: #fee2e2; border: 2px solid #dc2626; padding: 20px; margin-bottom: 20px; border-radius: 8px;">
                <h3 style="color: #991b1b;">❌ Error:</h3>
                <p style="color: #7f1d1d;">{{ session('error') }}</p>
            </div>
            @endif

            <div class="form-section">
                <h3 class="section-title">
                    <span>💊</span>
                    <span>Select Medicine to Dispose</span>
                </h3>

                <div class="form-group">
                    <label>Medicine <span class="required">*</span></label>
                    <select name="medicine_id" id="medicineSelect" class="form-control" required
                        onchange="window.location.href='{{ route('pharmacist.disposals.create') }}?medicine_id=' + this.value">
                        <option value="">-- Choose Medicine --</option>
                        @foreach($medicines as $medicine)
                        <option value="{{ $medicine->medicine_id }}"
                            {{ (isset($selectedMedicineId) && $selectedMedicineId == $medicine->medicine_id) ? 'selected' : '' }}>
                            {{ $medicine->medicine_name }} (Stock: {{ $medicine->quantity_in_stock }})
                        </option>
                        @endforeach
                    </select>
                    <span class="help-text">Page will refresh to load batches for the selected medicine.</span>
                </div>

                <div class="form-group">
                    <label>Select Batch (Optional)</label>
                    <select name="batch_number" id="batch_number" class="form-control">
                        <option value="">-- General Stock (No specific batch) --</option>
                        @if(isset($selectedBatches) && count($selectedBatches) > 0)
                        @foreach($selectedBatches as $batch)
                        <option value="{{ $batch->batch_number }}"
                            {{ (isset($selectedBatchNumber) && $selectedBatchNumber == $batch->batch_number) ? 'selected' : '' }}>
                            Batch: {{ $batch->batch_number }} (Qty: {{ $batch->quantity }}) - Exp: {{ $batch->expiry_date }}
                        </option>
                        @endforeach
                        @else
                        @if(isset($selectedMedicineId))
                        <option value="" disabled>No batches found for this medicine</option>
                        @endif
                        @endif
                    </select>
                    <span class="help-text">Select a batch to dispose of specific items (e.g., expired batch).</span>
                </div>

                @if(isset($selectedMedicineId) && $medicines->isNotEmpty())
                @php
                $selMed = $medicines->where('medicine_id', $selectedMedicineId)->first();
                @endphp
                @if($selMed)
                <div id="medicineInfoBox" class="medicine-info-box show">
                    <div class="info-row">
                        <span class="info-label">Current Stock:</span>
                        <span class="info-value">{{ $selMed->quantity_in_stock }} units</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Unit Price:</span>
                        <span class="info-value">RM {{ number_format($selMed->unit_price, 2) }}</span>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Set global variables for the calculateLoss function
                            window.currentStock = {
                                {
                                    $selMed - > quantity_in_stock
                                }
                            };
                            window.unitPrice = {
                                {
                                    $selMed - > unit_price
                                }
                            };

                            // Update debug panel if it exists
                            const debugStock = document.getElementById('debugStock');
                            const debugPrice = document.getElementById('debugPrice');
                            if (debugStock) debugStock.textContent = window.currentStock;
                            if (debugPrice) debugPrice.textContent = window.unitPrice;
                        });
                    </script>
                </div>
                @endif
                @endif
            </div>

            <!-- Section 2: Disposal Details -->
            <div class="form-section">
                <h3 class="section-title">
                    <span>📋</span>
                    <span>Disposal Details</span>
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>Quantity to Dispose <span class="required">*</span></label>
                        <input type="number"
                            name="quantity_disposed"
                            id="quantityInput"
                            class="form-control"
                            min="1"
                            required
                            placeholder="Enter quantity"
                            oninput="calculateLoss()">
                        <span class="help-text" id="stockHelp">Max: Based on current stock</span>
                    </div>

                    <div class="form-group">
                        <label>Batch Number</label>
                        <input type="text" name="batch_number" class="form-control" placeholder="Enter batch number">
                    </div>
                </div>

                <div class="form-group">
                    <label>Estimated Loss</label>
                    <div style="padding: 12px; background: #fef2f2; border-radius: 8px; font-size: 20px; font-weight: 700; color: #dc2626;">
                        RM <span id="estimatedLoss">0.00</span>
                    </div>
                    <span class="help-text">Calculated: Quantity × Unit Price</span>
                </div>
            </div>

            <!-- Section 3: Disposal Reason -->
            <div class="form-section">
                <h3 class="section-title">
                    <span>❓</span>
                    <span>Disposal Reason</span>
                </h3>

                <div class="form-group">
                    <label>Reason <span class="required">*</span></label>
                    <select name="reason" class="form-control" required>
                        <option value="">-- Select Reason --</option>
                        <option value="Expired">Expired</option>
                        <option value="Near Expiry">Near Expiry (Dispose before expiration)</option>
                        <option value="Damaged">Damaged</option>
                        <option value="Contaminated">Contaminated</option>
                        <option value="Recalled by Manufacturer">Recalled by Manufacturer</option>
                        <option value="Quality Issue">Quality Issue</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Reason Details</label>
                    <textarea name="reason_details"
                        class="form-control"
                        rows="3"
                        placeholder="Provide additional details about the disposal reason..."></textarea>
                </div>
            </div>

            <!-- Section 4: Disposal Method -->
            <div class="form-section">
                <h3 class="section-title">
                    <span>🔥</span>
                    <span>Disposal Method (Regulatory Compliance)</span>
                </h3>

                <div class="form-group">
                    <label>Method <span class="required">*</span></label>
                    <select name="disposal_method" class="form-control" required>
                        <option value="">-- Select Method --</option>
                        <option value="Incineration">Incineration (Licensed Facility)</option>
                        <option value="Chemical Treatment">Chemical Treatment</option>
                        <option value="Encapsulation">Encapsulation (Solidification)</option>
                        <option value="Landfill (Non-hazardous)">Landfill (Non-hazardous only)</option>
                        <option value="Return to Supplier">Return to Supplier</option>
                        <option value="Other">Other (Specify in notes)</option>
                    </select>
                    <span class="help-text">Must comply with local pharmaceutical waste regulations</span>
                </div>

                <div class="form-group">
                    <label>Disposal Details</label>
                    <textarea name="disposal_details"
                        class="form-control"
                        rows="3"
                        placeholder="Provide details about the disposal process (facility name, authorization number, etc.)..."></textarea>
                </div>
            </div>

            <!-- Section 5: Authorization & Documentation -->
            <div class="form-section">
                <h3 class="section-title">
                    <span>✅</span>
                    <span>Authorization & Documentation</span>
                </h3>

                <div class="form-group">
                    <label>Witnessed By (Admin/Senior Pharmacist)</label>
                    <select name="witnessed_by" class="form-control">
                        <option value="">-- Not witnessed --</option>
                        @foreach(\App\Models\User::whereIn('role', ['admin'])->get() as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ ucfirst($user->role) }})</option>
                        @endforeach
                    </select>
                    <span class="help-text">Optional: Admin witness for controlled substances</span>
                </div>

                <div class="form-group">
                    <label>Documentation Notes</label>
                    <textarea name="documentation_notes"
                        class="form-control"
                        rows="4"
                        placeholder="Additional documentation notes (reference numbers, certificates, photos taken, etc.)..."></textarea>
                </div>
            </div>

            <!-- ✅ ADD THIS DEBUG SECTION -->
            <div class="form-section" style="background: #fff3cd; padding: 20px; border: 2px solid #ffc107;">
                <h3 style="color: #856404;">🔍 DEBUG PANEL</h3>
                <div id="debugInfo" style="font-family: monospace; font-size: 12px;">
                    <p><strong>Current Stock:</strong> <span id="debugStock">Not loaded</span></p>
                    <p><strong>Unit Price:</strong> <span id="debugPrice">Not loaded</span></p>
                    <p><strong>Form Data:</strong></p>
                    <ul id="debugFormData"></ul>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('pharmacist.disposals.index') }}" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn btn-submit">
                    Record Disposal
                </button>
            </div>
        </form>
    </div>

    <script>
        let currentStock = 0;
        let unitPrice = 0;
        let formSubmitAttempts = 0;

        console.log('=== SCRIPT LOADED ===');

        function updateDebugDisplay() {
            const debugStock = document.getElementById('debugStock');
            const debugPrice = document.getElementById('debugPrice');
            if (debugStock) debugStock.textContent = currentStock;
            if (debugPrice) debugPrice.textContent = unitPrice;
        }

        // ✅ ATTACH SUBMIT HANDLER IMMEDIATELY (before DOMContentLoaded)
        (function attachSubmitHandler() {
            console.log('=== TRYING TO ATTACH HANDLER ===');

            const form = document.getElementById('disposalForm') || document.querySelector('form');

            if (!form) {
                console.log('Form not ready yet, retrying...');
                setTimeout(attachSubmitHandler, 10);
                return;
            }

            console.log('✅ Form found:', form);
            console.log('Form action:', form.action);
            console.log('Form method:', form.method);

            // ✅ ATTACH SUBMIT HANDLER
            form.addEventListener('submit', function(e) {
                formSubmitAttempts++;
                console.log(`=== FORM SUBMIT ATTEMPT #${formSubmitAttempts} ===`);

                // PREVENT DEFAULT IMMEDIATELY
                e.preventDefault();
                e.stopPropagation();
                console.log('✅ Default prevented');

                // Collect ALL form data
                const formData = new FormData(form);

                console.log('=== ALL FORM DATA ===');
                for (let [key, value] of formData.entries()) {
                    console.log(`  ${key}: ${value || '(empty)'}`);
                }

                // Update debug panel
                const debugList = document.getElementById('debugFormData');
                if (debugList) {
                    debugList.innerHTML = '';
                    for (let [key, value] of formData.entries()) {
                        const li = document.createElement('li');
                        li.textContent = `${key}: ${value || '(empty)'}`;
                        debugList.appendChild(li);
                    }
                }

                // Validation
                const medicineId = formData.get('medicine_id');
                const quantity = formData.get('quantity_disposed');
                const reason = formData.get('reason');
                const method = formData.get('disposal_method');

                console.log('=== VALIDATION CHECK ===');
                console.log('Medicine ID:', medicineId, medicineId ? '✅' : '❌');
                console.log('Quantity:', quantity, (quantity && quantity > 0) ? '✅' : '❌');
                console.log('Reason:', reason, reason ? '✅' : '❌');
                console.log('Method:', method, method ? '✅' : '❌');

                if (!medicineId) {
                    console.error('❌ Validation failed: No medicine selected');
                    alert('Please select a medicine');
                    return false;
                }

                if (!quantity || quantity <= 0) {
                    console.error('❌ Validation failed: Invalid quantity');
                    alert('Please enter a valid quantity');
                    return false;
                }

                if (!reason) {
                    console.error('❌ Validation failed: No reason');
                    alert('Please select a disposal reason');
                    return false;
                }

                if (!method) {
                    console.error('❌ Validation failed: No method');
                    alert('Please select a disposal method');
                    return false;
                }

                console.log('✅ All validation passed');

                // Get medicine name
                const select = document.getElementById('medicineSelect');
                const option = select.options[select.selectedIndex];
                const medicineName = option.dataset.name || option.text.split('(')[0].trim() || 'Unknown';

                console.log('Medicine name for confirmation:', medicineName);

                // Confirmation dialog
                const confirmMessage = `Confirm disposal?\n\nMedicine: ${medicineName}\nQuantity: ${quantity} units\n\nThis action is permanent and will reduce inventory.`;

                console.log('Showing confirmation dialog...');
                const userConfirmed = confirm(confirmMessage);
                console.log('User response:', userConfirmed ? '✅ Confirmed' : '❌ Cancelled');

                if (!userConfirmed) {
                    console.log('❌ User cancelled, stopping submission');
                    return false;
                }

                console.log('✅ User confirmed');
                console.log('=== SUBMITTING FORM ===');
                console.log('Form action:', form.action);
                console.log('Form method:', form.method);

                // Create a new form to submit (bypasses event listeners)
                const submitForm = document.createElement('form');
                submitForm.method = 'POST';
                submitForm.action = form.action;

                // Copy all form data
                for (let [key, value] of formData.entries()) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    submitForm.appendChild(input);
                }

                // Append to body and submit
                document.body.appendChild(submitForm);

                console.log('✅ Submitting new form...');
                submitForm.submit();

                // Show loading indicator
                const submitBtn = document.querySelector('.btn-submit');
                if (submitBtn) {
                    submitBtn.textContent = 'Submitting...';
                    submitBtn.disabled = true;
                }

                return false;
            }, true); // Use capture phase

            console.log('✅ Submit handler attached to form');
        })();

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== DOM READY ===');

            const select = document.getElementById('medicineSelect');
            if (select && select.value) {
                console.log('Medicine pre-selected:', select.value);
                updateMedicineInfo();
            }

            updateDebugDisplay();
        });

        function updateMedicineInfo() {
            const select = document.getElementById('medicineSelect');
            const option = select.options[select.selectedIndex];
            const infoBox = document.getElementById('medicineInfoBox');

            console.log('=== updateMedicineInfo ===');

            if (option.value) {
                infoBox.classList.add('show');

                currentStock = parseInt(option.dataset.currentStock) || 0;
                unitPrice = parseFloat(option.dataset.unitPrice) || 0;

                console.log('✅ Parsed:', {
                    currentStock,
                    unitPrice
                });

                document.getElementById('infoCurrentStock').textContent = currentStock + ' units';
                document.getElementById('infoExpiry').textContent = option.dataset.expiry || 'N/A';
                document.getElementById('infoPrice').textContent = 'RM ' + unitPrice.toFixed(2);
                document.getElementById('infoStatus').textContent = option.dataset.status || 'N/A';

                const quantityInput = document.getElementById('quantityInput');
                quantityInput.max = currentStock;
                quantityInput.value = '';
                document.getElementById('stockHelp').textContent = `Max: ${currentStock} units`;
                document.getElementById('estimatedLoss').textContent = '0.00';

                updateDebugDisplay();
            } else {
                infoBox.classList.remove('show');
            }
        }

        function calculateLoss() {
            const quantityInput = document.getElementById('quantityInput');
            const quantity = parseFloat(quantityInput.value) || 0;

            console.log('=== calculateLoss ===', {
                quantity,
                currentStock,
                unitPrice
            });

            if (quantity <= 0) {
                document.getElementById('estimatedLoss').textContent = '0.00';
                return;
            }

            if (currentStock === 0) {
                console.warn('⚠️ Stock is 0, reloading medicine info...');
                updateMedicineInfo();
            }

            if (currentStock > 0 && quantity > currentStock) {
                alert(`Cannot dispose more than current stock (${currentStock} units)`);
                quantityInput.value = currentStock;
                calculateLoss();
                return;
            }

            const loss = quantity * unitPrice;
            document.getElementById('estimatedLoss').textContent = loss.toFixed(2);
            console.log('✅ Loss calculated:', loss);
        }
    </script>
</body>

</html>