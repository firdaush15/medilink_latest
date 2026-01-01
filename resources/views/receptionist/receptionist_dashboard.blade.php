<!--receptionist_dashboard.blade.php - FIXED-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receptionist Dashboard - MediLink</title>
    @vite(['resources/css/receptionist/receptionist_sidebar.css', 'resources/css/receptionist/receptionist_dashboard.css'])
</head>
<body>
    @include('receptionist.sidebar.receptionist_sidebar')

    <div class="main-content">
        <div class="header">
            <h1>Welcome, {{ Auth::user()->name }}</h1>
            <p class="date-time">{{ now()->format('l, F d, Y') }}</p>
        </div>

        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon">📅</div>
                <div class="stat-details">
                    <h3>{{ $todayAppointments }}</h3>
                    <p>Today's Appointments</p>
                </div>
            </div>

            <div class="stat-card green">
                <div class="stat-icon">✅</div>
                <div class="stat-details">
                    <h3>{{ $checkedInCount }}</h3>
                    <p>Checked In</p>
                </div>
            </div>

            <div class="stat-card orange">
                <div class="stat-icon">⏳</div>
                <div class="stat-details">
                    <h3>{{ $waitingCount }}</h3>
                    <p>In Waiting Room</p>
                </div>
            </div>

            <div class="stat-card purple">
                <div class="stat-icon">👥</div>
                <div class="stat-details">
                    <h3>{{ $totalPatientsToday }}</h3>
                    <p>Total Patients Today</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="{{ route('receptionist.patients.register') }}" class="action-btn register">
                    <span class="icon">➕</span>
                    <span>New Patient Registration</span>
                </a>
                <a href="{{ route('receptionist.appointments.create') }}" class="action-btn appointment">
                    <span class="icon">📆</span>
                    <span>Book Appointment</span>
                </a>
                <a href="{{ route('receptionist.check-in') }}" class="action-btn checkin">
                    <span class="icon">✔️</span>
                    <span>Patient Check-In</span>
                </a>
                <a href="{{ route('receptionist.doctor-availability') }}" class="action-btn doctors">
                    <span class="icon">👨‍⚕️</span>
                    <span>View Doctor Schedule</span>
                </a>
            </div>
        </div>

        <!-- Today's Appointments -->
        <div class="appointments-section">
            <div class="section-header">
                <h2>Today's Appointments Schedule</h2>
                <a href="{{ route('receptionist.appointments') }}" class="view-all">View All →</a>
            </div>

            <div class="appointments-table">
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Patient Name</th>
                            <th>Doctor</th>
                            <th>Status</th>
                            <th>Check-In</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                        <tr>
                            <td class="time">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</td>
                            <td>
                                <strong>{{ $appointment->patient->user->name }}</strong>
                                <small>ID: P{{ str_pad($appointment->patient->patient_id, 4, '0', STR_PAD_LEFT) }}</small>
                            </td>
                            <td>Dr. {{ $appointment->doctor->user->name }}</td>
                            <td>
                                {{-- ✅ FIXED: Check status instead of arrival_status --}}
                                @if($appointment->status === 'checked_in' || 
                                    $appointment->status === 'vitals_pending' ||
                                    $appointment->status === 'vitals_recorded' ||
                                    $appointment->status === 'ready_for_doctor')
                                    <span class="badge checked-in">Checked In</span>
                                @elseif($appointment->status === 'confirmed')
                                    <span class="badge pending">Pending</span>
                                @elseif($appointment->status === 'in_consultation')
                                    <span class="badge in-progress">With Doctor</span>
                                @elseif($appointment->status === 'completed')
                                    <span class="badge completed">Completed</span>
                                @else
                                    <span class="badge cancelled">{{ ucfirst($appointment->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($appointment->arrived_at)
                                    <span class="checkin-time">{{ $appointment->arrived_at->format('h:i A') }}</span>
                                @else
                                    <span class="not-checked">Not yet</span>
                                @endif
                            </td>
                            <td>
                                {{-- ✅ FIXED: Check status = 'confirmed' instead of arrival_status --}}
                                @if($appointment->status === 'confirmed' && !$appointment->arrived_at)
                                    <a href="{{ route('receptionist.check-in') }}" class="btn-checkin">Check In</a>
                                @else
                                    <a href="{{ route('receptionist.appointments.show', $appointment->appointment_id) }}" class="btn-view">View</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="no-data">No appointments scheduled for today</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Doctor Availability Today -->
        <div class="doctor-availability">
            <h2>Doctor Availability Today</h2>
            <div class="doctor-grid">
                @foreach($doctors as $doctor)
                <div class="doctor-card">
                    <div class="doctor-info">
                        <div class="doctor-avatar">
                            @if($doctor->profile_photo)
                                <img src="{{ asset('storage/' . $doctor->profile_photo) }}" alt="{{ $doctor->user->name }}">
                            @else
                                <div class="avatar-placeholder">{{ substr($doctor->user->name, 0, 1) }}</div>
                            @endif
                        </div>
                        <div class="doctor-details">
                            <h3>Dr. {{ $doctor->user->name }}</h3>
                            <p class="specialization">{{ $doctor->specialization }}</p>
                            <span class="status-badge {{ strtolower(str_replace(' ', '-', $doctor->availability_status)) }}">
                                {{ $doctor->availability_status }}
                            </span>
                        </div>
                    </div>
                    <div class="appointment-count">
                        <span>{{ $doctor->appointments()->whereDate('appointment_date', today())->where('status', 'confirmed')->count() }}</span>
                        <small>appointments today</small>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>