<!--receptionist_walkIn.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Walk-In Patient Registration - MediLink</title>
    @vite(['resources/css/receptionist/receptionist_sidebar.css', 'resources/css/receptionist/receptionist_appointmentCreate.css'])
    <style>
        .urgency-selector {
            display: flex;
            gap: 15px;
            margin: 20px 0;
        }
        .urgency-card {
            flex: 1;
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
        }
        .urgency-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .urgency-card.selected {
            border-color: #2196F3;
            background: #e3f2fd;
        }
        .urgency-card.routine { border-color: #4CAF50; }
        .urgency-card.routine.selected { background: #e8f5e9; border-color: #4CAF50; }
        .urgency-card.urgent { border-color: #ff9800; }
        .urgency-card.urgent.selected { background: #fff3e0; border-color: #ff9800; }
        .urgency-card.emergency { border-color: #f44336; }
        .urgency-card.emergency.selected { background: #ffebee; border-color: #f44336; }
        .urgency-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .walk-in-badge {
            background: #ff9800;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @include('receptionist.sidebar.receptionist_sidebar')

    <div class="main-content">
        <div class="header">
            <div>
                <h1>🚨 Walk-In Patient Registration</h1>
                <p>Register and assign priority for walk-in patients</p>
                <span class="walk-in-badge">WALK-IN MODE</span>
            </div>
            <a href="{{ route('receptionist.check-in') }}" class="btn-back">← Back to Check-In</a>
        </div>

        @if(session('success'))
        <div class="alert alert-success">
            <span class="icon">✓</span>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-error">
            <span class="icon">⚠</span>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('receptionist.walk-in.store') }}" class="appointment-form">
            @csrf
            <input type="hidden" name="is_walk_in" value="1">

            <!-- Step 1: Urgency Level Selection -->
            <div class="form-section">
                <h2 class="section-title">Step 1: Assess Urgency Level</h2>
                <p style="color: #666; margin-bottom: 15px;">Select the appropriate urgency level based on patient's condition</p>
                
                <div class="urgency-selector">
                    <div class="urgency-card routine" onclick="selectUrgency('routine')">
                        <div class="urgency-icon">🟢</div>
                        <h3>Routine</h3>
                        <p>General consultation</p>
                        <small>Non-urgent medical issues</small>
                    </div>
                    
                    <div class="urgency-card urgent" onclick="selectUrgency('urgent')">
                        <div class="urgency-icon">🟡</div>
                        <h3>Urgent</h3>
                        <p>Needs prompt attention</p>
                        <small>Moderate pain, fever</small>
                    </div>
                    
                    <div class="urgency-card emergency" onclick="selectUrgency('emergency')">
                        <div class="urgency-icon">🔴</div>
                        <h3>Emergency</h3>
                        <p>Immediate care required</p>
                        <small>Severe pain, breathing issues</small>
                    </div>
                </div>
                
                <input type="hidden" name="urgency_level" id="urgency_level" required>
                @error('urgency_level')
                    <span class="error-msg">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-container">
                <!-- Left Column -->
                <div class="form-column">
                    <div class="form-section">
                        <h2 class="section-title">Step 2: Patient Information</h2>
                        
                        <div class="form-group">
                            <label for="patient_type">Patient Type <span class="required">*</span></label>
                            <select id="patient_type" required onchange="togglePatientFields()">
                                <option value="">-- Select --</option>
                                <option value="existing">Existing Patient</option>
                                <option value="new">New Patient (Quick Registration)</option>
                            </select>
                        </div>

                        <!-- Existing Patient Search -->
                        <div id="existing-patient-section" style="display: none;">
                            <div class="form-group">
                                <label for="patient_id">Search Patient <span class="required">*</span></label>
                                <div class="search-wrapper">
                                    <input type="text" id="patient-search" placeholder="Search by name, ID, or phone..." autocomplete="off">
                                    <div id="patient-results" class="search-results"></div>
                                </div>
                                <select name="patient_id" id="patient_id" style="display: none;">
                                    <option value="">-- Select Patient --</option>
                                    @foreach($patients as $patient)
                                    <option value="{{ $patient->patient_id }}" 
                                        data-name="{{ $patient->user->name }}"
                                        data-email="{{ $patient->user->email }}"
                                        data-phone="{{ $patient->phone_number }}">
                                        {{ $patient->user->name }} - P{{ str_pad($patient->patient_id, 4, '0', STR_PAD_LEFT) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="selected-patient-info" class="patient-info-display" style="display: none;">
                                <div class="info-card">
                                    <h4>Selected Patient</h4>
                                    <div class="info-row">
                                        <span class="label">Name:</span>
                                        <span id="display-name"></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="label">Phone:</span>
                                        <span id="display-phone"></span>
                                    </div>
                                    <button type="button" class="btn-change" onclick="clearPatientSelection()">Change Patient</button>
                                </div>
                            </div>
                        </div>

                        <!-- New Patient Quick Registration -->
                        <div id="new-patient-section" style="display: none;">
                            <p style="background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;">
                                ℹ️ Quick registration for walk-in. Complete details can be added later.
                            </p>
                            
                            <div class="form-group">
                                <label for="new_patient_name">Full Name <span class="required">*</span></label>
                                <input type="text" id="new_patient_name" name="new_patient_name">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_patient_phone">Phone Number <span class="required">*</span></label>
                                    <input type="tel" id="new_patient_phone" name="new_patient_phone" placeholder="+60123456789">
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_patient_ic">IC Number</label>
                                    <input type="text" id="new_patient_ic" name="new_patient_ic" placeholder="990101-01-1234">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="form-column">
                    <div class="form-section">
                        <h2 class="section-title">Step 3: Doctor Assignment</h2>
                        
                        <div class="form-group">
                            <label for="doctor_id">Available Doctor <span class="required">*</span></label>
                            <select name="doctor_id" id="doctor_id" required>
                                <option value="">-- Select Doctor --</option>
                                @foreach($doctors as $doctor)
                                <option value="{{ $doctor->doctor_id }}">
                                    Dr. {{ $doctor->user->name }} - {{ $doctor->specialization }}
                                    ({{ $doctor->appointments()->whereDate('appointment_date', today())->whereIn('status', ['confirmed', 'checked_in'])->count() }} patients today)
                                </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <span class="error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reason">Chief Complaint / Symptoms <span class="required">*</span></label>
                            <textarea name="reason" id="reason" rows="4" required placeholder="Describe patient's main symptoms..."></textarea>
                            @error('reason')
                                <span class="error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="walk_in_notes">Additional Notes</label>
                            <textarea name="walk_in_notes" id="walk_in_notes" rows="3" placeholder="Any special instructions or observations..."></textarea>
                        </div>
                    </div>

                    <!-- Summary Section -->
                    <div class="form-section summary-section">
                        <h2 class="section-title">Registration Summary</h2>
                        <div class="summary-content">
                            <div class="summary-item">
                                <span class="summary-label">Type:</span>
                                <span class="summary-value">🚨 Walk-In Patient</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Urgency:</span>
                                <span class="summary-value" id="summary-urgency">Not selected</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Patient:</span>
                                <span class="summary-value" id="summary-patient">Not selected</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Doctor:</span>
                                <span class="summary-value" id="summary-doctor">Not selected</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('receptionist.check-in') }}" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn btn-primary">🚨 Register Walk-In & Assign Queue</button>
            </div>
        </form>
    </div>

    <script>
        // Urgency selection
        function selectUrgency(level) {
            document.querySelectorAll('.urgency-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.target.closest('.urgency-card').classList.add('selected');
            document.getElementById('urgency_level').value = level;
            
            const labels = {
                'routine': '🟢 Routine',
                'urgent': '🟡 Urgent',
                'emergency': '🔴 Emergency'
            };
            document.getElementById('summary-urgency').textContent = labels[level];
        }

        // Patient type toggle
        function togglePatientFields() {
            const type = document.getElementById('patient_type').value;
            document.getElementById('existing-patient-section').style.display = type === 'existing' ? 'block' : 'none';
            document.getElementById('new-patient-section').style.display = type === 'new' ? 'block' : 'none';
            
            // Toggle required fields
            if (type === 'existing') {
                document.getElementById('patient_id').required = true;
                document.getElementById('new_patient_name').required = false;
                document.getElementById('new_patient_phone').required = false;
            } else if (type === 'new') {
                document.getElementById('patient_id').required = false;
                document.getElementById('new_patient_name').required = true;
                document.getElementById('new_patient_phone').required = true;
            }
        }

        // Patient search
        const patientSearch = document.getElementById('patient-search');
        const patientResults = document.getElementById('patient-results');
        const patientSelect = document.getElementById('patient_id');

        patientSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            
            if (searchTerm.length < 2) {
                patientResults.innerHTML = '';
                patientResults.style.display = 'none';
                return;
            }

            const options = Array.from(patientSelect.options).filter(option => {
                return option.value && option.textContent.toLowerCase().includes(searchTerm);
            });

            if (options.length > 0) {
                patientResults.innerHTML = options.map(option => `
                    <div class="result-item" onclick="selectPatient(${option.value})">
                        <strong>${option.dataset.name}</strong>
                        <small>${option.dataset.phone}</small>
                    </div>
                `).join('');
                patientResults.style.display = 'block';
            } else {
                patientResults.innerHTML = '<div class="no-results">No patients found</div>';
                patientResults.style.display = 'block';
            }
        });

        function selectPatient(patientId) {
            patientSelect.value = patientId;
            const option = patientSelect.options[patientSelect.selectedIndex];
            
            document.getElementById('display-name').textContent = option.dataset.name;
            document.getElementById('display-phone').textContent = option.dataset.phone;
            
            patientSearch.value = option.dataset.name;
            patientResults.style.display = 'none';
            document.getElementById('selected-patient-info').style.display = 'block';
            
            document.getElementById('summary-patient').textContent = option.dataset.name;
        }

        function clearPatientSelection() {
            patientSelect.value = '';
            patientSearch.value = '';
            document.getElementById('selected-patient-info').style.display = 'none';
            document.getElementById('summary-patient').textContent = 'Not selected';
        }

        // Doctor selection
        document.getElementById('doctor_id').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                document.getElementById('summary-doctor').textContent = option.textContent.split('(')[0].trim();
            }
        });

        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!patientSearch.contains(e.target) && !patientResults.contains(e.target)) {
                patientResults.style.display = 'none';
            }
        });
    </script>
</body>
</html>