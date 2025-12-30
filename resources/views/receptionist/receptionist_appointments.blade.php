<!--receptionist_appointments.blade.php - WITH PATIENT HISTORY MODAL-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments Management - MediLink</title>
    @vite(['resources/css/receptionist/receptionist_sidebar.css', 'resources/css/receptionist/receptionist_appointments.css'])
    <style>
        .btn-history-sm {
            background: #ff9800;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }
        .btn-history-sm:hover {
            background: #f57c00;
        }
    </style>
</head>
<body>
    @include('receptionist.sidebar.receptionist_sidebar')

    <div class="main-content">
        <div class="header">
            <div>
                <h1>Appointments Management</h1>
                <p>View and manage all appointments</p>
            </div>
            <a href="{{ route('receptionist.appointments.create') }}" class="btn-primary">
                ➕ New Appointment
            </a>
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

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-tabs">
                <button class="filter-btn active" data-status="all">All</button>
                <button class="filter-btn" data-status="confirmed">Confirmed</button>
                <button class="filter-btn" data-status="completed">Completed</button>
                <button class="filter-btn" data-status="cancelled">Cancelled</button>
            </div>
            
            <div class="search-box">
                <input type="text" id="search-input" placeholder="Search by patient name, doctor, or appointment ID..." />
                <button class="search-btn">🔍</button>
            </div>
        </div>

        <!-- Appointments Table -->
        <div class="appointments-container">
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date & Time</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Check-In</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                    <tr data-status="{{ $appointment->status }}">
                        <td><strong>#{{ $appointment->appointment_id }}</strong></td>
                        <td>
                            <div class="datetime">
                                <span class="date">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</span>
                                <span class="time">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="patient-info">
                                <strong>{{ $appointment->patient->user->name }}</strong>
                                <small>ID: P{{ str_pad($appointment->patient->patient_id, 4, '0', STR_PAD_LEFT) }}</small>
                                <!-- ✅ NEW: History Button -->
                                <button class="btn-history-sm" 
                                        onclick="openPatientHistory({{ $appointment->patient->patient_id }})"
                                        style="margin-top: 5px; display: block;">
                                    📋 View History
                                </button>
                            </div>
                        </td>
                        <td>
                            <div class="doctor-info">
                                <strong>Dr. {{ $appointment->doctor->user->name }}</strong>
                                <small>{{ $appointment->doctor->specialization }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="reason-text">{{ $appointment->reason ?? 'General Consultation' }}</span>
                        </td>
                        <td>
                            @if($appointment->status === 'confirmed')
                                <span class="status-badge confirmed">Confirmed</span>
                            @elseif($appointment->status === 'completed')
                                <span class="status-badge completed">Completed</span>
                            @else
                                <span class="status-badge cancelled">Cancelled</span>
                            @endif
                        </td>
                        <td>
                            @if($appointment->arrived_at)
                                <span class="checkin-yes">✓ {{ $appointment->arrived_at->format('h:i A') }}</span>
                                @if($appointment->receptionistWhoCheckedIn)
                                    <small style="display:block; color: #666;">
                                        by {{ $appointment->receptionistWhoCheckedIn->name }}
                                    </small>
                                @endif
                            @else
                                <span class="checkin-no">Not yet</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('receptionist.appointments.show', $appointment->appointment_id) }}" 
                                   class="btn-action view" 
                                   title="View Details">
                                    👁️
                                </a>
                                @if($appointment->status === 'confirmed' && !$appointment->arrived_at)
                                <button class="btn-action edit" 
                                        onclick="openRescheduleModal({{ $appointment->appointment_id }})" 
                                        title="Reschedule">
                                    📅
                                </button>
                                <button class="btn-action cancel" 
                                        onclick="openCancelModal({{ $appointment->appointment_id }})" 
                                        title="Cancel">
                                    ❌
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="no-data">No appointments found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            {{ $appointments->links() }}
        </div>
    </div>

    <!-- Reschedule Modal -->
    <div id="reschedule-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRescheduleModal()">&times;</span>
            <h2>Reschedule Appointment</h2>
            <form id="reschedule-form" method="POST">
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

    <!-- Cancel Modal -->
    <div id="cancel-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCancelModal()">&times;</span>
            <h2>Cancel Appointment</h2>
            <p class="warning-text">⚠️ Are you sure you want to cancel this appointment?</p>
            <form id="cancel-form" method="POST">
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
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
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
        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const status = this.dataset.status;
                document.querySelectorAll('tbody tr[data-status]').forEach(row => {
                    if (status === 'all' || row.dataset.status === status) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Search functionality
        document.getElementById('search-input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('tbody tr[data-status]').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Reschedule Modal
        function openRescheduleModal(appointmentId) {
            document.getElementById('reschedule-form').action = `/receptionist/appointments/${appointmentId}/reschedule`;
            document.getElementById('reschedule-modal').style.display = 'block';
        }

        function closeRescheduleModal() {
            document.getElementById('reschedule-modal').style.display = 'none';
        }

        // Cancel Modal
        function openCancelModal(appointmentId) {
            document.getElementById('cancel-form').action = `/receptionist/appointments/${appointmentId}/cancel`;
            document.getElementById('cancel-modal').style.display = 'block';
        }

        function closeCancelModal() {
            document.getElementById('cancel-modal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
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
    </script>
</body>
</html>