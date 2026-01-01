<!--receptionist_patientRegistration.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration - MediLink</title>
    @vite(['resources/css/receptionist/receptionist_sidebar.css', 'resources/css/receptionist/receptionist_patientRegistration.css'])
</head>
<body>
    @include('receptionist.sidebar.receptionist_sidebar')

    <div class="main-content">
        <div class="header">
            <h1>New Patient Registration</h1>
            <p>Register a new patient in the system</p>
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

        <form method="POST" action="{{ route('receptionist.patients.store') }}" class="registration-form">
            @csrf

            <div class="form-section">
                <h2 class="section-title">Personal Information</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                        <small class="field-hint">Used for appointment notifications and login</small>
                        @error('email')
                            <span class="error-msg">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone_number">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required placeholder="+60123456789">
                        @error('phone_number')
                            <span class="error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth <span class="required">*</span></label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ date('Y-m-d') }}" required>
                        @error('date_of_birth')
                            <span class="error-msg">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Gender <span class="required">*</span></label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <span class="error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="emergency_contact">Emergency Contact Number</label>
                        <input type="tel" id="emergency_contact" name="emergency_contact" value="{{ old('emergency_contact') }}" placeholder="+60123456789">
                        <small class="field-hint">Person to contact in case of emergency</small>
                        @error('emergency_contact')
                            <span class="error-msg">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3" placeholder="Enter full address">{{ old('address') }}</textarea>
                    @error('address')
                        <span class="error-msg">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-section">
                <h2 class="section-title">Account Setup</h2>
                <p class="section-description">A temporary password will be generated and sent to the patient's email</p>
                
                <div class="password-info">
                    <div class="info-icon">🔐</div>
                    <div class="info-content">
                        <strong>Default Password Policy:</strong>
                        <ul>
                            <li>A secure random password will be auto-generated</li>
                            <li>Password will be sent to patient's email</li>
                            <li>Patient must change password on first login</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2 class="section-title">Data Privacy & Consent</h2>
                
                <div class="consent-box">
                    <label class="checkbox-label">
                        <input type="checkbox" name="data_consent" required>
                        <span>I confirm that I have informed the patient about data collection and they have provided consent for their personal and medical information to be stored in this system in accordance with data protection regulations.</span>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('receptionist.dashboard') }}" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn btn-primary">Register Patient</button>
            </div>
        </form>

        <!-- Recently Registered Patients -->
        @if(isset($recentPatients) && $recentPatients->count() > 0)
        <div class="recent-patients">
            <h2>Recently Registered Patients</h2>
            <table>
                <thead>
                    <tr>
                        <th>Patient ID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Registered On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPatients as $patient)
                    <tr>
                        <td><strong>P{{ str_pad($patient->patient_id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                        <td>{{ $patient->user->name }}</td>
                        <td>{{ $patient->phone_number }}</td>
                        <td>{{ $patient->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('receptionist.appointments.create', ['patient_id' => $patient->patient_id]) }}" class="btn-book">Book Appointment</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

<script>
    /**
     * ============================================
     * SIMPLIFIED PHONE INPUT
     * No auto-formatting - let users type freely
     * Backend will standardize everything
     * ============================================
     */

    document.addEventListener('DOMContentLoaded', function() {
        const phoneInput = document.getElementById('phone_number');
        const emergencyInput = document.getElementById('emergency_contact');

        // Helper function to setup phone input
        function setupPhoneInput(input) {
            if (!input) return;

            // Update placeholder
            input.placeholder = 'Example: 012-345 6789';

            // Add hint text
            const formGroup = input.closest('.form-group');
            if (formGroup) {
                const existingHint = formGroup.querySelector('.phone-format-hint');
                if (!existingHint) {
                    const hint = document.createElement('small');
                    hint.className = 'field-hint phone-format-hint';
                    hint.innerHTML = '💡 Accepts any format: 012..., 60..., or +60...';
                    hint.style.display = 'block';
                    hint.style.marginTop = '4px';
                    hint.style.color = '#6b7280';
                    input.parentNode.insertBefore(hint, input.nextSibling);
                }
            }

            // Only basic cleanup on input - remove letters but keep +, spaces, dashes
            input.addEventListener('input', function(e) {
                let value = e.target.value;
                
                // Remove letters and special chars except +, space, dash, and digits
                value = value.replace(/[^0-9+\s-]/g, '');
                
                // Only allow + at the beginning
                if (value.indexOf('+') > 0) {
                    value = value.replace(/\+/g, '');
                }
                
                e.target.value = value;
            });

            // Optional: Clean up extra spaces on blur
            input.addEventListener('blur', function(e) {
                let value = e.target.value.trim();
                
                // Remove multiple spaces
                value = value.replace(/\s+/g, ' ');
                
                // Remove multiple dashes
                value = value.replace(/-+/g, '-');
                
                e.target.value = value;
            });
        }

        // Setup both inputs
        setupPhoneInput(phoneInput);
        setupPhoneInput(emergencyInput);
    });
</script>
</body>
</html>