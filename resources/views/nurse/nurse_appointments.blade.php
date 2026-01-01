<!--nurse_appointments.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Patient Appointments - MediLink</title>
    @vite(['resources/css/nurse/nurse_sidebar.css', 'resources/css/nurse/nurse_appointments.css'])
    <style>
        .waiting-time-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #fff3e0;
            color: #f57c00;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 4px;
        }
        .waiting-time-badge.delayed {
            background: #ffebee;
            color: #c62828;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .vitals-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            padding: 12px;
            background: #f5f5f5;
            border-radius: 8px;
            margin: 12px 0;
            font-size: 13px;
        }
        .vitals-preview-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .vitals-preview-item.critical {
            color: #c62828;
            font-weight: 600;
        }
        .auto-refresh-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #4caf50;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .auto-refresh-indicator.show {
            opacity: 1;
        }
    </style>
</head>
<body>
    @include('nurse.sidebar.nurse_sidebar')

    <div class="main-content">
        <!-- Flash Messages -->
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h1>Patient Appointments</h1>
                <p>Manage patient preparation and preliminary checks</p>
            </div>
            <div class="header-right">
                <span id="lastRefresh" style="font-size: 12px; color: #757575; margin-right: 12px;"></span>
                <button class="btn btn-outline btn-sm" onclick="manualRefresh()" title="Refresh Now">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
                        <path d="M21 3v5h-5"/>
                        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
                        <path d="M8 16H3v5"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="stats-row">
            <div class="stat-mini orange">
                <div class="stat-mini-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="stat-mini-content">
                    <h3 id="waitingCount">{{ $waitingForNurseCount }}</h3>
                    <p>Waiting for Nurse</p>
                </div>
            </div>

            <div class="stat-mini blue">
                <div class="stat-mini-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                    </svg>
                </div>
                <div class="stat-mini-content">
                    <h3 id="underCheckupCount">{{ $underCheckupCount }}</h3>
                    <p>Under Checkup</p>
                </div>
            </div>

            <div class="stat-mini green">
                <div class="stat-mini-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <div class="stat-mini-content">
                    <h3 id="readyCount">{{ $readyForDoctorCount }}</h3>
                    <p>Ready for Doctor</p>
                </div>
            </div>

            <div class="stat-mini purple">
                <div class="stat-mini-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div class="stat-mini-content">
                    <h3 id="withDoctorCount">{{ $withDoctorCount }}</h3>
                    <p>With Doctor</p>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-card">
            <div class="filters-header">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    Filter Appointments
                </h3>
                <span class="results-count">{{ $appointments->total() }} results</span>
            </div>

            <form method="GET" action="{{ route('nurse.appointments') }}" class="filters-form">
                <div class="filter-group">
                    <label for="doctor_id">Doctor</label>
                    <select name="doctor_id" id="doctor_id" class="filter-select">
                        <option value="all" {{ $filterDoctor == 'all' ? 'selected' : '' }}>All Doctors</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->doctor_id }}" {{ $filterDoctor == $doctor->doctor_id ? 'selected' : '' }}>
                                Dr. {{ $doctor->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="time_slot">Time Slot</label>
                    <select name="time_slot" id="time_slot" class="filter-select">
                        <option value="all" {{ $timeSlot == 'all' ? 'selected' : '' }}>All Day</option>
                        <option value="morning" {{ $timeSlot == 'morning' ? 'selected' : '' }}>Morning (6am-12pm)</option>
                        <option value="afternoon" {{ $timeSlot == 'afternoon' ? 'selected' : '' }}>Afternoon (12pm-6pm)</option>
                        <option value="evening" {{ $timeSlot == 'evening' ? 'selected' : '' }}>Evening (6pm-12am)</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="filter-select">
                        <option value="all" {{ $filterStatus == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="waiting_for_nurse" {{ $filterStatus == 'waiting_for_nurse' ? 'selected' : '' }}>Waiting for Nurse</option>
                        <option value="under_checkup" {{ $filterStatus == 'under_checkup' ? 'selected' : '' }}>Under Checkup</option>
                        <option value="ready_for_doctor" {{ $filterStatus == 'ready_for_doctor' ? 'selected' : '' }}>Ready for Doctor</option>
                        <option value="with_doctor" {{ $filterStatus == 'with_doctor' ? 'selected' : '' }}>With Doctor</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                    <a href="{{ route('nurse.appointments') }}" class="btn btn-outline btn-sm">Reset</a>
                </div>
            </form>
        </div>

        <!-- Appointments List -->
        @if($appointments->count() > 0)
            <div class="appointments-list">
                @foreach($appointments as $appointment)
                <div class="appointment-card workflow-{{ $appointment->stage_class }}">
                    <div class="appointment-time">
                        <span class="time-badge">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</span>
                        @if($appointment->arrived_at)
                        <small class="arrived-time">Arrived: {{ $appointment->arrived_at->format('h:i A') }}</small>
                        @if($appointment->waiting_time)
                        <span class="waiting-time-badge {{ $appointment->is_delayed ? 'delayed' : '' }}">
                            ⏱️ Waiting: {{ $appointment->waiting_time }}
                        </span>
                        @endif
                        @endif
                    </div>

                    <div class="appointment-details">
                        <div class="patient-info-row">
                            <div class="patient-name-section">
                                <h3>{{ $appointment->patient->user->name }}</h3>
                                <p class="doctor-name">
                                    Dr. {{ $appointment->doctor->user->name }} • {{ $appointment->doctor->specialization }}
                                </p>
                                @if($appointment->receptionistWhoCheckedIn)
                                <p class="checkin-info">
                                    <small>Checked in by: {{ $appointment->receptionistWhoCheckedIn->name }}</small>
                                </p>
                                @endif
                            </div>

                            <div class="status-badges">
                                @if($appointment->with_doctor)
                                    <span class="badge badge-info">With Doctor</span>
                                @elseif($appointment->is_ready_for_doctor)
                                    <span class="badge badge-success">Ready for Doctor</span>
                                @elseif($appointment->has_vitals)
                                    <span class="badge badge-primary">Vitals Recorded</span>
                                @else
                                    <span class="badge badge-warning">Waiting for Nurse</span>
                                @endif
                            </div>
                        </div>

                        <!-- Latest Vitals Preview -->
                        @if($appointment->latest_vital_summary)
                        <div class="vitals-preview">
                            <div class="vitals-preview-item {{ $appointment->latest_vital_summary['is_critical'] ? 'critical' : '' }}">
                                🌡️ {{ $appointment->latest_vital_summary['temperature'] }}
                            </div>
                            <div class="vitals-preview-item">
                                💓 {{ $appointment->latest_vital_summary['heart_rate'] }}
                            </div>
                            <div class="vitals-preview-item">
                                🩺 {{ $appointment->latest_vital_summary['blood_pressure'] }}
                            </div>
                            <div class="vitals-preview-item">
                                💨 SpO2: {{ $appointment->latest_vital_summary['oxygen_saturation'] }}
                            </div>
                            @if($appointment->latest_vital_summary['is_critical'])
                            <div class="vitals-preview-item critical">
                                ⚠️ CRITICAL VALUES
                            </div>
                            @endif
                            <small style="width: 100%; text-align: right; color: #757575;">
                                {{ $appointment->latest_vital_summary['recorded_time'] }}
                            </small>
                        </div>
                        @endif

                        @if($appointment->reason)
                        <p class="visit-reason">
                            <strong>Reason:</strong> {{ $appointment->reason }}
                        </p>
                        @endif

                        <!-- Actions -->
                        <div class="appointment-actions">
                            @if($appointment->needs_vitals)
                                <!-- ✅ SIMPLIFIED: Just link to patients page -->
                                <a href="{{ route('nurse.patients', ['filter' => 'needs_vitals', 'highlight' => $appointment->patient_id]) }}" class="btn btn-primary btn-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                    </svg>
                                    Record Vitals
                                </a>
                            @elseif($appointment->has_vitals && !$appointment->is_ready_for_doctor)
                                <form method="POST" action="{{ route('nurse.appointments.mark-ready', $appointment->appointment_id) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        Mark Ready for Doctor
                                    </button>
                                </form>
                            @elseif($appointment->is_ready_for_doctor && !$appointment->with_doctor)
                                <span class="status-text">✓ Waiting for Doctor</span>
                            @elseif($appointment->with_doctor)
                                <span class="status-text">🩺 Currently with Doctor</span>
                            @endif

                            <a href="{{ route('nurse.patients.show', $appointment->patient_id) }}" class="btn btn-outline btn-sm">
                                View Patient
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                <div class="pagination-container">
                    {{ $appointments->appends(request()->query())->links() }}
                </div>
                <div class="pagination-info">
                    <span class="results-count">
                        Showing {{ $appointments->firstItem() }} to {{ $appointments->lastItem() }} of {{ $appointments->total() }} appointments
                    </span>
                </div>
            </div>
        @else
            <div class="empty-state">
                <h3>No Patients Waiting</h3>
                <p>No patients have been checked in yet today.</p>
                <a href="{{ route('nurse.dashboard') }}" class="btn btn-primary">Back to Dashboard</a>
            </div>
        @endif
    </div>

    <!-- Auto-refresh indicator -->
    <div id="refreshIndicator" class="auto-refresh-indicator">
        ✓ Updated
    </div>

    <script>
        // Auto-submit filters on change
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });

        // ✅ FIXED: Auto-refresh with better error handling
        let refreshInterval;
        
        function updateLastRefreshTime() {
            const now = new Date();
            document.getElementById('lastRefresh').textContent = 
                'Last updated: ' + now.toLocaleTimeString();
        }

        function showRefreshIndicator() {
            const indicator = document.getElementById('refreshIndicator');
            indicator.classList.add('show');
            setTimeout(() => {
                indicator.classList.remove('show');
            }, 2000);
        }

        async function refreshCounts() {
            if (document.visibilityState !== 'visible') return;

            try {
                const response = await fetch('{{ route("nurse.appointments.refresh-counts") }}');
                
                if (!response.ok) {
                    console.error('Refresh failed:', response.status);
                    return;
                }

                const data = await response.json();
                
                // Update counts
                document.getElementById('waitingCount').textContent = data.waiting_for_nurse;
                document.getElementById('underCheckupCount').textContent = data.under_checkup;
                document.getElementById('readyCount').textContent = data.ready_for_doctor;
                document.getElementById('withDoctorCount').textContent = data.with_doctor;
                
                updateLastRefreshTime();
                showRefreshIndicator();
                
                console.log('✓ Counts refreshed successfully');
            } catch (error) {
                console.error('Failed to refresh counts:', error);
            }
        }

        function manualRefresh() {
            window.location.reload();
        }

        // Start auto-refresh
        function startAutoRefresh() {
            updateLastRefreshTime();
            
            // Refresh every 30 seconds
            refreshInterval = setInterval(refreshCounts, 30000);
            
            console.log('✓ Auto-refresh started (every 30 seconds)');
        }

        // Stop auto-refresh when page is hidden
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                clearInterval(refreshInterval);
                console.log('⏸ Auto-refresh paused');
            } else {
                startAutoRefresh();
                refreshCounts(); // Refresh immediately when returning
                console.log('▶ Auto-refresh resumed');
            }
        });

        // Initialize
        startAutoRefresh();
        
        // Test the endpoint on load
        refreshCounts();
    </script>
</body>
</html>