<!--receptionist_appointmentCreate.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - MediLink</title>
    @vite(['resources/css/receptionist/receptionist_sidebar.css', 'resources/css/receptionist/receptionist_appointmentCreate.css'])
</head>
<body>
    @include('receptionist.sidebar.receptionist_sidebar')

    <div class="main-content">
        <div class="header">
            <div>
                <h1>Book New Appointment</h1>
                <p>Schedule an appointment for a patient</p>
            </div>
            <a href="{{ route('receptionist.appointments') }}" class="btn-back">← Back to Appointments</a>
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

        <form method="POST" action="{{ route('receptionist.appointments.store') }}" class="appointment-form">
            @csrf

            <div class="form-container">
                <!-- Left Column -->
                <div class="form-column">
                    <div class="form-section">
                        <h2 class="section-title">Patient Information</h2>
                        
                        <div class="form-group">
                            <label for="patient_id">Select Patient <span class="required">*</span></label>
                            <div class="search-wrapper">
                                <input type="text" id="patient-search" placeholder="Search by name, email, or patient ID..." autocomplete="off">
                                <div id="patient-results" class="search-results"></div>
                            </div>
                            <select name="patient_id" id="patient_id" required style="display: none;">
                                <option value="">-- Select Patient --</option>
                                @foreach($patients as $patient)
                                <option value="{{ $patient->patient_id }}" 
                                    {{ (old('patient_id') == $patient->patient_id || (isset($selectedPatient) && $selectedPatient->patient_id == $patient->patient_id)) ? 'selected' : '' }}
                                    data-name="{{ $patient->user->name }}"
                                    data-email="{{ $patient->user->email }}"
                                    data-phone="{{ $patient->phone_number }}"
                                    data-age="{{ $patient->age }}"
                                    data-gender="{{ $patient->gender }}">
                                    {{ $patient->user->name }} - P{{ str_pad($patient->patient_id, 4, '0', STR_PAD_LEFT) }}
                                </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <span class="error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Selected Patient Display -->
                        <div id="selected-patient-info" class="patient-info-display" style="display: none;">
                            <div class="info-card">
                                <h4>Selected Patient</h4>
                                <div class="info-row">
                                    <span class="label">Name:</span>
                                    <span id="display-name"></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Email:</span>
                                    <span id="display-email"></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Phone:</span>
                                    <span id="display-phone"></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Age:</span>
                                    <span id="display-age"></span>
                                </div>
                                <div class="info-row">
                                    <span class="label">Gender:</span>
                                    <span id="display-gender"></span>
                                </div>
                                <button type="button" class="btn-change" onclick="clearPatientSelection()">Change Patient</button>
                            </div>
                        </div>

                        <div class="quick-register">
                            <p>Patient not registered yet?</p>
                            <a href="{{ route('receptionist.patients.register') }}" class="btn-register">➕ Register New Patient</a>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="form-column">
                    <div class="form-section">
                        <h2 class="section-title">Appointment Details</h2>
                        
                        <div class="form-group">
                            <label for="doctor_id">Select Doctor <span class="required">*</span></label>
                            <select name="doctor_id" id="doctor_id" required onchange="loadAvailableSlots()">
                                <option value="">-- Select Doctor --</option>
                                @foreach($doctors as $doctor)
                                <option value="{{ $doctor->doctor_id }}" {{ old('doctor_id') == $doctor->doctor_id ? 'selected' : '' }}>
                                    Dr. {{ $doctor->user->name }} - {{ $doctor->specialization }}
                                </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <span class="error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="appointment_date">Appointment Date <span class="required">*</span></label>
                                <input type="date" name="appointment_date" id="appointment_date" 
                                    value="{{ old('appointment_date') }}" 
                                    min="{{ date('Y-m-d') }}" 
                                    required 
                                    onchange="loadAvailableSlots()">
                                @error('appointment_date')
                                    <span class="error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="appointment_time">Appointment Time <span class="required">*</span></label>
                                <select name="appointment_time" id="appointment_time" required>
                                    <option value="">-- Select Time --</option>
                                </select>
                                <small class="field-hint">Available time slots will load after selecting doctor and date</small>
                                @error('appointment_time')
                                    <span class="error-msg">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reason">Reason for Visit</label>
                            <textarea name="reason" id="reason" rows="4" placeholder="Brief description of the reason for visit...">{{ old('reason') }}</textarea>
                            <small class="field-hint">Optional - helps the doctor prepare for the consultation</small>
                            @error('reason')
                                <span class="error-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Booking Summary -->
                    <div class="form-section summary-section">
                        <h2 class="section-title">Booking Summary</h2>
                        <div class="summary-content">
                            <div class="summary-item">
                                <span class="summary-label">Patient:</span>
                                <span class="summary-value" id="summary-patient">Not selected</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Doctor:</span>
                                <span class="summary-value" id="summary-doctor">Not selected</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Date:</span>
                                <span class="summary-value" id="summary-date">Not selected</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Time:</span>
                                <span class="summary-value" id="summary-time">Not selected</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('receptionist.appointments') }}" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn btn-primary">📅 Book Appointment</button>
            </div>
        </form>
    </div>

    <script>
        // Patient search functionality
        const patientSearch = document.getElementById('patient-search');
        const patientResults = document.getElementById('patient-results');
        const patientSelect = document.getElementById('patient_id');
        const patientInfoDisplay = document.getElementById('selected-patient-info');

        patientSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            
            if (searchTerm.length < 2) {
                patientResults.innerHTML = '';
                patientResults.style.display = 'none';
                return;
            }

            const options = Array.from(patientSelect.options).filter(option => {
                return option.value && (
                    option.textContent.toLowerCase().includes(searchTerm) ||
                    option.dataset.email?.toLowerCase().includes(searchTerm)
                );
            });

            if (options.length > 0) {
                patientResults.innerHTML = options.map(option => `
                    <div class="result-item" onclick="selectPatient(${option.value})">
                        <strong>${option.dataset.name}</strong>
                        <small>${option.dataset.email} • ${option.dataset.phone}</small>
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
            document.getElementById('display-email').textContent = option.dataset.email;
            document.getElementById('display-phone').textContent = option.dataset.phone;
            document.getElementById('display-age').textContent = option.dataset.age + ' years';
            document.getElementById('display-gender').textContent = option.dataset.gender;
            
            patientSearch.value = option.dataset.name;
            patientResults.style.display = 'none';
            patientInfoDisplay.style.display = 'block';
            
            document.getElementById('summary-patient').textContent = option.dataset.name;
        }

        function clearPatientSelection() {
            patientSelect.value = '';
            patientSearch.value = '';
            patientInfoDisplay.style.display = 'none';
            document.getElementById('summary-patient').textContent = 'Not selected';
        }

        // Doctor selection update
        document.getElementById('doctor_id').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                document.getElementById('summary-doctor').textContent = option.textContent;
            } else {
                document.getElementById('summary-doctor').textContent = 'Not selected';
            }
        });

        // Date selection update
        document.getElementById('appointment_date').addEventListener('change', function() {
            if (this.value) {
                const date = new Date(this.value);
                document.getElementById('summary-date').textContent = date.toLocaleDateString('en-US', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
            } else {
                document.getElementById('summary-date').textContent = 'Not selected';
            }
        });

        // Time selection update
        document.getElementById('appointment_time').addEventListener('change', function() {
            if (this.value) {
                const time = new Date('2000-01-01 ' + this.value);
                document.getElementById('summary-time').textContent = time.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
            } else {
                document.getElementById('summary-time').textContent = 'Not selected';
            }
        });

        // Load available time slots
        function loadAvailableSlots() {
            const doctorId = document.getElementById('doctor_id').value;
            const date = document.getElementById('appointment_date').value;
            const timeSelect = document.getElementById('appointment_time');

            if (!doctorId || !date) {
                timeSelect.innerHTML = '<option value="">-- Select Time --</option>';
                return;
            }

            // Show loading
            timeSelect.innerHTML = '<option value="">Loading available slots...</option>';

            // Generate time slots (9 AM to 5 PM, 30-minute intervals)
            const slots = [];
            for (let hour = 9; hour < 17; hour++) {
                for (let minute = 0; minute < 60; minute += 30) {
                    const timeStr = `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}:00`;
                    const displayTime = new Date(`2000-01-01 ${timeStr}`).toLocaleTimeString('en-US', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                    slots.push({ value: timeStr, display: displayTime });
                }
            }

            timeSelect.innerHTML = '<option value="">-- Select Time --</option>' + 
                slots.map(slot => `<option value="${slot.value}">${slot.display}</option>`).join('');
        }

        // Auto-select patient if provided
        @if(isset($selectedPatient))
        window.addEventListener('DOMContentLoaded', function() {
            selectPatient({{ $selectedPatient->patient_id }});
        });
        @endif

        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!patientSearch.contains(e.target) && !patientResults.contains(e.target)) {
                patientResults.style.display = 'none';
            }
        });
    </script>
</body>
</html>