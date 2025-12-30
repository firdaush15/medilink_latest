<!--nurse_dashboard.blade.php-->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Dashboard - MediLink</title>
    @vite(['resources/css/nurse/nurse_sidebar.css', 'resources/css/nurse/nurse_dashboard.css'])
</head>

<body>
    @include('nurse.sidebar.nurse_sidebar')

    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>Good {{ now()->format('A') === 'AM' ? 'Morning' : 'Afternoon' }}, {{ auth()->user()->name }}</h1>
                <p class="shift-info">
                    <span class="shift-badge">{{ $nurse->shift ?? 'Day Shift' }}</span>
                    <span class="department-badge">{{ $nurse->department ?? 'General' }}</span>
                </p>
            </div>
            <div class="header-right">
                <button class="icon-btn" title="Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
                    @if($stats['critical_alerts'] > 0)
                    <span class="notification-badge">{{ $stats['critical_alerts'] }}</span>
                    @endif
                </button>
                <div class="user-info">
                    @if(auth()->user()->profile_photo)
                    <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Profile" class="profile-img">
                    @else
                    <div class="profile-img-initials">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Priority Alerts Section -->
        @if(count($recentAlerts) > 0)
        <div class="priority-alerts">
            <div class="alert-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
                <h3>Priority Alerts ({{ count($recentAlerts) }})</h3>
                <a href="{{ route('nurse.alerts') }}" class="view-all-link">View All</a>
            </div>
            <div class="alerts-scroll">
                @foreach($recentAlerts as $alert)
                <div class="alert-item {{ strtolower($alert->priority) }}">
                    <span class="alert-priority-badge">{{ $alert->priority }}</span>
                    <strong>{{ $alert->alert_title }}</strong> - {{ $alert->alert_message }}
                    @if($alert->patient)
                    (Patient: {{ $alert->patient->user->name }})
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- ✅ FIXED: Statistics Overview with Correct Variable Names -->
        <div class="stats-grid">
<div class="stat-card stat-orange">
    <div class="stat-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
        </svg>
    </div>
    <div class="stat-content">
        <div class="stat-value">{{ $stats['my_assigned_waiting'] }}</div>
        <div class="stat-label">My Patients Waiting</div>
        <div class="stat-sublabel">Assigned to you</div>
    </div>
</div>

            <div class="stat-card stat-green">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['ready_for_doctor'] }}</div>
                    <div class="stat-label">Ready for Doctor</div>
                    <div class="stat-sublabel">Vitals recorded, waiting</div>
                </div>
            </div>

            <div class="stat-card stat-blue">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['with_doctor'] }}</div>
                    <div class="stat-label">With Doctor</div>
                    <div class="stat-sublabel">Currently in consultation</div>
                </div>
            </div>

            <div class="stat-card stat-purple">
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 11 12 14 22 4" />
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['completed_today'] }}</div>
                    <div class="stat-label">Completed Today</div>
                    @if($stats['urgent_tasks'] > 0)
                    <span class="stat-badge urgent">{{ $stats['urgent_tasks'] }} urgent tasks</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Replace the Quick Actions Grid section (around line 130-210) with this: -->

        <!-- ✅ FIXED: Quick Actions Grid (REMOVED MEDICATION CARD) -->
        <div class="section">
            <h2 class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
                Quick Actions
            </h2>
            <div class="quick-actions-grid">
                <a href="{{ route('nurse.appointments') }}" class="action-card">
                    <div class="action-icon blue-gradient">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                    </div>
                    <h3>Today's Schedule</h3>
                    <p>View all appointments & patient flow</p>
                    @if($stats['waiting_for_nurse'] > 0)
                    <span class="action-badge orange">{{ $stats['waiting_for_nurse'] }} waiting</span>
                    @endif
                </a>

                <a href="{{ route('nurse.patients') }}" class="action-card">
                    <div class="action-icon pink-gradient">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                        </svg>
                    </div>
                    <h3>Record Vitals</h3>
                    <p>Log temperature, BP, heart rate</p>
                    @if($stats['waiting_for_nurse'] > 0)
                    <span class="action-badge orange">{{ $stats['waiting_for_nurse'] }} needed</span>
                    @endif
                </a>

                <a href="{{ route('nurse.tasks') }}" class="action-card">
                    <div class="action-icon green-gradient">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 11 12 14 22 4" />
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                        </svg>
                    </div>
                    <h3>Doctor Tasks</h3>
                    <p>View assigned preparation tasks</p>
                    @if($stats['urgent_tasks'] > 0)
                    <span class="action-badge urgent">{{ $stats['urgent_tasks'] }} urgent</span>
                    @endif
                </a>

                <a href="{{ route('nurse.alerts') }}" class="action-card">
                    <div class="action-icon red-gradient">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                    </div>
                    <h3>Alerts & Notifications</h3>
                    <p>Critical vitals and urgent notices</p>
                    @if($stats['critical_alerts'] > 0)
                    <span class="action-badge urgent">{{ $stats['critical_alerts'] }} critical</span>
                    @endif
                </a>

                <a href="{{ route('nurse.patients') }}" class="action-card">
                    <div class="action-icon purple-gradient">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                        </svg>
                    </div>
                    <h3>Patient Records</h3>
                    <p>View vitals history & medical records</p>
                </a>

                <a href="{{ route('nurse.messages') }}" class="action-card">
                    <div class="action-icon orange-gradient">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                        </svg>
                    </div>
                    <h3>Messages</h3>
                    <p>Communicate with doctors & admin</p>
                </a>
            </div>
        </div>

        <!-- ✅ ADD THIS SECTION -->
