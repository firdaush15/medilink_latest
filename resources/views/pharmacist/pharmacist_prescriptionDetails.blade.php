<!-- resources/views/pharmacist/pharmacist_prescriptionDetails.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Prescription Verification - MediLink</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Success/Error Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* CRITICAL ALLERGY WARNING */
        .allergy-critical-banner {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 3px solid #bd2130;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }

            50% {
                box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
            }
        }

        .allergy-critical-banner .icon {
            font-size: 48px;
            display: inline-block;
            animation: shake 0.5s infinite;
        }

        @keyframes shake {

            0%,
            100% {
                transform: rotate(0deg);
            }

            25% {
                transform: rotate(-10deg);
            }

            75% {
                transform: rotate(10deg);
            }
        }

        .allergy-critical-banner h2 {
            margin: 10px 0;
            font-size: 24px;
        }

        .allergy-warning-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid white;
        }

        /* PATIENT ALLERGIES SECTION */
        .allergies-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .allergies-section h3 {
            color: #dc3545;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .allergy-card {
            background: #fff5f5;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .allergy-card.severe {
            background: #ffe6e6;
            border-left-color: #c82333;
        }

        .allergy-card.moderate {
            background: #fff8e6;
            border-left-color: #ffc107;
        }

        .allergy-card.mild {
            background: #f0f9ff;
            border-left-color: #17a2b8;
        }

        .allergy-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .allergen-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .severity-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .severity-badge.life-threatening {
            background: #dc3545;
            color: white;
        }

        .severity-badge.severe {
            background: #fd7e14;
            color: white;
        }

        .severity-badge.moderate {
            background: #ffc107;
            color: #333;
        }

        .severity-badge.mild {
            background: #17a2b8;
            color: white;
        }

        .allergy-details {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .no-allergies {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }

        /* PRESCRIPTION ITEMS */
        .prescription-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .medicine-item {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }

        .medicine-item.has-allergy {
            border-left-color: #dc3545;
            background: #fff5f5;
        }

        .medicine-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .medicine-details {
            color: #666;
            margin-top: 8px;
        }

        /* SAFETY CHECKS */
        .safety-checks {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .check-item {
            padding: 12px;
            margin: 8px 0;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
        }

        .check-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .check-item.checked {
            background: #d4edda;
            border-color: #28a745;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-verify {
            background: #28a745;
            color: white;
        }

        .btn-verify:hover:not(:disabled) {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }

        .btn-reject {
            background: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }

        .loading.active {
            display: block;
        }
    </style>
</head>

<body>
    <div class="container">
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
            <strong>✗</strong>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="header">
            <h1>Prescription Verification</h1>
            <p>Prescription ID: #{{ str_pad($prescription->prescription_id, 5, '0', STR_PAD_LEFT) }}</p>
            <p><strong>Current Status:</strong>
                <span style="color: {{ $dispensing->verification_status == 'Pending' ? '#ffc107' : '#28a745' }};">
                    {{ $dispensing->verification_status }}
                </span>
            </p>
        </div>

        <!-- CRITICAL ALLERGY WARNING (if any) -->
        @if(count($allergyWarnings) > 0)
        <div class="allergy-critical-banner">
            <div class="icon">⚠️</div>
            <h2>CRITICAL ALLERGY ALERT!</h2>
            <p><strong>This prescription contains medications the patient is allergic to!</strong></p>

            @foreach($allergyWarnings as $warning)
            <div class="allergy-warning-item">
                <strong>{{ $warning['medicine'] }}</strong> matches patient allergy to
                <strong>{{ $warning['allergen'] }}</strong>
                <br>
                <strong>Severity:</strong> {{ $warning['severity'] }}
                @if($warning['reaction'])
                <br>
                <strong>Reaction:</strong> {{ $warning['reaction'] }}
                @endif
            </div>
            @endforeach

            <p style="margin-top: 15px; font-size: 14px;">
                ⚠️ <strong>ACTION REQUIRED:</strong> Contact prescribing doctor immediately before proceeding!
            </p>
        </div>
        @endif

        <!-- PATIENT ALLERGIES SECTION -->
        <div class="allergies-section">
            <h3>
                <span style="font-size: 24px;">🚨</span>
                Patient Known Allergies
            </h3>

            @if($prescription->patient->activeAllergies->count() > 0)
            @foreach($prescription->patient->activeAllergies as $allergy)
            <div class="allergy-card {{ strtolower($allergy->severity) }}">
                <div class="allergy-header">
                    <div class="allergen-name">
                        {{ $allergy->allergen_name }}
                        <span style="font-size: 14px; color: #666;">({{ $allergy->allergy_type }})</span>
                    </div>
                    <span class="severity-badge {{ strtolower(str_replace(['-', ' '], '', $allergy->severity)) }}">
                        {{ $allergy->severity }}
                    </span>
                </div>

                <div class="allergy-details">
                    @if($allergy->reaction_description)
                    <strong>Reaction:</strong> {{ $allergy->reaction_description }}<br>
                    @endif

                    @if($allergy->onset_date)
                    <strong>First Reported:</strong> {{ $allergy->onset_date->format('M d, Y') }}<br>
                    @endif

                    @if($allergy->notes)
                    <strong>Notes:</strong> {{ $allergy->notes }}
                    @endif
                </div>
            </div>
            @endforeach
            @else
            <div class="no-allergies">
                ✅ No known allergies recorded for this patient
            </div>
            @endif
        </div>

        <!-- PATIENT INFO -->
        <div class="prescription-section">
            <h3>Patient Information</h3>
            <p><strong>Name:</strong> {{ $prescription->patient->user->name }}</p>
            <p><strong>Age:</strong> {{ $prescription->patient->age }} years</p>
            <p><strong>Gender:</strong> {{ $prescription->patient->gender }}</p>

            @if($prescription->patient->blood_type && $prescription->patient->blood_type !== 'Unknown')
            <p><strong>Blood Type:</strong> {{ $prescription->patient->blood_type }}</p>
            @endif

            @if($prescription->patient->chronic_conditions)
            <p><strong>Chronic Conditions:</strong> {{ $prescription->patient->chronic_conditions }}</p>
            @endif

            @if($prescription->patient->current_medications)
            <p><strong>Current Medications:</strong> {{ $prescription->patient->current_medications }}</p>
            @endif
        </div>

        <!-- PRESCRIPTION ITEMS -->
        <div class="prescription-section">
            <h3>Prescribed Medications</h3>

            @foreach($prescription->items as $item)
            <div class="medicine-item">
                <div class="medicine-header">
                    <h4>{{ $item->medicine_name }}</h4>
                    @if($item->medicine_id)
                    <span class="badge badge-info">In Inventory</span>
                    @else
                    <span class="badge badge-warning">Not Linked</span>
                    @endif
                </div>

                <div class="medicine-details-grid">
                    <div class="detail-item">
                        <span class="label">Dosage per dose:</span>
                        <span class="value">{{ $item->dosage }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="label">Frequency:</span>
                        <span class="value">{{ $item->frequency }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="label">Duration:</span>
                        <span class="value">{{ $item->days_supply ?? 'N/A' }} days</span>
                    </div>

                    <!-- ✅ ADD THIS -->
                    <div class="detail-item">
                        <span class="label">Total Quantity:</span>
                        <span class="value"><strong>{{ $item->quantity_prescribed ?? 'N/A' }} units</strong></span>
                    </div>

                    @if($item->unit_price)
                    <div class="detail-item">
                        <span class="label">Unit Price:</span>
                        <span class="value">RM {{ number_format($item->unit_price, 2) }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="label">Total Price:</span>
                        <span class="value"><strong>RM {{ number_format($item->total_price, 2) }}</strong></span>
                    </div>
                    @endif

                    @if($item->instructions)
                    <div class="detail-item full-width">
                        <span class="label">Special Instructions:</span>
                        <span class="value">{{ $item->instructions }}</span>
                    </div>
                    @endif
                </div>

                @if($item->medicine_id && $item->medicine)
                <div class="inventory-info">
                    <span class="label">Available Stock:</span>
                    <span class="stock-badge {{ $item->medicine->quantity_in_stock > $item->quantity_prescribed ? 'stock-good' : 'stock-low' }}">
                        {{ $item->medicine->quantity_in_stock }} units
                    </span>
                    @if($item->medicine->quantity_in_stock < $item->quantity_prescribed)
                        <span class="warning-text">⚠️ Insufficient stock!</span>
                        @endif
                </div>
                @endif
            </div>
            @endforeach

            @if($prescription->notes)
            <div style="margin-top: 15px; padding: 12px; background: #e7f3ff; border-radius: 5px;">
                <strong>Doctor's Notes:</strong> {{ $prescription->notes }}
            </div>
            @endif
        </div>

        <!-- SAFETY CHECKS -->
        @if($dispensing->verification_status == 'Pending')
        <div class="safety-checks">
            <h3>Safety Verification Checklist</h3>

            <form action="{{ route('pharmacist.prescriptions.verify', $prescription->prescription_id) }}" method="POST" id="verifyForm">
                @csrf

                <div class="check-item" id="allergy-check">
                    <input type="checkbox" name="allergy_checked" id="allergy-checkbox" required value="1">
                    <label for="allergy-checkbox">
                        I have reviewed patient's allergy information
                        @if($prescription->patient->activeAllergies->count() > 0)
                        ({{ $prescription->patient->activeAllergies->count() }} active allergies)
                        @else
                        (No allergies recorded)
                        @endif
                    </label>
                </div>

                <div class="check-item" id="interaction-check">
                    <input type="checkbox" name="interaction_checked" id="interaction-checkbox" required value="1">
                    <label for="interaction-checkbox">
                        I have checked for drug-drug interactions
                    </label>
                </div>

                <div class="check-item" id="dosage-check">
                    <input type="checkbox" id="dosage-checkbox" required>
                    <label for="dosage-checkbox">
                        I have verified dosages are appropriate for this patient
                    </label>
                </div>

                @if(count($allergyWarnings) > 0)
                <div class="check-item" id="doctor-contact-check">
                    <input type="checkbox" name="doctor_contacted" id="doctor-checkbox" required value="1">
                    <label for="doctor-checkbox">
                        <strong style="color: #dc3545;">
                            I have contacted the prescribing doctor regarding allergy concerns
                        </strong>
                    </label>
                </div>
                @endif

                <div style="margin-top: 20px;">
                    <label for="verification_notes"><strong>Verification Notes:</strong></label>
                    <textarea name="verification_notes" id="verification_notes" rows="4"
                        style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd; font-family: inherit;"
                        placeholder="Enter any notes, concerns, or actions taken..."></textarea>
                </div>

                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-verify" id="verify-btn" disabled>
                        ✓ Verify & Approve
                    </button>
                    <button type="button" class="btn btn-reject" onclick="openRejectModal()">
                        ✗ Reject Prescription
                    </button>
                    <a href="{{ route('pharmacist.prescriptions') }}" class="btn" style="background: #6c757d; color: white; text-decoration: none; display: inline-flex; align-items: center;">
                        ← Back to List
                    </a>
                </div>
            </form>
        </div>
        @else
        <div class="safety-checks">
            <h3>Verification Status</h3>
            <p style="padding: 15px; background: #d4edda; border-radius: 5px; color: #155724;">
                <strong>✓ This prescription has been {{ strtolower($dispensing->verification_status) }}</strong>
                @if($dispensing->verified_at)
                <br>Verified on: {{ $dispensing->verified_at->format('M d, Y h:i A') }}
                @endif
            </p>
            <div style="margin-top: 20px;">
                <a href="{{ route('pharmacist.prescriptions') }}" class="btn" style="background: #007bff; color: white; text-decoration: none; display: inline-flex; align-items: center;">
                    ← Back to List
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Loading Indicator -->
    <div class="loading" id="loadingIndicator">
        <p>Processing verification...</p>
    </div>

    <script>
        // Enable/disable verify button based on checkboxes
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        const verifyBtn = document.getElementById('verify-btn');

        if (verifyBtn) {
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);

                    // Visual feedback
                    checkbox.parentElement.classList.toggle('checked', checkbox.checked);

                    // Enable verify button if all checks done
                    verifyBtn.disabled = !allChecked;
                });
            });

            // Show loading indicator on form submit
            document.getElementById('verifyForm').addEventListener('submit', function() {
                document.getElementById('loadingIndicator').classList.add('active');
                verifyBtn.disabled = true;
                verifyBtn.textContent = 'Processing...';
            });
        }

        function openRejectModal() {
            const reason = prompt("Please enter reason for rejection (minimum 10 characters):");
            if (reason && reason.length >= 10) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("pharmacist.prescriptions.reject", $prescription->prescription_id) }}';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';

                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'rejection_reason';
                reasonInput.value = reason;

                form.appendChild(csrf);
                form.appendChild(reasonInput);
                document.body.appendChild(form);

                document.getElementById('loadingIndicator').classList.add('active');
                form.submit();
            } else if (reason !== null) {
                alert('Rejection reason must be at least 10 characters long.');
            }
        }

        // Auto-hide success messages after 5 seconds
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