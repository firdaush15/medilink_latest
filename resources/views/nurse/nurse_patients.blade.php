<!--nurse_patients.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Patient Management - MediLink</title>
    @vite(['resources/css/nurse/nurse_sidebar.css', 'resources/css/nurse/nurse_patients.css'])
    <style>
        .workflow-under-checkup {
            border-left: 4px solid #2196f3 !important;
            background: #e3f2fd;
        }
        .badge-under-checkup {
            background: #2196f3;
            color: white;
        }
    </style>
</head>

<body>
    @include('nurse.sidebar.nurse_sidebar')

    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h1>Patient Management</h1>
                <p>Record vitals and monitor patient health status</p>
            </div>
            <div class="header-right">
                <button class="icon-btn" title="Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg>
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

        <!-- Quick Stats -->
        <div class="stats-row">
            <div class="stat-mini orange">
                <div class="stat-mini-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                    </svg>
                </div>
                <div class="stat-mini-content">
                    <h3>{{ $needsVitalsCount }}</h3>
                    <p>Needs Vitals</p>
                </div>
            </div>

            <div class="stat-mini blue">
                <div class="stat-mini-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="stat-mini-content">
                    <h3>{{ $underCheckupCount }}</h3>
                    <p>Under Checkup</p>
                </div>
            </div>

            <div class="stat-mini green">
                <div class="stat-mini-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </div>
                <div class="stat-mini-content">
                    <h3>{{ $vitalsRecordedCount }}</h3>
                    <p>Vitals Recorded</p>
                </div>
            </div>

            <div class="stat-mini red">
                <div class="stat-mini-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                </div>
                <div class="stat-mini-content">
                    <h3>{{ $criticalVitalsCount }}</h3>
                    <p>Critical Vitals</p>
                </div>
            </div>
        </div>

        <!-- Search & Filter Section -->
        <div class="filters-card">
            <div class="search-bar">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" id="patientSearch" placeholder="Search by patient name, ID, or phone number..." value="{{ $search }}">
            </div>

            <div class="filter-tabs">
                <a href="{{ route('nurse.patients', ['filter' => 'all']) }}"
                    class="filter-tab {{ $filter == 'all' ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                    </svg>
                    All Arrived ({{ $totalPatientsCount }})
                </a>

                <a href="{{ route('nurse.patients', ['filter' => 'needs_vitals']) }}"
                    class="filter-tab {{ $filter == 'needs_vitals' ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                    </svg>
                    Needs Vitals ({{ $needsVitalsCount }})
                </a>

                <!-- ✅ NEW: Under Checkup Filter -->
                <a href="{{ route('nurse.patients', ['filter' => 'under_checkup']) }}"
                    class="filter-tab {{ $filter == 'under_checkup' ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Under Checkup ({{ $underCheckupCount }})
                </a>

                <a href="{{ route('nurse.patients', ['filter' => 'vitals_recorded']) }}"
                    class="filter-tab {{ $filter == 'vitals_recorded' ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    Vitals Recorded ({{ $vitalsRecordedCount }})
                </a>

                <a href="{{ route('nurse.patients', ['filter' => 'critical']) }}"
                    class="filter-tab {{ $filter == 'critical' ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    Critical ({{ $criticalVitalsCount }})
                </a>

                <a href="{{ route('nurse.patients', ['filter' => 'with_doctor']) }}"
                    class="filter-tab {{ $filter == 'with_doctor' ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    With Doctor
                </a>
            </div>
        </div>

        <!-- Patient Cards -->
        @if($patients->count() > 0)
        <div class="patients-grid">
            @foreach($patients as $patient)
            <div class="patient-card workflow-{{ $patient->workflow_class }}" data-patient-id="{{ $patient->patient_id }}">
                <!-- Patient Header -->
                <div class="patient-card-header">
                    <div class="patient-avatar">
                        @if($patient->user->profile_photo)
                        <img src="{{ asset('storage/' . $patient->user->profile_photo) }}" alt="{{ $patient->user->name }}">
                        @else
                        <div class="avatar-initials {{ $patient->has_critical_vitals ? 'critical' : '' }}">
                            {{ substr($patient->user->name, 0, 1) }}
                        </div>
                        @endif
                        @if($patient->has_critical_vitals)
                        <span class="avatar-badge critical" title="Critical Vitals">!</span>
                        @elseif($patient->under_checkup)
                        <span class="avatar-badge" style="background: #2196f3;" title="Under Checkup">⏱</span>
                        @elseif($patient->needs_vitals)
                        <span class="avatar-badge warning" title="Needs Vitals">!</span>
                        @endif
                    </div>
                    <div class="patient-info">
                        <h3>{{ $patient->user->name }}</h3>
                        <p class="patient-meta">
                            <span>{{ $patient->age }} yrs</span>
                            <span>•</span>
                            <span>{{ $patient->gender }}</span>
                            <span>•</span>
                            <span>ID: #{{ str_pad($patient->patient_id, 4, '0', STR_PAD_LEFT) }}</span>
                        </p>
                    </div>
                    <div class="patient-status">
                        @if($patient->has_critical_vitals)
                        <span class="badge badge-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            Critical Vitals
                        </span>
                        @elseif($patient->with_doctor)
                        <span class="badge badge-info">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            With Doctor
                        </span>
                        @elseif($patient->ready_for_doctor)
                        <span class="badge badge-success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            Ready for Doctor
                        </span>
                        @elseif($patient->under_checkup)
                        <!-- ✅ NEW STATUS BADGE -->
                        <span class="badge badge-under-checkup">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Under Checkup
                        </span>
                        @elseif($patient->needs_vitals)
                        <span class="badge badge-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                            </svg>
                            Needs Vitals
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Latest Vitals Preview -->
                @if($patient->latest_vital)
                <div class="vitals-summary">
                    <div class="vital-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z" />
                        </svg>
                        <span class="vital-label">Temp</span>
                        <span class="vital-value {{ $patient->latest_vital->temperature > 38 ? 'critical' : '' }}">
                            {{ $patient->latest_vital->temperature }}°C
                        </span>
                    </div>
                    <div class="vital-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                        </svg>
                        <span class="vital-label">BP</span>
                        <span class="vital-value">{{ $patient->latest_vital->blood_pressure }}</span>
                    </div>
                    <div class="vital-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                        </svg>
                        <span class="vital-label">HR</span>
                        <span class="vital-value {{ $patient->latest_vital->heart_rate > 100 ? 'critical' : '' }}">
                            {{ $patient->latest_vital->heart_rate }} bpm
                        </span>
                    </div>
                    <div class="vital-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                        </svg>
                        <span class="vital-label">SpO2</span>
                        <span class="vital-value {{ $patient->latest_vital->oxygen_saturation < 95 ? 'critical' : '' }}">
                            {{ $patient->latest_vital->oxygen_saturation }}%
                        </span>
                    </div>
                    <div class="vital-timestamp">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        Recorded {{ $patient->latest_vital->recorded_at->diffForHumans() }}
                    </div>
                </div>
                @else
                <div class="vitals-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                    </svg>
                    <p>{{ $patient->next_action ?? 'No vitals recorded yet' }}</p>
                </div>
                @endif

                <!-- Appointment Info -->
                @if($patient->today_appointment)
                <div class="appointment-info">
                    <div class="appointment-time-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        {{ $patient->today_appointment->appointment_time->format('h:i A') }}
                    </div>
                    <span class="appointment-doctor">
                        Dr. {{ $patient->today_appointment->doctor->user->name }}
                    </span>
                    <span class="workflow-stage-badge stage-{{ $patient->workflow_class }}">
                        {{ $patient->workflow_stage }}
                    </span>
                </div>
                @endif

                <!-- Actions -->
                <div class="patient-actions">
                    @if($patient->needs_vitals || $patient->under_checkup)
                    <button class="btn btn-primary btn-sm" onclick="openVitalsModal({{ $patient->patient_id }}, '{{ $patient->user->name }}', {{ $patient->today_appointment->appointment_id ?? 'null' }})">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                        </svg>
                        {{ $patient->under_checkup ? 'Continue Recording' : 'Record Vitals' }}
                    </button>
                    @elseif($patient->vitals_recorded && !$patient->with_doctor)
                    <button class="btn btn-secondary btn-sm" onclick="openVitalsModal({{ $patient->patient_id }}, '{{ $patient->user->name }}', {{ $patient->today_appointment->appointment_id ?? 'null' }})">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        Update Vitals
                    </button>
                    @endif
                    <a href="{{ route('nurse.patients.show', $patient->patient_id) }}" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        View History
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            <div class="pagination-container">
                {{ $patients->appends(request()->query())->links() }}
            </div>
            <div class="pagination-info">
                <span class="results-count">
                    Showing {{ $patients->firstItem() }} to {{ $patients->lastItem() }} of {{ $patients->total() }} patients
                </span>
            </div>
        </div>
        @else
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
            </svg>
            <h3>No Patients Found</h3>
            <p>
                @if($filter == 'all')
                No patients have arrived yet today.
                @elseif($filter == 'needs_vitals')
                All arrived patients have had their vitals recorded.
                @elseif($filter == 'under_checkup')
                No patients are currently under checkup.
                @elseif($filter == 'critical')
                No patients with critical vitals today.
                @else
                No patients match your current filter.
                @endif
            </p>
        </div>
        @endif

        <!-- Record Vitals Modal -->
        <div id="vitalsModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Record Patient Vitals</h2>
                    <button class="modal-close" onclick="closeVitalsModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>

                <form id="vitalsForm" method="POST" action="{{ route('nurse.vitals.store') }}">
                    @csrf
                    <input type="hidden" name="patient_id" id="modal_patient_id">
                    <input type="hidden" name="nurse_id" value="{{ auth()->user()->nurse->nurse_id }}">
                    <input type="hidden" name="appointment_id" id="modal_appointment_id">

                    <div class="modal-body">
                        <div class="patient-name-display">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            <span id="modal_patient_name"></span>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="temperature">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z" />
                                    </svg>
                                    Temperature (°C)
                                </label>
                                <input type="number" step="0.1" name="temperature" id="temperature" placeholder="36.5" class="form-control">
                                <span class="form-hint">Normal: 36.1 - 37.2°C</span>
                            </div>

                            <div class="form-group">
                                <label for="blood_pressure">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                                    </svg>
                                    Blood Pressure
                                </label>
                                <input type="text" name="blood_pressure" id="blood_pressure" placeholder="120/80" class="form-control">
                                <span class="form-hint">Format: systolic/diastolic (e.g., 120/80)</span>
                            </div>

                            <div class="form-group">
                                <label for="heart_rate">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                    </svg>
                                    Heart Rate (BPM)
                                </label>
                                <input type="number" name="heart_rate" id="heart_rate" placeholder="72" class="form-control">
                                <span class="form-hint">Normal: 60 - 100 bpm</span>
                            </div>

                            <div class="form-group">
                                <label for="respiratory_rate">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 10h.01M15 10h.01M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z" />
                                    </svg>
                                    Respiratory Rate
                                </label>
                                <input type="number" name="respiratory_rate" id="respiratory_rate" placeholder="16" class="form-control">
                                <span class="form-hint">Normal: 12 - 20 breaths/min</span>
                            </div>

                            <div class="form-group">
                                <label for="oxygen_saturation">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                    </svg>
                                    Oxygen Saturation (SpO2 %)
                                </label>
                                <input type="number" name="oxygen_saturation" id="oxygen_saturation" placeholder="98" class="form-control">
                                <span class="form-hint">Normal: > 95%</span>
                            </div>

                            <div class="form-group">
                                <label for="weight">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                    </svg>
                                    Weight (kg)
                                </label>
                                <input type="number" step="0.1" name="weight" id="weight" placeholder="70.5" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="height">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="2" x2="12" y2="22" />
                                        <path d="m19 15-7 7-7-7" />
                                        <path d="m19 9-7-7-7 7" />
                                    </svg>
                                    Height (cm)
                                </label>
                                <input type="number" step="0.1" name="height" id="height" placeholder="170" class="form-control">
                            </div>

                            <div class="form-group full-width">
                                <label for="notes">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="16" y1="13" x2="8" y2="13" />
                                        <line x1="16" y1="17" x2="8" y2="17" />
                                    </svg>
                                    Notes (Optional)
                                </label>
                                <textarea name="notes" id="notes" rows="3" placeholder="Any observations or concerns..." class="form-control"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="closeVitalsModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            Save Vitals
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // Search functionality
            let searchTimeout;
            document.getElementById('patientSearch').addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const search = e.target.value;
                    const currentFilter = '{{ $filter }}';
                    const url = new URL('{{ route("nurse.patients") }}', window.location.origin);
                    if (search) {
                        url.searchParams.set('search', search);
                    }
                    if (currentFilter && currentFilter !== 'all') {
                        url.searchParams.set('filter', currentFilter);
                    }
                    window.location.href = url.toString();
                }, 500);
            });

            // ✅ UPDATED: Modal functions with automatic status change
            async function openVitalsModal(patientId, patientName, appointmentId) {
                document.getElementById('modal_patient_id').value = patientId || '';
                document.getElementById('modal_appointment_id').value = appointmentId || '';
                document.getElementById('modal_patient_name').textContent = patientName || 'Select Patient';
                
                // ✅ NEW: Automatically mark as "Under Checkup" when opening modal
                if (appointmentId) {
                    try {
                        const response = await fetch(`/nurse/appointments/${appointmentId}/start-vitals`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        
                        if (response.ok) {
                            console.log('✓ Patient marked as under checkup');
                        }
                    } catch (error) {
                        console.error('Failed to update status:', error);
                    }
                }
                
                document.getElementById('vitalsModal').classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeVitalsModal() {
                document.getElementById('vitalsModal').classList.remove('active');
                document.body.style.overflow = 'auto';
                document.getElementById('vitalsForm').reset();
            }

            // Close modal on outside click
            document.getElementById('vitalsModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeVitalsModal();
                }
            });

            // Validate vital ranges
            document.getElementById('vitalsForm').addEventListener('submit', function(e) {
                const temp = parseFloat(document.getElementById('temperature').value);
                const hr = parseInt(document.getElementById('heart_rate').value);
                const spo2 = parseInt(document.getElementById('oxygen_saturation').value);

                let warnings = [];
                if (temp && (temp < 35 || temp > 39)) warnings.push('Temperature is outside normal range');
                if (hr && (hr < 40 || hr > 120)) warnings.push('Heart rate is outside normal range');
                if (spo2 && spo2 < 90) warnings.push('Oxygen saturation is critically low');

                if (warnings.length > 0 && !confirm('WARNING:\n' + warnings.join('\n') + '\n\nContinue recording?')) {
                    e.preventDefault();
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeVitalsModal();
                }
            });
        </script>
</body>
</html>