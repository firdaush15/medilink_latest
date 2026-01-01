<!-- resources/views/pharmacist/pharmacist_inventoryCreate.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Add New Medicine - MediLink</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css', 'resources/css/pharmacist/pharmacist_inventoryCreate.css'])
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
                <h1>➕ Add New Medicine</h1>
            </div>
            <div class="user-info">
                <span>{{ auth()->user()->name }}</span>
                <img src="{{ auth()->user()->profile_photo ?? asset('assets/default-avatar.png') }}" alt="Profile" class="profile-pic">
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="alert alert-success">
            <strong>✓</strong> {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error">
            <strong>✗</strong> {{ session('error') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-error">
            <strong>✗</strong> Please fix the following errors:
            <ul style="margin: 5px 0 0 20px;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Add Medicine Form -->
        <div class="form-container">
            <form action="{{ route('pharmacist.inventory.store') }}" method="POST" id="addMedicineForm">
                @csrf

                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h2>💊 Basic Information</h2>
                        <p class="section-description">Enter the medicine's basic details</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="medicine_name">Medicine Name <span class="required">*</span></label>
                            <input type="text" 
                                   id="medicine_name" 
                                   name="medicine_name" 
                                   value="{{ old('medicine_name') }}"
                                   placeholder="e.g., Paracetamol"
                                   required
                                   autocomplete="off">
                            <div id="medicine-suggestions" class="autocomplete-suggestions"></div>
                            <small class="form-hint">Start typing to see suggestions</small>
                        </div>

                        <div class="form-group">
                            <label for="brand_name">Brand Name</label>
                            <input type="text" 
                                   id="brand_name" 
                                   name="brand_name" 
                                   value="{{ old('brand_name') }}"
                                   placeholder="e.g., Panadol">
                            <small class="form-hint">Optional: Commercial brand name</small>
                        </div>

                        <div class="form-group">
                            <label for="generic_name">Generic Name</label>
                            <input type="text" 
                                   id="generic_name" 
                                   name="generic_name" 
                                   value="{{ old('generic_name') }}"
                                   placeholder="e.g., Acetaminophen">
                            <small class="form-hint">Scientific/generic name</small>
                        </div>

                        <div class="form-group">
                            <label for="category">Category <span class="required">*</span></label>
                            <select id="category" name="category" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="form">Form <span class="required">*</span></label>
                            <select id="form" name="form" required>
                                <option value="">-- Select Form --</option>
                                @foreach($forms as $f)
                                <option value="{{ $f }}" {{ old('form') == $f ? 'selected' : '' }}>{{ $f }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="strength">Strength <span class="required">*</span></label>
                            <div class="input-group">
                                <input type="text" 
                                       id="strength" 
                                       name="strength" 
                                       value="{{ old('strength') }}"
                                       placeholder="e.g., 500"
                                       required>
                                <select id="strength_unit" class="unit-select">
                                    <option value="mg">mg</option>
                                    <option value="g">g</option>
                                    <option value="mcg">mcg</option>
                                    <option value="ml">ml</option>
                                    <option value="IU">IU</option>
                                    <option value="%">%</option>
                                </select>
                            </div>
                            <small class="form-hint">Enter value and select unit</small>
                        </div>
                    </div>
                </div>

                <!-- Stock Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h2>📦 Stock Information</h2>
                        <p class="section-description">Set initial stock levels and pricing</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="quantity_in_stock">Initial Stock Quantity <span class="required">*</span></label>
                            <input type="number" 
                                   id="quantity_in_stock" 
                                   name="quantity_in_stock" 
                                   value="{{ old('quantity_in_stock', 0) }}"
                                   min="0"
                                   required>
                            <small class="form-hint">Number of units in stock</small>
                        </div>

                        <div class="form-group">
                            <label for="reorder_level">Reorder Level <span class="required">*</span></label>
                            <input type="number" 
                                   id="reorder_level" 
                                   name="reorder_level" 
                                   value="{{ old('reorder_level', 50) }}"
                                   min="0"
                                   required>
                            <small class="form-hint">Alert when stock falls below this</small>
                        </div>

                        <div class="form-group">
                            <label for="unit_price">Unit Price (RM) <span class="required">*</span></label>
                            <input type="number" 
                                   id="unit_price" 
                                   name="unit_price" 
                                   value="{{ old('unit_price') }}"
                                   step="0.01"
                                   min="0"
                                   required>
                            <small class="form-hint">Price per unit</small>
                        </div>

                        <div class="form-group">
                            <label for="supplier">Supplier</label>
                            <input type="text" 
                                   id="supplier" 
                                   name="supplier" 
                                   value="{{ old('supplier') }}"
                                   placeholder="e.g., Pharmaco Ltd">
                        </div>

                        <div class="form-group">
                            <label for="batch_number">Batch Number</label>
                            <input type="text" 
                                   id="batch_number" 
                                   name="batch_number" 
                                   value="{{ old('batch_number') }}"
                                   placeholder="e.g., BAT2024-001">
                        </div>
                    </div>
                </div>

                <!-- Date Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h2>📅 Date Information</h2>
                        <p class="section-description">Manufacturing and expiry dates</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="manufacture_date">Manufacture Date</label>
                            <input type="date" 
                                   id="manufacture_date" 
                                   name="manufacture_date" 
                                   value="{{ old('manufacture_date') }}">
                        </div>

                        <div class="form-group">
                            <label for="expiry_date">Expiry Date <span class="required">*</span></label>
                            <input type="date" 
                                   id="expiry_date" 
                                   name="expiry_date" 
                                   value="{{ old('expiry_date') }}"
                                   required>
                            <small class="form-hint" id="expiry-warning"></small>
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h2>ℹ️ Additional Information</h2>
                        <p class="section-description">Storage instructions and medical information</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="storage_instructions">Storage Instructions</label>
                            <textarea id="storage_instructions" 
                                      name="storage_instructions" 
                                      rows="3" 
                                      placeholder="e.g., Store at room temperature (15-30°C), keep away from moisture">{{ old('storage_instructions') }}</textarea>
                        </div>

                        <div class="form-group full-width">
                            <label for="side_effects">Common Side Effects</label>
                            <textarea id="side_effects" 
                                      name="side_effects" 
                                      rows="3" 
                                      placeholder="e.g., Nausea, dizziness, headache">{{ old('side_effects') }}</textarea>
                        </div>

                        <div class="form-group full-width">
                            <label for="contraindications">Contraindications</label>
                            <textarea id="contraindications" 
                                      name="contraindications" 
                                      rows="3" 
                                      placeholder="e.g., Not for patients with liver disease, pregnancy">{{ old('contraindications') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Prescription & Control Section -->
                <div class="form-section">
                    <div class="section-header">
                        <h2>🔒 Prescription & Control</h2>
                        <p class="section-description">Regulatory information</p>
                    </div>

                    <div class="form-grid checkbox-grid">
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" 
                                       name="requires_prescription" 
                                       id="requires_prescription"
                                       value="1"
                                       {{ old('requires_prescription', true) ? 'checked' : '' }}>
                                <span class="checkbox-custom"></span>
                                <span class="checkbox-text">Requires Prescription</span>
                            </label>
                            <small class="form-hint">Check if this medicine needs a doctor's prescription</small>
                        </div>

                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" 
                                       name="is_controlled_substance" 
                                       id="is_controlled_substance"
                                       value="1"
                                       {{ old('is_controlled_substance') ? 'checked' : '' }}>
                                <span class="checkbox-custom"></span>
                                <span class="checkbox-text">Controlled Substance</span>
                            </label>
                            <small class="form-hint">Check if this is a controlled/scheduled drug</small>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="{{ route('pharmacist.inventory') }}" class="btn btn-cancel">Cancel</a>
                    <button type="reset" class="btn btn-reset">Reset Form</button>
                    <button type="submit" class="btn btn-submit" id="submitBtn">
                        <span class="btn-text">💾 Add Medicine</span>
                        <span class="btn-loader" style="display: none;">
                            <span class="spinner"></span> Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ========================================
        // AUTOCOMPLETE FOR MEDICINE NAMES
        // ========================================
        const medicineInput = document.getElementById('medicine_name');
        const suggestionsDiv = document.getElementById('medicine-suggestions');
        
        // Common medicine names for autocomplete
        const commonMedicines = [
            'Paracetamol', 'Ibuprofen', 'Amoxicillin', 'Aspirin', 'Metformin',
            'Omeprazole', 'Losartan', 'Atorvastatin', 'Amlodipine', 'Cetirizine',
            'Ciprofloxacin', 'Doxycycline', 'Gabapentin', 'Levothyroxine', 'Lisinopril',
            'Metoprolol', 'Pantoprazole', 'Prednisone', 'Simvastatin', 'Warfarin'
        ];

        medicineInput.addEventListener('input', function() {
            const value = this.value.toLowerCase();
            suggestionsDiv.innerHTML = '';
            
            if (value.length < 2) {
                suggestionsDiv.style.display = 'none';
                return;
            }

            const matches = commonMedicines.filter(med => 
                med.toLowerCase().includes(value)
            );

            if (matches.length > 0) {
                suggestionsDiv.style.display = 'block';
                matches.forEach(match => {
                    const div = document.createElement('div');
                    div.className = 'suggestion-item';
                    div.textContent = match;
                    div.addEventListener('click', function() {
                        medicineInput.value = match;
                        suggestionsDiv.style.display = 'none';
                    });
                    suggestionsDiv.appendChild(div);
                });
            } else {
                suggestionsDiv.style.display = 'none';
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target !== medicineInput) {
                suggestionsDiv.style.display = 'none';
            }
        });

        // ========================================
        // STRENGTH UNIT CONCATENATION
        // ========================================
        const strengthInput = document.getElementById('strength');
        const strengthUnit = document.getElementById('strength_unit');

        document.getElementById('addMedicineForm').addEventListener('submit', function(e) {
            // Combine strength value and unit
            if (strengthInput.value) {
                strengthInput.value = strengthInput.value.replace(/[a-zA-Z%]/g, '') + strengthUnit.value;
            }
        });

        // ========================================
        // EXPIRY DATE VALIDATION
        // ========================================
        const expiryDate = document.getElementById('expiry_date');
        const expiryWarning = document.getElementById('expiry-warning');

        expiryDate.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            const thirtyDaysFromNow = new Date();
            thirtyDaysFromNow.setDate(today.getDate() + 30);

            if (selectedDate < today) {
                expiryWarning.textContent = '⚠️ Warning: This date is in the past';
                expiryWarning.style.color = '#dc3545';
            } else if (selectedDate < thirtyDaysFromNow) {
                expiryWarning.textContent = '⚠️ Warning: Expires within 30 days';
                expiryWarning.style.color = '#ff9800';
            } else {
                expiryWarning.textContent = '✓ Valid expiry date';
                expiryWarning.style.color = '#28a745';
            }
        });

        // ========================================
        // FORM SUBMISSION
        // ========================================
        document.getElementById('addMedicineForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoader = submitBtn.querySelector('.btn-loader');

            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline-flex';
        });

        // ========================================
        // AUTO-HIDE ALERTS
        // ========================================
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>