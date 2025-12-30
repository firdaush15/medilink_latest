<!--pharmacist_prescriptions.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Prescription Verification - MediLink</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css', 'resources/css/pharmacist/pharmacist_prescriptions.css'])
</head>

<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')

    <div class="main-content">
        <div class="top-bar">
            <h1>💊 Prescription Verification & Dispensing</h1>
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
            <strong>✗</strong> Validation errors:
            <ul style="margin: 5px 0 0 20px;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="tab-btn {{ request('status') == 'pending' || !request('status') ? 'active' : '' }}"
                onclick="window.location.href='{{ route('pharmacist.prescriptions') }}?status=pending'">
                ⏳ Pending Verification <span class="badge">{{ $pendingCount }}</span>
            </button>
            <button class="tab-btn {{ request('status') == 'verified' ? 'active' : '' }}"
                onclick="window.location.href='{{ route('pharmacist.prescriptions') }}?status=verified'">
                ✅ Ready to Dispense <span class="badge">{{ $verifiedCount }}</span>
            </button>
            <button class="tab-btn {{ request('status') == 'dispensed' ? 'active' : '' }}"
                onclick="window.location.href='{{ route('pharmacist.prescriptions') }}?status=dispensed'">
                ✔️ Dispensed <span class="badge">{{ $dispensedCount }}</span>
            </button>
            <button class="tab-btn {{ request('status') == 'rejected' ? 'active' : '' }}"
                onclick="window.location.href='{{ route('pharmacist.prescriptions') }}?status=rejected'">
                ❌ Rejected
            </button>
        </div>

        <!-- Search and Filter -->
        <div class="search-filter-bar">
            <div class="search-box">
                <input type="text" placeholder="Search by patient name, prescription ID..." id="searchInput">
                <button class="search-btn">🔍 Search</button>
            </div>
            <div class="filter-options">
                <select id="dateFilter" onchange="applyDateFilter(this.value)">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="all">All Time</option>
                </select>
                <select id="doctorFilter" onchange="applyDoctorFilter(this.value)">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doctor)
                    <option value="{{ $doctor->doctor_id }}">Dr. {{ $doctor->user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Prescriptions List -->
        <div class="prescriptions-container">
            @forelse($prescriptions as $dispensing)
            <div class="prescription-card" data-prescription-id="{{ $dispensing->prescription_id }}">
                <div class="prescription-header">
                    <div class="prescription-id">
                        <span class="label">Prescription</span>
                        <span class="id">#{{ str_pad($dispensing->prescription_id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="status-badge {{ strtolower($dispensing->verification_status) }}">
                        {{ $dispensing->verification_status }}
                    </div>
                </div>

                <div class="prescription-body">
                    <div class="patient-info">
                        <div class="info-row">
                            <span class="icon">👤</span>
                            <div>
                                <strong>{{ $dispensing->patient->user->name }}</strong>
                                <small>{{ $dispensing->patient->age }} yrs, {{ $dispensing->patient->gender }}</small>
                            </div>
                        </div>
                        <div class="info-row">
                            <span class="icon">👨‍⚕️</span>
                            <div>
                                <strong>Dr. {{ $dispensing->prescription->doctor->user->name }}</strong>
                                <small>{{ $dispensing->prescription->doctor->specialization }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="prescription-details">
                        <div class="detail-item">
                            <span class="label">Prescribed Date:</span>
                            <span class="value">{{ $dispensing->prescription->prescribed_date->format('M d, Y') }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Submitted:</span>
                            <span class="value">{{ $dispensing->created_at->diffForHumans() }}</span>
                        </div>
                        @if($dispensing->verification_status == 'Verified' && $dispensing->verified_at)
                        <div class="detail-item">
                            <span class="label">Verified At:</span>
                            <span class="value">{{ $dispensing->verified_at->format('M d, Y h:i A') }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="medications-preview">
                        <strong>💊 Medications ({{ $dispensing->prescription->items->count() }}):</strong>
                        <ul>
                            @foreach($dispensing->prescription->items->take(3) as $item)
                            <li>
                                {{ $item->medicine_name }} - {{ $item->dosage }}
                                @if($item->quantity_prescribed)
                                <strong>({{ $item->quantity_prescribed }} units total)</strong>
                                @else
                                ({{ $item->frequency }})
                                @endif
                            </li>
                            @endforeach
                            @if($dispensing->prescription->items->count() > 3)
                            <li class="more-items">+ {{ $dispensing->prescription->items->count() - 3 }} more...</li>
                            @endif
                        </ul>
                    </div>

                    @if($dispensing->prescription->notes)
                    <div class="doctor-notes">
                        <strong>📝 Doctor's Notes:</strong>
                        <p>{{ $dispensing->prescription->notes }}</p>
                    </div>
                    @endif

                    <!-- Safety Checks -->
                    <div class="safety-checks">
                        <div class="check-item {{ $dispensing->allergy_checked ? 'checked' : '' }}">
                            <input type="checkbox" {{ $dispensing->allergy_checked ? 'checked' : '' }} disabled>
                            <span>Allergy Checked</span>
                        </div>
                        <div class="check-item {{ $dispensing->interaction_checked ? 'checked' : '' }}">
                            <input type="checkbox" {{ $dispensing->interaction_checked ? 'checked' : '' }} disabled>
                            <span>Drug Interaction Checked</span>
                        </div>
                        @if($dispensing->verification_status == 'Verified' || $dispensing->verification_status == 'Dispensed')
                        <div class="check-item {{ $dispensing->patient_counseled ? 'checked' : '' }}">
                            <input type="checkbox" {{ $dispensing->patient_counseled ? 'checked' : '' }} disabled>
                            <span>Patient Counseled</span>
                        </div>
                        @endif
                    </div>

                    @if($dispensing->interaction_warnings)
                    <div class="warning-box">
                        <strong>⚠️ Interaction Warnings:</strong>
                        <p>{{ $dispensing->interaction_warnings }}</p>
                    </div>
                    @endif
                </div>

                <div class="prescription-footer">
                    <div class="action-buttons">
                        <a href="{{ route('pharmacist.prescriptions.show', $dispensing->prescription_id) }}" class="btn btn-view">
                            👁️ View Details
                        </a>

                        @if($dispensing->verification_status == 'Pending')
                        <button onclick="openVerifyModal({{ $dispensing->prescription_id }})" class="btn btn-verify">
                            ✓ Verify
                        </button>
                        <button onclick="openRejectModal({{ $dispensing->prescription_id }})" class="btn btn-reject">
                            ✗ Reject
                        </button>
                        @endif

                        @if($dispensing->verification_status == 'Verified')
                        <button onclick="openDispenseModal({{ $dispensing->prescription_id }}, '{{ $dispensing->patient->user->name }}', {{ $dispensing->prescription->items->count() }})" class="btn btn-dispense">
                            💊 Dispense
                        </button>
                        @endif

                        @if($dispensing->verification_status == 'Dispensed')
                        <a href="{{ route('pharmacist.prescriptions.show', $dispensing->prescription_id) }}" class="btn btn-receipt">
                            📄 View Receipt
                        </a>
                        @endif
                    </div>

                    @if($dispensing->verification_status == 'Dispensed')
                    <div class="dispensed-info">
                        <span>Dispensed by: {{ auth()->user()->name }}</span>
                        <span>{{ $dispensing->dispensed_at->format('M d, Y h:i A') }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>No Prescriptions Found</h3>
                <p>There are no prescriptions in this category at the moment.</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($prescriptions->hasPages())
        <div class="pagination">
            {{ $prescriptions->links() }}
        </div>
        @endif
    </div>

    <!-- Verify Modal -->
    <div id="verifyModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('verifyModal')">&times;</span>
            <h2>✅ Verify Prescription</h2>
            <form id="verifyForm" method="POST" action="">
                @csrf
                <input type="hidden" id="verify_prescription_id" name="prescription_id">

                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="allergy_checked" id="allergy_checked" required value="1">
                        <span class="checkmark"></span>
                        <span class="label-text">I have checked patient allergies</span>
                    </label>
                </div>

                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="interaction_checked" id="interaction_checked" required value="1">
                        <span class="checkmark"></span>
                        <span class="label-text">I have checked for drug interactions</span>
                    </label>
                </div>

                <div class="form-group">
                    <label>⚠️ Interaction Warnings (if any):</label>
                    <textarea name="interaction_warnings" id="interaction_warnings" rows="3" placeholder="Enter any drug interaction warnings..."></textarea>
                </div>

                <div class="form-group">
                    <label>📝 Verification Notes:</label>
                    <textarea name="verification_notes" id="verification_notes" rows="3" placeholder="Any additional notes..."></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeModal('verifyModal')">Cancel</button>
                    <button type="submit" class="btn btn-verify" id="verifySubmitBtn">
                        <span class="btn-text">✓ Verify Prescription</span>
                        <span class="btn-loader" style="display: none;">
                            <span class="spinner"></span> Verifying...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('rejectModal')">&times;</span>
            <h2>❌ Reject Prescription</h2>
            <form id="rejectForm" method="POST" action="">
                @csrf
                <input type="hidden" id="reject_prescription_id" name="prescription_id">

                <div class="form-group">
                    <label>Rejection Reason: <span class="required">*</span></label>
                    <textarea name="rejection_reason" id="rejection_reason" rows="5"
                        placeholder="Please provide a detailed reason for rejection (minimum 10 characters)..."
                        required></textarea>
                    <small class="char-count">0 / 10 characters minimum</small>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeModal('rejectModal')">Cancel</button>
                    <button type="submit" class="btn btn-reject" id="rejectSubmitBtn">
                        ✗ Reject Prescription
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Dispense Modal -->
    <div id="dispenseModal" class="modal">
        <div class="modal-content modal-large">
            <span class="close" onclick="closeModal('dispenseModal')">&times;</span>
            <h2>💊 Dispense Prescription</h2>

            <div class="dispense-info">
                <p><strong>Patient:</strong> <span id="dispense_patient_name"></span></p>
                <p><strong>Prescription ID:</strong> #<span id="dispense_prescription_id_display"></span></p>
                <p><strong>Total Medications:</strong> <span id="dispense_medication_count"></span></p>
            </div>

            <form id="dispenseForm" method="POST" action="">
                @csrf
                <input type="hidden" id="dispense_prescription_id" name="prescription_id">

                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="patient_counseled" id="patient_counseled" required value="1">
                        <span class="checkmark"></span>
                        <span class="label-text">✅ I have counseled the patient on medication usage <span class="required">*</span></span>
                    </label>
                </div>

                <div class="form-group">
                    <label>📝 Counseling Notes: <span class="required">*</span></label>
                    <textarea name="counseling_notes" id="counseling_notes" rows="5"
                        placeholder="Document what was discussed with the patient:
• Dosage instructions (when and how to take)
• Potential side effects to watch for
• Storage requirements
• Duration of treatment
• Food/drug interactions
• What to do if a dose is missed" required></textarea>
                    <small>Record important points discussed during patient counseling</small>
                </div>

                <div class="form-group">
                    <label>🔔 Special Instructions for Patient:</label>
                    <textarea name="special_instructions" id="special_instructions" rows="3"
                        placeholder="Any special instructions (e.g., 'Take with food', 'Avoid alcohol', 'Complete full course', etc.)..."></textarea>
                </div>

                <div class="info-box">
                    <strong>ℹ️ Dispensing Checklist:</strong> Please ensure:
                    <ul>
                        <li>✅ All medications are correctly labeled with patient name</li>
                        <li>✅ Patient understands dosage instructions</li>
                        <li>✅ Patient is aware of potential side effects</li>
                        <li>✅ Storage instructions provided</li>
                        <li>✅ Patient knows when to return for follow-up</li>
                        <li>💳 <strong>Payment will be collected by receptionist at checkout</strong></li>
                    </ul>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeModal('dispenseModal')">Cancel</button>
                    <button type="submit" class="btn btn-dispense" id="dispenseSubmitBtn">
                        <span class="btn-text">💊 Complete Dispensing</span>
                        <span class="btn-loader" style="display: none;">
                            <span class="spinner"></span> Processing...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Processing...</p>
        </div>
    </div>

    <script>
        // ========================================
        // DISPENSE MODAL
        // ========================================
        function openDispenseModal(prescriptionId, patientName, medicationCount) {
            document.getElementById('dispense_prescription_id').value = prescriptionId;
            document.getElementById('dispense_prescription_id_display').textContent = String(prescriptionId).padStart(5, '0');
            document.getElementById('dispense_patient_name').textContent = patientName;
            document.getElementById('dispense_medication_count').textContent = medicationCount;

            const form = document.getElementById('dispenseForm');
            // 🔴 FIX: Use Laravel url() helper
            form.action = "{{ url('pharmacist/prescriptions') }}/" + prescriptionId + "/dispense";

            form.reset();
            document.getElementById('dispense_prescription_id').value = prescriptionId;

            document.getElementById('dispenseModal').style.display = 'block';
        }

        // Handle dispense form submission via AJAX
        document.getElementById('dispenseForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('dispenseSubmitBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoader = submitBtn.querySelector('.btn-loader');

            // Validate patient counseled checkbox
            const patientCounseled = document.getElementById('patient_counseled').checked;
            if (!patientCounseled) {
                alert('⚠️ Please confirm that you have counseled the patient before dispensing.');
                return;
            }

            // Validate counseling notes
            const counselingNotes = document.getElementById('counseling_notes').value.trim();
            if (!counselingNotes || counselingNotes.length < 20) {
                alert('⚠️ Please provide detailed counseling notes (minimum 20 characters).');
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline-flex';

            const formData = new FormData(this);
            const prescriptionId = document.getElementById('dispense_prescription_id').value;

            // 🔴 FIX: Use Laravel's url() helper to get correct base path
            const dispenseUrl = "{{ url('pharmacist/prescriptions') }}/" + prescriptionId + "/dispense";

            // Send AJAX request
            fetch(dispenseUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', data.message);
                        closeModal('dispenseModal');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    } else {
                        throw new Error(data.message || 'Failed to dispense prescription');
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    btnText.style.display = 'inline';
                    btnLoader.style.display = 'none';
                    showNotification('error', error.message);
                });
        });

        // ========================================
        // VERIFY MODAL
        // ========================================
        function openVerifyModal(prescriptionId) {
            document.getElementById('verify_prescription_id').value = prescriptionId;

            const form = document.getElementById('verifyForm');
            // 🔴 FIX: Use Laravel url() helper
            form.action = "{{ url('pharmacist/prescriptions') }}/" + prescriptionId + "/verify";

            form.reset();
            document.getElementById('verify_prescription_id').value = prescriptionId;

            document.getElementById('verifyModal').style.display = 'block';
        }

        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('verifySubmitBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoader = submitBtn.querySelector('.btn-loader');

            const allergyChecked = document.getElementById('allergy_checked').checked;
            const interactionChecked = document.getElementById('interaction_checked').checked;

            if (!allergyChecked || !interactionChecked) {
                alert('⚠️ Please check both allergy and interaction checkboxes before verifying.');
                return;
            }

            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline-flex';

            this.submit();
        });

        // ========================================
        // REJECT MODAL
        // ========================================
        function openRejectModal(prescriptionId) {
            document.getElementById('reject_prescription_id').value = prescriptionId;

            const form = document.getElementById('rejectForm');
            // 🔴 FIX: Use Laravel url() helper
            form.action = "{{ url('pharmacist/prescriptions') }}/" + prescriptionId + "/reject";

            form.reset();
            document.getElementById('reject_prescription_id').value = prescriptionId;
            updateCharCount();

            document.getElementById('rejectModal').style.display = 'block';
        }

        const rejectionTextarea = document.getElementById('rejection_reason');
        const charCount = document.querySelector('.char-count');

        if (rejectionTextarea) {
            rejectionTextarea.addEventListener('input', updateCharCount);
        }

        function updateCharCount() {
            const length = rejectionTextarea.value.length;
            charCount.textContent = `${length} / 10 characters minimum`;
            charCount.style.color = length >= 10 ? '#2e7d32' : '#f57c00';
        }

        document.getElementById('rejectForm').addEventListener('submit', function(e) {
            const reason = document.getElementById('rejection_reason').value;

            if (reason.length < 10) {
                e.preventDefault();
                alert('⚠️ Rejection reason must be at least 10 characters long.');
                return;
            }

            const submitBtn = document.getElementById('rejectSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Rejecting...';
        });

        // ========================================
        // MODAL UTILITIES
        // ========================================
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';

            if (modalId === 'verifyModal') {
                document.getElementById('verifyForm').reset();
                const submitBtn = document.getElementById('verifySubmitBtn');
                const btnText = submitBtn.querySelector('.btn-text');
                const btnLoader = submitBtn.querySelector('.btn-loader');
                submitBtn.disabled = false;
                btnText.style.display = 'inline';
                btnLoader.style.display = 'none';
            } else if (modalId === 'rejectModal') {
                document.getElementById('rejectForm').reset();
                updateCharCount();
            } else if (modalId === 'dispenseModal') {
                document.getElementById('dispenseForm').reset();
                const submitBtn = document.getElementById('dispenseSubmitBtn');
                const btnText = submitBtn.querySelector('.btn-text');
                const btnLoader = submitBtn.querySelector('.btn-loader');
                submitBtn.disabled = false;
                btnText.style.display = 'inline';
                btnLoader.style.display = 'none';
            }
        }

        window.onclick = function(event) {
            const verifyModal = document.getElementById('verifyModal');
            const rejectModal = document.getElementById('rejectModal');
            const dispenseModal = document.getElementById('dispenseModal');

            if (event.target == verifyModal) {
                closeModal('verifyModal');
            } else if (event.target == rejectModal) {
                closeModal('rejectModal');
            } else if (event.target == dispenseModal) {
                closeModal('dispenseModal');
            }
        }

        // ========================================
        // NOTIFICATION SYSTEM
        // ========================================
        function showNotification(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `<strong>${type === 'success' ? '✓' : '✗'}</strong> ${message}`;

            const mainContent = document.querySelector('.main-content');
            const topBar = document.querySelector('.top-bar');
            mainContent.insertBefore(alertDiv, topBar.nextSibling);

            setTimeout(() => {
                alertDiv.style.transition = 'opacity 0.5s ease';
                alertDiv.style.opacity = '0';
                setTimeout(() => alertDiv.remove(), 500);
            }, 5000);
        }

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

        // ========================================
        // FILTERS
        // ========================================
        function applyDateFilter(value) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('date', value);
            window.location.href = currentUrl.toString();
        }

        function applyDoctorFilter(value) {
            const currentUrl = new URL(window.location.href);
            if (value) {
                currentUrl.searchParams.set('doctor_id', value);
            } else {
                currentUrl.searchParams.delete('doctor_id');
            }
            window.location.href = currentUrl.toString();
        }
    </script>
</body>

</html>