<!--receptionist_appointmentDetails.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Details - MediLink</title>
    @vite(['resources/css/receptionist/receptionist_sidebar.css', 'resources/css/receptionist/receptionist_appointmentDetails.css'])
</head>
<body>
    @include('receptionist.sidebar.receptionist_sidebar')

    <div class="main-content">
        <div class="header">
            <div>
                <h1>Appointment Details</h1>
                <p>Appointment ID: #{{ $appointment->appointment_id }}</p>
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
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="details-container">
            <!-- Left Column -->
            <div class="details-column">
                <!-- Appointment Info Card -->
                <div class="info-card">
                    <div class="card-header">
                        <h2>Appointment Information</h2>
                        <span class="status-badge {{ $appointment->status }}">
                            {{ ucfirst($appointment->status) }}
                        </span>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="icon">📅</span>
                            <div>
                                <label>Date</label>
                                <strong>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, F d, Y') }}</strong>
                            </div>
                        </div>

                        <div class="info-item">
                            <span class="icon">🕐</span>
                            <div>
                                <label>Time</label>
                                <strong>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</strong>
                            </div>
                        </div>

                        @if($appointment->reason)
                        <div class="info-item full-width">
                            <span class="icon">📝</span>
                            <div>
                                <label>Reason for Visit</label>
                                <p>{{ $appointment->reason }}</p>
                            </div>
                        </div>
                        @endif

                        @if($appointment->cancelled_reason)
                        <div class="info-item full-width cancelled-reason">
                            <span class="icon">⚠️</span>
                            <div>
                                <label>Cancellation Reason</label>
                                <p>{{ $appointment->cancelled_reason }}</p>
                            </div>
                        </div>
                        @endif

                        <div class="info-item">
                            <span class="icon">📆</span>
                            <div>
                                <label>Booked On</label>
                                <strong>{{ $appointment->created_at->format('M d, Y h:i A') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Patient Info Card -->
                <div class="info-card">
                    <div class="card-header">
                        <h2>Patient Information</h2>
                        <span class="patient-id">P{{ str_pad($appointment->patient->patient_id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    
                    <div class="patient-details">
                        <div class="patient-avatar">
                            @if($appointment->patient->user->profile_photo)
                                <img src="{{ asset('storage/' . $appointment->patient->user->profile_photo) }}" alt="{{ $appointment->patient->user->name }}">
                            @else
                                <div class="avatar-placeholder">{{ substr($appointment->patient->user->name, 0, 1) }}</div>
                            @endif
                        </div>
                        <div class="patient-info">
                            <h3>{{ $appointment->patient->user->name }}</h3>
                            <div class="patient-meta">
                                <span>{{ $appointment->patient->age }} years</span>
                                <span>•</span>
                                <span>{{ $appointment->patient->gender }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="contact-info">
                        <div class="contact-item">
                            <span class="icon">📧</span>
                            <div>
                                <label>Email</label>
                                <a href="mailto:{{ $appointment->patient->user->email }}">{{ $appointment->patient->user->email }}</a>
                            </div>
                        </div>
                        <div class="contact-item">
                            <span class="icon">📞</span>
                            <div>
                                <label>Phone</label>
                                <a href="tel:{{ $appointment->patient->phone_number }}">{{ $appointment->patient->phone_number }}</a>
                            </div>
                        </div>
                        @if($appointment->patient->emergency_contact)
                        <div class="contact-item">
                            <span class="icon">🚨</span>
                            <div>
                                <label>Emergency Contact</label>
                                <a href="tel:{{ $appointment->patient->emergency_contact }}">{{ $appointment->patient->emergency_contact }}</a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Doctor Info Card -->
                <div class="info-card">
                    <div class="card-header">
                        <h2>Doctor Information</h2>
                    </div>
                    
                    <div class="doctor-details">
                        <div class="doctor-avatar">
                            @if($appointment->doctor->profile_photo)
                                <img src="{{ asset('storage/' . $appointment->doctor->profile_photo) }}" alt="{{ $appointment->doctor->user->name }}">
                            @else
                                <div class="avatar-placeholder">{{ substr($appointment->doctor->user->name, 0, 1) }}</div>
                            @endif
                        </div>
                        <div class="doctor-info">
                            <h3>Dr. {{ $appointment->doctor->user->name }}</h3>
                            <p class="specialization">{{ $appointment->doctor->specialization }}</p>
                            <span class="availability-badge {{ strtolower(str_replace(' ', '-', $appointment->doctor->availability_status)) }}">
                                {{ $appointment->doctor->availability_status }}
                            </span>
                        </div>
                    </div>

                    <div class="contact-info">
                        <div class="contact-item">
                            <span class="icon">📧</span>
                            <div>
                                <label>Email</label>
                                <a href="mailto:{{ $appointment->doctor->user->email }}">{{ $appointment->doctor->user->email }}</a>
                            </div>
                        </div>
                        <div class="contact-item">
                            <span class="icon">📞</span>
                            <div>
                                <label>Phone</label>
                                <a href="tel:{{ $appointment->doctor->phone_number }}">{{ $appointment->doctor->phone_number }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="details-column">
                <!-- Check-In Status -->
                @if($appointment->checkIn)
                <div class="info-card checkin-card">
                    <div class="card-header">
                        <h2>✓ Check-In Information</h2>
                    </div>
                    
                    <div class="checkin-details">
                        <div class="checkin-item">
                            <label>Checked In At</label>
                            <strong>{{ $appointment->checkIn->checked_in_at->format('h:i A') }}</strong>
                        </div>
                        <div class="checkin-item">
                            <label>Arrival Status</label>
                            <span class="arrival-badge {{ strtolower(str_replace(' ', '-', $appointment->checkIn->arrival_status)) }}">
                                {{ $appointment->checkIn->arrival_status }}
                            </span>
                        </div>
                        @if($appointment->checkIn->check_in_notes)
                        <div class="checkin-item full-width">
                            <label>Check-In Notes</label>
                            <p>{{ $appointment->checkIn->check_in_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @elseif($appointment->status === 'confirmed')
                <div class="info-card action-card">
                    <div class="card-header">
                        <h2>Quick Actions</h2>
                    </div>
                    <a href="{{ route('receptionist.check-in') }}" class="btn-action checkin">
                        ✔️ Check In Patient
                    </a>
                </div>
                @endif

                <!-- Prescriptions -->
                @if($appointment->prescriptions->count() > 0)
                <div class="info-card">
                    <div class="card-header">
                        <h2>💊 Prescriptions</h2>
                    </div>
                    
                    @foreach($appointment->prescriptions as $prescription)
                    <div class="prescription-section">
                        <div class="prescription-header">
                            <strong>Prescription #{{ $prescription->prescription_id }}</strong>
                            <span class="prescription-date">{{ \Carbon\Carbon::parse($prescription->prescribed_date)->format('M d, Y') }}</span>
                        </div>
                        
                        @if($prescription->items->count() > 0)
                        <table class="medication-table">
                            <thead>
                                <tr>
                                    <th>Medicine</th>
                                    <th>Dosage</th>
                                    <th>Frequency</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prescription->items as $item)
                                <tr>
                                    <td><strong>{{ $item->medicine_name }}</strong></td>
                                    <td>{{ $item->dosage }}</td>
                                    <td>{{ $item->frequency }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif

                        @if($prescription->notes)
                        <div class="prescription-notes">
                            <strong>Doctor's Notes:</strong>
                            <p>{{ $prescription->notes }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Action Buttons -->
                @if($appointment->status === 'confirmed' && !$appointment->checkIn)
                <div class="info-card action-card">
                    <div class="card-header">
                        <h2>Appointment Management</h2>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn-action reschedule" onclick="openRescheduleModal()">
                            📅 Reschedule
                        </button>
                        <button class="btn-action cancel" onclick="openCancelModal()">
                            ❌ Cancel Appointment
                        </button>
                    </div>
                </div>
                @endif

                <!-- Timeline -->
                <div class="info-card">
                    <div class="card-header">
                        <h2>Activity Timeline</h2>
                    </div>
                    
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-dot created"></div>
                            <div class="timeline-content">
                                <strong>Appointment Booked</strong>
                                <span>{{ $appointment->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>

                        @if($appointment->checkIn)
                        <div class="timeline-item">
                            <div class="timeline-dot checkin"></div>
                            <div class="timeline-content">
                                <strong>Patient Checked In</strong>
                                <span>{{ $appointment->checkIn->checked_in_at->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                        @endif

                        @if($appointment->status === 'completed')
                        <div class="timeline-item">
                            <div class="timeline-dot completed"></div>
                            <div class="timeline-content">
                                <strong>Appointment Completed</strong>
                                <span>{{ $appointment->updated_at->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                        @endif

                        @if($appointment->status === 'cancelled')
                        <div class="timeline-item">
                            <div class="timeline-dot cancelled"></div>
                            <div class="timeline-content">
                                <strong>Appointment Cancelled</strong>
                                <span>{{ $appointment->updated_at->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reschedule Modal (same as appointments page) -->
    <div id="reschedule-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRescheduleModal()">&times;</span>
            <h2>Reschedule Appointment</h2>
            <form method="POST" action="{{ route('receptionist.appointments.reschedule', $appointment->appointment_id) }}">
                @csrf
                <div class="form-group">
                    <label>New Date</label>
                    <input type="date" name="appointment_date" required min="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label>New Time</label>
                    <input type="time" name="appointment_time" required>
                </div>
                <div class="form-group">
                    <label>Reason for Rescheduling (Optional)</label>
                    <textarea name="reschedule_reason" rows="3"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeRescheduleModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Reschedule</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancel Modal (same as appointments page) -->
    <div id="cancel-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCancelModal()">&times;</span>
            <h2>Cancel Appointment</h2>
            <p class="warning-text">⚠️ Are you sure you want to cancel this appointment?</p>
            <form method="POST" action="{{ route('receptionist.appointments.cancel', $appointment->appointment_id) }}">
                @csrf
                <div class="form-group">
                    <label>Cancellation Reason <span class="required">*</span></label>
                    <textarea name="cancelled_reason" rows="4" required placeholder="Please provide a reason for cancellation..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeCancelModal()">Go Back</button>
                    <button type="submit" class="btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRescheduleModal() {
            document.getElementById('reschedule-modal').style.display = 'block';
        }

        function closeRescheduleModal() {
            document.getElementById('reschedule-modal').style.display = 'none';
        }

        function openCancelModal() {
            document.getElementById('cancel-modal').style.display = 'block';
        }

        function closeCancelModal() {
            document.getElementById('cancel-modal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>