<!--receptionist_checkIn.blade.php - WITH PATIENT HISTORY MODAL-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Check-In - MediLink</title>
    @vite(['resources/css/receptionist/receptionist_sidebar.css', 'resources/css/receptionist/receptionist_checkIn.css'])
    <style>
        .queue-badge {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 0.9em;
        }
        .late-indicator {
            background: #ff9800;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            margin-left: 8px;
        }
        .estimated-time {
            color: #666;
            font-size: 0.9em;
            margin-top: 4px;
        }
        .btn-history {
            background: #ff9800;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        .btn-history:hover {
            background: #f57c00;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    @include('receptionist.sidebar.receptionist_sidebar')

    <div class="main-content">
        <div class="header">
            <h1>Patient Check-In & Smart Queue Management</h1>
            <p>Manage patient arrivals with intelligent queue prioritization</p>
        </div>

        @if(session('success'))
        <div class="alert alert-success">
            <span class="icon">✓</span>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger">
            <span class="icon">⚠</span>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <!-- Quick Check-In Search -->
        <div class="quick-checkin">
            <h2>Quick Check-In</h2>
            <div class="search-container">
                <input type="text" id="appointment-search" placeholder="Search by Patient Name, ID, or Phone Number..." autocomplete="off">
                <button class="btn-search">🔍 Search</button>
            </div>
            <div id="search-results" class="search-results"></div>
        </div>

        <!-- Today's Appointments for Check-In -->
        <div class="appointments-checkin">
            <div class="section-header">
                <h2>Today's Appointments</h2>
                <div class="filter-tabs">
                    <button class="tab-btn active" data-filter="all">All ({{ $appointments->count() }})</button>
                    <button class="tab-btn" data-filter="pending">Pending Check-In ({{ $appointments->where('status', 'confirmed')->where('arrived_at', null)->count() }})</button>
                    <button class="tab-btn" data-filter="checked-in">Checked In ({{ $appointments->whereIn('status', ['checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor'])->count() }})</button>
                    <button class="tab-btn" data-filter="completed">Completed ({{ $appointments->where('status', 'completed')->count() }})</button>
                </div>
            </div>

            <div class="appointments-grid">
                @forelse($appointments as $appointment)
                @php
                    if (in_array($appointment->status, ['checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])) {
                        $cardStatus = 'checked-in';
                    } elseif ($appointment->status === 'completed') {
                        $cardStatus = 'completed';
                    } else {
                        $cardStatus = 'pending';
                    }
                @endphp
                
                <div class="appointment-card" data-status="{{ $cardStatus }}">
                    <div class="card-header">
                        <div class="time-badge">
                            {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}
                        </div>
                        
                        @if(in_array($appointment->status, ['checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation']))
                            <span class="status-badge checked-in">
                                ✓ Checked In
                                @if($appointment->queue_number)
                                    <span class="queue-badge">Queue #{{ $appointment->queue_number }}</span>
                                @endif
                            </span>
                            @if($appointment->is_late)
                                <span class="late-indicator">⏰ Late</span>
                            @endif
                        @elseif($appointment->status === 'completed')
                            <span class="status-badge completed">Completed</span>
                        @elseif($appointment->status === 'cancelled')
                            <span class="status-badge cancelled">Cancelled</span>
                        @else
                            <span class="status-badge pending">Pending Check-In</span>
                        @endif
                    </div>

                    <div class="patient-info">
                        <h3>
                            {{ $appointment->patient->user->name }}
                            <!-- ✅ NEW: History Button -->
                            <button class="btn-history" 
                                    onclick="openPatientHistory({{ $appointment->patient->patient_id }})"
                                    title="View patient visit history">
                                📋
                            </button>
                        </h3>
                        <p class="patient-id">ID: P{{ str_pad($appointment->patient->patient_id, 4, '0', STR_PAD_LEFT) }}</p>
                        <p class="patient-details">
                            <span>📞 {{ $appointment->patient->phone_number }}</span>
                            <span>🎂 {{ $appointment->patient->age }} years</span>
                            <span>⚧ {{ $appointment->patient->gender }}</span>
                        </p>
                    </div>

                    <div class="doctor-info">
                        <strong>Doctor:</strong> Dr. {{ $appointment->doctor->user->name }}
                        <span class="specialization">{{ $appointment->doctor->specialization }}</span>
                    </div>

                    @if($appointment->reason)
                    <div class="appointment-reason">
                        <strong>Reason:</strong> {{ $appointment->reason }}
                    </div>
                    @endif

                    @if($appointment->arrived_at)
                    <div class="checkin-info">
                        <p><strong>Checked in:</strong> {{ $appointment->arrived_at->format('h:i A') }}</p>
                        <p><strong>Current Stage:</strong> {{ $appointment->getCurrentStageDisplay() }}</p>
                        
                        @if($appointment->queue_number)
                            <p><strong>Queue Position:</strong> #{{ $appointment->queue_number }}</p>
                        @endif
                        
                        @if($appointment->estimated_call_time)
                            <p class="estimated-time">
                                <strong>Estimated Call:</strong> {{ $appointment->estimated_call_time->format('h:i A') }}
                                ({{ $appointment->estimated_call_time->diffForHumans() }})
                            </p>
                        @endif
                        
                        @if($appointment->is_late)
                            <p class="late-indicator">
                                Late by {{ $appointment->late_penalty_minutes }} minutes
                            </p>
                        @endif
                        
                        @if($appointment->receptionistWhoCheckedIn)
                            <p style="font-size: 0.85em; color: #666;">
                                Checked in by: {{ $appointment->receptionistWhoCheckedIn->name }}
                            </p>
                        @endif
                    </div>
                    @endif

                    <div class="card-actions">
                        @if($appointment->status === 'confirmed' && !$appointment->arrived_at)
                            <button class="btn btn-checkin" onclick="openCheckInModal({{ $appointment->appointment_id }}, '{{ $appointment->patient->user->name }}')">
                                Check In Patient
                            </button>
                        @elseif($appointment->arrived_at && $appointment->status !== 'completed')
                            <span class="waiting-text">⏳ {{ $appointment->getCurrentStageDisplay() }}</span>
                        @endif
                        <a href="{{ route('receptionist.appointments.show', $appointment->appointment_id) }}" class="btn btn-view">View Details</a>
                    </div>
                </div>
                @empty
                <div class="no-appointments">
                    <p>No appointments scheduled for today</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Current Queue Status -->
        <div class="queue-status">
            <h2>📋 Current Waiting Queue (Smart Priority)</h2>
            <div class="queue-stats">
                <div class="queue-stat">
                    <span class="number">{{ $waitingCount }}</span>
                    <span class="label">Patients Waiting</span>
                </div>
                <div class="queue-stat">
                    <span class="number">{{ $avgWaitTime ?? 'N/A' }}</span>
                    <span class="label">Avg. Wait Time (min)</span>
                </div>
            </div>

            @if($waitingPatients->count() > 0)
            <div class="queue-list">
                @foreach($waitingPatients as $waiting)
                <div class="queue-item">
                    <div class="queue-number">
                        #{{ $waiting->queue_number ?? '?' }}
                    </div>
                    <div class="queue-patient">
                        <strong>
                            {{ $waiting->patient->user->name }}
                            <!-- ✅ NEW: History Button in Queue -->
                            <button class="btn-history" 
                                    style="padding: 4px 8px; font-size: 12px; margin-left: 8px;"
                                    onclick="openPatientHistory({{ $waiting->patient->patient_id }})"
                                    title="View history">
                                📋
                            </button>
                        </strong>
                        <small>
                            Dr. {{ $waiting->doctor->user->name }} • 
                            Appt: {{ \Carbon\Carbon::parse($waiting->appointment_time)->format('h:i A') }}
                            @if($waiting->is_late)
                                <span class="late-indicator">⏰ Late</span>
                            @endif
                        </small>
                        @if($waiting->estimated_call_time)
                            <small class="estimated-time">
                                Est. Call: {{ $waiting->estimated_call_time->format('h:i A') }}
                            </small>
                        @endif
                    </div>
                    <div class="wait-duration">
                        @if($waiting->arrived_at)
                            {{ $waiting->arrived_at->diffForHumans(null, true) }}
                        @else
                            Just arrived
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Queue Explanation -->
            <div class="queue-info">
                <p><strong>ℹ️ Queue Priority Rules:</strong></p>
                <ul style="font-size: 0.9em; color: #666; padding-left: 20px;">
                    <li>Primary sort: Appointment time (earlier slots get priority)</li>
                    <li>Secondary sort: Arrival time (for same-time appointments)</li>
                    <li>Grace period: 15 minutes after appointment time</li>
                    <li>Late arrivals: Queue position adjusted based on lateness</li>
                </ul>
            </div>
            @else
            <p class="no-queue">No patients in waiting queue</p>
            @endif
        </div>
    </div>

    <!-- Check-In Modal -->
    <div id="checkin-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Patient Check-In</h2>
            <p id="patient-name-display"></p>

            <form id="checkin-form" method="POST">
                @csrf
                <input type="hidden" id="appointment-id" name="appointment_id">

                <div class="form-group">
                    <p><strong>ℹ️ Queue Assignment:</strong></p>
                    <p style="font-size: 0.9em; color: #666;">
                        The system will automatically assign a queue number based on:
                        <br>1. Scheduled appointment time
                        <br>2. Current arrival time
                        <br>3. Grace period rules (15 minutes)
                    </p>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeCheckInModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm Check-In & Assign Queue</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================
         PATIENT VISIT HISTORY MODAL
         ======================================== -->
    <div id="patient-history-modal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h2>📋 Patient Visit History</h2>
                <span class="modal-close" onclick="closePatientHistory()">&times;</span>
            </div>
            
            <div id="patient-history-content" class="modal-body">
                <div class="loading-spinner">Loading patient history...</div>
            </div>
        </div>
    </div>

    <style>
        /* Patient History Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(3px);
        }

        .modal-container {
            background-color: #ffffff;
            margin: 3% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 950px;
            max-height: 85vh;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 22px;
        }

        .modal-close {
            color: white;
            font-size: 32px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
            transition: all 0.3s;
        }

        .modal-close:hover {
            transform: rotate(90deg);
            opacity: 0.8;
        }

        .modal-body {
            padding: 30px;
            max-height: calc(85vh - 90px);
            overflow-y: auto;
        }

        .loading-spinner {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-size: 16px;
        }

        .loading-spinner::before {
            content: "⏳";
            font-size: 48px;
            display: block;
            margin-bottom: 15px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .history-header {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .history-header h3 {
            margin: 0 0 10px 0;
            font-size: 24px;
            color: #333;
        }

        .history-header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }

        .history-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .stat-box {
            text-align: center;
            padding: 20px 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .stat-box:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #2196F3;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .flag-warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .flag-warning strong {
            color: #856404;
        }

        .visit-timeline {
            margin-top: 25px;
        }

        .visit-timeline h4 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }

        .visit-item {
            border-left: 4px solid #2196F3;
            padding-left: 25px;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .visit-item::before {
            content: '';
            width: 16px;
            height: 16px;
            background: #2196F3;
            border: 3px solid white;
            border-radius: 50%;
            position: absolute;
            left: -10px;
            top: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .visit-date {
            font-weight: bold;
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }

        .visit-doctor {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .visit-details {
            margin-top: 10px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            border-left: 3px solid #2196F3;
        }

        .visit-details p {
            margin: 8px 0;
            font-size: 14px;
            color: #555;
        }

        .visit-details strong {
            color: #333;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge.completed {
            background: #d4edda;
            color: #155724;
        }

        .badge.cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        @media (max-width: 768px) {
            .modal-container {
                width: 95%;
                margin: 10px auto;
            }
            
            .history-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

    <script>
        // Filter tabs functionality
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filter = this.dataset.filter;
                document.querySelectorAll('.appointment-card').forEach(card => {
                    if (filter === 'all' || card.dataset.status === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Check-In Modal
        function openCheckInModal(appointmentId, patientName) {
            document.getElementById('appointment-id').value = appointmentId;
            document.getElementById('patient-name-display').textContent = 'Checking in: ' + patientName;
            document.getElementById('checkin-form').action = `/receptionist/check-in/${appointmentId}/process`;
            document.getElementById('checkin-modal').style.display = 'block';
        }

        function closeCheckInModal() {
            document.getElementById('checkin-modal').style.display = 'none';
        }

        document.querySelector('.close').onclick = closeCheckInModal;

        window.onclick = function(event) {
            const modal = document.getElementById('checkin-modal');
            if (event.target == modal) {
                closeCheckInModal();
            }
        }

        // ========================================
        // PATIENT HISTORY MODAL FUNCTIONS
        // ========================================
        function openPatientHistory(patientId) {
            const modal = document.getElementById('patient-history-modal');
            const content = document.getElementById('patient-history-content');
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            content.innerHTML = '<div class="loading-spinner">Loading patient history...</div>';
            
            fetch(`/receptionist/patients/${patientId}/history`)
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch history');
                    return response.json();
                })
                .then(data => {
                    content.innerHTML = renderPatientHistory(data);
                })
                .catch(error => {
                    content.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">⚠️</div>
                            <h3>Failed to Load Patient History</h3>
                            <p style="color: #666;">${error.message}</p>
                        </div>
                    `;
                    console.error('Error loading patient history:', error);
                });
        }

        function closePatientHistory() {
            const modal = document.getElementById('patient-history-modal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function renderPatientHistory(data) {
            let html = `
                <div class="history-header">
                    <h3>${data.patient.name}</h3>
                    <p><strong>Patient ID:</strong> P${String(data.patient.id).padStart(4, '0')}</p>
                    <p><strong>Phone:</strong> ${data.patient.phone} | <strong>Email:</strong> ${data.patient.email}</p>
                    
                    ${data.patient.is_flagged ? `
                        <div class="flag-warning">
                            <span style="font-size: 24px;">⚠️</span>
                            <div>
                                <strong>Flagged Patient</strong>
                                <div style="font-size: 13px; margin-top: 3px;">${data.patient.flag_reason || 'Reason not specified'}</div>
                            </div>
                        </div>
                    ` : ''}
                    
                    <div class="history-stats">
                        <div class="stat-box">
                            <div class="stat-number">${data.stats.total_visits}</div>
                            <div class="stat-label">Total Visits</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number">${data.stats.no_shows}</div>
                            <div class="stat-label">No-Shows</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number">${data.stats.late_arrivals}</div>
                            <div class="stat-label">Late Arrivals</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number">RM ${parseFloat(data.stats.total_paid).toFixed(2)}</div>
                            <div class="stat-label">Total Paid</div>
                        </div>
                    </div>
                </div>
                
                <div class="visit-timeline">
                    <h4>📅 Recent Visits (Last 10)</h4>
            `;
            
            if (data.visits && data.visits.length > 0) {
                data.visits.forEach(visit => {
                    html += `
                        <div class="visit-item">
                            <div class="visit-date">${visit.date}</div>
                            <div class="visit-doctor">👨‍⚕️ Dr. ${visit.doctor} - ${visit.specialization}</div>
                            <div class="visit-details">
                                <p><strong>Status:</strong> <span class="badge ${visit.status.toLowerCase()}">${visit.status}</span></p>
                                <p><strong>Reason:</strong> ${visit.reason || 'General consultation'}</p>
                                ${visit.payment_collected ? `<p><strong>💳 Payment:</strong> RM ${parseFloat(visit.payment_amount).toFixed(2)}</p>` : '<p><strong>💳 Payment:</strong> Not collected</p>'}
                            </div>
                        </div>
                    `;
                });
            } else {
                html += `
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <p>No previous visits found for this patient.</p>
                    </div>
                `;
            }
            
            html += '</div>';
            return html;
        }

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('patient-history-modal');
            if (event.target === modal) {
                closePatientHistory();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePatientHistory();
            }
        });

        // Auto-refresh queue every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>