@if(isset($pendingAssignments) && $pendingAssignments->count() > 0)
<div class="section">
    <div class="section-header">
        <h2 class="section-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
            </svg>
            My Assigned Patients ({{ $pendingAssignments->count() }})
        </h2>
        <a href="{{ route('nurse.queue-management') }}" class="view-all-link">Go to Queue →</a>
    </div>

    <div style="display: grid; gap: 15px;">
        @foreach($pendingAssignments as $assignment)
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 4px solid #2196F3;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                <div>
                    <h3 style="margin: 0 0 5px 0; font-size: 18px; color: #333;">
                        {{ $assignment->appointment->patient->user->name }}
                    </h3>
                    <p style="margin: 0; color: #666; font-size: 14px;">
                        Dr. {{ $assignment->appointment->doctor->user->name }} • 
                        Queue #{{ $assignment->appointment->queue_number ?? '?' }}
                    </p>
                </div>
                <span style="background: #fff3cd; color: #856404; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                    WAITING
                </span>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <a href="{{ route('nurse.queue-management') }}" class="btn btn-primary btn-sm" style="flex: 1; text-align: center;">
                    📢 Call Patient
                </a>
                <a href="{{ route('nurse.patients', ['highlight' => $assignment->patient_id]) }}" class="btn btn-outline btn-sm" style="flex: 1; text-align: center;">
                    View Details
                </a>
            </div>
            <p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">
                Assigned {{ $assignment->assigned_at->diffForHumans() }}
            </p>
        </div>
        @endforeach
    </div>
</div>
@endif

        <!-- Upcoming Appointments -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                    Upcoming Appointments
                </h2>
                <span class="section-count">Next {{ $upcomingAppointments->count() }} appointments</span>
            </div>

            @if($upcomingAppointments->count() > 0)
            <div class="upcoming-appointments">
                @foreach($upcomingAppointments as $appointment)
                <a href="{{ route('nurse.appointments') }}" class="appointment-mini-card {{ $appointment->needs_vitals ? 'urgent' : '' }}">
                    <div class="appointment-mini-time">
                        <span class="time">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i') }}</span>
                        <span class="period">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('A') }}</span>
                    </div>

                    <div class="appointment-mini-info">
                        <h4 class="patient-name">{{ $appointment->patient->user->name }}</h4>
                        <p class="doctor-specialty">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Dr. {{ $appointment->doctor->user->name }} • {{ $appointment->doctor->specialization }}
                        </p>
                    </div>

                    <div class="appointment-mini-status">
                        @if($appointment->needs_vitals)
                        <span class="status-dot urgent"></span>
                        <span class="mini-badge needs-vitals">Needs Vitals</span>
                        @elseif($appointment->ready_for_doctor)
                        <span class="status-dot ready"></span>
                        <span class="mini-badge checked-in">Ready</span>
                        @elseif($appointment->with_doctor)
                        <span class="status-dot waiting"></span>
                        <span class="mini-badge" style="background: #e3f2fd; color: #2196f3;">With Doctor</span>
                        @elseif($appointment->awaiting_checkin)
                        <span class="status-dot waiting"></span>
                        <span class="mini-badge" style="background: #f5f5f5; color: #757575;">Awaiting Check-in</span>
                        @else
                        <span class="status-dot waiting"></span>
                        <span class="mini-badge" style="background: #fff3e0; color: #f57c00;">In Progress</span>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>

            <a href="{{ route('nurse.appointments') }}" class="view-all-appointments">
                <span>View All Appointments</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14" />
                    <path d="m12 5 7 7-7 7" />
                </svg>
            </a>
            @else
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                <h3>No Upcoming Appointments</h3>
                <p>All patients have been seen for now.</p>
            </div>
            @endif
        </div>

        <!-- Active Patients (Quick Access) -->
        @if(count($activePatients) > 0)
        <div class="section">
            <h2 class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
                Patients in Facility Today
            </h2>

            <div class="patients-grid">
                @foreach($activePatients as $patient)
                <div class="patient-quick-card">
                    <div class="patient-avatar">
                        @if($patient->user->profile_photo)
                        <img src="{{ asset('storage/' . $patient->user->profile_photo) }}" alt="{{ $patient->user->name }}">
                        @else
                        <div class="avatar-initials">{{ substr($patient->user->name, 0, 1) }}</div>
                        @endif
                    </div>
                    <div class="patient-quick-info">
                        <h4>{{ $patient->user->name }}</h4>
                        <p>{{ $patient->age }} yrs • {{ $patient->gender }}</p>
                        @if($patient->appointments->isNotEmpty())
                        <p class="appointment-time-small">{{ $patient->appointments->first()->appointment_time->format('h:i A') }}</p>
                        @endif
                    </div>
                    <a href="{{ route('nurse.patients', ['id' => $patient->patient_id]) }}" class="btn-icon" title="View Patient">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6" />
                        </svg>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <script>
        // Auto-refresh dashboard every 3 minutes for real-time updates
        setTimeout(() => {
            location.reload();
        }, 180000);
    </script>
</body>

</html>