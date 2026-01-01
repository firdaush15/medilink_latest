<!--nurse_patient_details.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $patient->user->name }} - Patient Details - MediLink</title>
    @vite(['resources/css/nurse/nurse_sidebar.css', 'resources/css/nurse/nurse_patientDetails.css'])
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

        <!-- Page Header with Back Button -->
        <div class="page-header">
            <div class="header-left">
                <a href="{{ route('nurse.patients') }}" class="back-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Patients
                </a>
                <h1>{{ $patient->user->name }}</h1>
                <p>Complete patient health profile and records</p>
            </div>
            <div class="header-right">
                <a href="{{ route('nurse.patients') }}" class="btn btn-outline btn-sm">
                    View All Patients
                </a>
            </div>
        </div>

        <!-- Patient Overview Card -->
        <div class="overview-card">
            <div class="patient-header">
                <div class="patient-avatar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div class="patient-info">
                    <h2>{{ $patient->user->name }}</h2>
                    <div class="patient-meta">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            {{ $patient->age ?? 'N/A' }} years old
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2a10 10 0 1 0 10 10H12V2z"/>
                            </svg>
                            {{ $patient->gender ?? 'N/A' }}
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                            {{ $patient->address ?? 'Address not provided' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="patient-contact">
                <div class="contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <div>
                        <label>Email</label>
                        <span>{{ $patient->user->email }}</span>
                    </div>
                </div>
                <div class="contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    <div>
                        <label>Phone</label>
                        <span>{{ $patient->phone ?? 'Not provided' }}</span>
                    </div>
                </div>
                <div class="contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <div>
                        <label>Registered</label>
                        <span>{{ $patient->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Vital Signs -->
        @if($vitalRecords->count() > 0)
        <div class="section-card">
            <div class="section-header">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                    </svg>
                    Latest Vital Signs
                </h3>
                <a href="#vitals-history" class="view-all-link">View History</a>
            </div>

            @php
                $latestVital = $vitalRecords->first();
            @endphp

            <div class="vitals-grid">
                <div class="vital-card">
                    <div class="vital-icon temperature">🌡️</div>
                    <div class="vital-info">
                        <label>Temperature</label>
                        <h4>{{ $latestVital->temperature }}°C</h4>
                        <small class="vital-status {{ $latestVital->temperature > 37.5 || $latestVital->temperature < 36.1 ? 'abnormal' : 'normal' }}">
                            {{ $latestVital->temperature > 37.5 || $latestVital->temperature < 36.1 ? 'Abnormal' : 'Normal' }}
                        </small>
                    </div>
                </div>

                <div class="vital-card">
                    <div class="vital-icon heart-rate">💓</div>
                    <div class="vital-info">
                        <label>Heart Rate</label>
                        <h4>{{ $latestVital->heart_rate }} bpm</h4>
                        <small class="vital-status {{ $latestVital->heart_rate > 100 || $latestVital->heart_rate < 60 ? 'abnormal' : 'normal' }}">
                            {{ $latestVital->heart_rate > 100 || $latestVital->heart_rate < 60 ? 'Abnormal' : 'Normal' }}
                        </small>
                    </div>
                </div>

                <div class="vital-card">
                    <div class="vital-icon blood-pressure">🩺</div>
                    <div class="vital-info">
                        <label>Blood Pressure</label>
                        <h4>{{ $latestVital->blood_pressure }}</h4>
                        <small class="vital-status normal">Recorded</small>
                    </div>
                </div>

                <div class="vital-card">
                    <div class="vital-icon oxygen">💨</div>
                    <div class="vital-info">
                        <label>Oxygen Saturation</label>
                        <h4>{{ $latestVital->oxygen_saturation }}%</h4>
                        <small class="vital-status {{ $latestVital->oxygen_saturation < 95 ? 'abnormal' : 'normal' }}">
                            {{ $latestVital->oxygen_saturation < 95 ? 'Low' : 'Normal' }}
                        </small>
                    </div>
                </div>

                <div class="vital-card">
                    <div class="vital-icon weight">⚖️</div>
                    <div class="vital-info">
                        <label>Weight</label>
                        <h4>{{ $latestVital->weight ?? 'N/A' }} kg</h4>
                    </div>
                </div>

                <div class="vital-card">
                    <div class="vital-icon height">📏</div>
                    <div class="vital-info">
                        <label>Height</label>
                        <h4>{{ $latestVital->height ?? 'N/A' }} cm</h4>
                    </div>
                </div>
            </div>

            <div class="vital-footer">
                <small>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Recorded on {{ $latestVital->record_date ? $latestVital->record_date->format('M d, Y \a\t h:i A') : $latestVital->created_at->format('M d, Y \a\t h:i A') }}
                    @if($latestVital->nurse)
                        by {{ $latestVital->nurse->user->name }}
                    @endif
                </small>
                @if($latestVital->notes)
                <p style="margin-top: 8px; color: #666;">
                    <strong>Notes:</strong> {{ $latestVital->notes }}
                </p>
                @endif
            </div>
        </div>
        @endif

        <!-- Medical Records History -->
        @if($medicalRecords->count() > 0)
        <div class="section-card" id="medical-history">
            <div class="section-header">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                    Recent Medical Records
                </h3>
            </div>

            <div class="medical-records-list">
                @foreach($medicalRecords as $record)
                <div class="medical-record-card">
                    <div class="record-header">
                        <div>
                            <h4>{{ $record->record_date ? $record->record_date->format('M d, Y') : $record->created_at->format('M d, Y') }}</h4>
                            <p class="record-doctor">Dr. {{ $record->doctor->user->name }}</p>
                        </div>
                        <span class="record-type-badge">Medical Record</span>
                    </div>

                    @if($record->diagnosis)
                    <div class="record-section">
                        <strong>Diagnosis:</strong>
                        <p>{{ $record->diagnosis }}</p>
                    </div>
                    @endif

                    @if($record->prescription)
                    <div class="record-section">
                        <strong>Prescription:</strong>
                        <p>{{ $record->prescription }}</p>
                    </div>
                    @endif

                    @if($record->notes)
                    <div class="record-section">
                        <strong>Notes:</strong>
                        <p>{{ $record->notes }}</p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Vital Signs History -->
        @if($vitalRecords->count() > 0)
        <div class="section-card" id="vitals-history">
            <div class="section-header">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3v18h18"/>
                        <path d="m19 9-5 5-4-4-3 3"/>
                    </svg>
                    Vital Signs History
                </h3>
            </div>

            <div class="vitals-history-list">
                @foreach($vitalRecords as $vital)
                <div class="vital-history-card">
                    <div class="vital-history-date">
                        <span class="date">{{ $vital->record_date ? $vital->record_date->format('M d') : $vital->created_at->format('M d') }}</span>
                        <span class="time">{{ $vital->record_date ? $vital->record_date->format('h:i A') : $vital->created_at->format('h:i A') }}</span>
                    </div>
                    <div class="vital-history-values">
                        <span>🌡️ {{ $vital->temperature }}°C</span>
                        <span>💓 {{ $vital->heart_rate }} bpm</span>
                        <span>🩺 {{ $vital->blood_pressure }}</span>
                        <span>💨 {{ $vital->oxygen_saturation }}%</span>
                        @if($vital->weight)
                        <span>⚖️ {{ $vital->weight }} kg</span>
                        @endif
                    </div>
                    @if($vital->nurse)
                    <div class="vital-history-nurse">
                        By {{ $vital->nurse->user->name }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Empty States -->
        @if($vitalRecords->count() === 0 && $medicalRecords->count() === 0)
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            <h3>No Medical History</h3>
            <p>This patient has no vital signs or medical records yet.</p>
        </div>
        @endif
    </div>
</body>
</html>