<!--receptionist_doctorAvailability.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Availability - MediLink</title>
    @vite(['resources/css/receptionist/receptionist_sidebar.css', 'resources/css/receptionist/receptionist_doctorAvailability.css'])
</head>
<body>
    @include('receptionist.sidebar.receptionist_sidebar')

    <div class="main-content">
        <div class="header">
            <h1>Doctor Availability Schedule</h1>
            <p>View doctor schedules and availability status</p>
        </div>

        <!-- Date Selector -->
        <div class="date-selector">
            <button class="date-nav-btn" onclick="changeDate(-1)">◀ Previous</button>
            <input type="date" id="selected-date" value="{{ date('Y-m-d') }}" onchange="loadSchedule()">
            <button class="date-nav-btn" onclick="changeDate(1)">Next ▶</button>
            <button class="today-btn" onclick="setToday()">Today</button>
        </div>

        <!-- Quick Stats -->
        <div class="stats-row">
            <div class="stat-box available">
                <div class="stat-number">{{ $availableDoctors }}</div>
                <div class="stat-label">Available</div>
            </div>
            <div class="stat-box on-leave">
                <div class="stat-number">{{ $onLeaveDoctors }}</div>
                <div class="stat-label">On Leave</div>
            </div>
            <div class="stat-box busy">
                <div class="stat-number">{{ $busyDoctors }}</div>
                <div class="stat-label">Busy</div>
            </div>
            <div class="stat-box total">
                <div class="stat-number">{{ $totalDoctors }}</div>
                <div class="stat-label">Total Doctors</div>
            </div>
        </div>

        <!-- Doctors Grid -->
        <div class="doctors-grid">
            @foreach($doctors as $doctor)
            <div class="doctor-card {{ strtolower(str_replace(' ', '-', $doctor->availability_status)) }}">
                <div class="doctor-header">
                    <div class="doctor-avatar">
                        @if($doctor->profile_photo)
                            <img src="{{ asset('storage/' . $doctor->profile_photo) }}" alt="{{ $doctor->user->name }}">
                        @else
                            <div class="avatar-placeholder">{{ substr($doctor->user->name, 0, 1) }}</div>
                        @endif
                    </div>
                    <div class="doctor-basic-info">
                        <h3>Dr. {{ $doctor->user->name }}</h3>
                        <p class="specialization">{{ $doctor->specialization }}</p>
                        <span class="status-badge {{ strtolower(str_replace(' ', '-', $doctor->availability_status)) }}">
                            {{ $doctor->availability_status }}
                        </span>
                    </div>
                </div>

                <div class="doctor-details">
                    <div class="detail-row">
                        <span class="icon">📞</span>
                        <span>{{ $doctor->phone_number }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="icon">📧</span>
                        <span>{{ $doctor->user->email }}</span>
                    </div>
                </div>

                <!-- Today's Schedule -->
                <div class="schedule-section">
                    <h4>Today's Schedule</h4>
                    @php
                        $todayAppointments = $doctor->appointments()
                            ->whereDate('appointment_date', today())
                            ->orderBy('appointment_time', 'asc')
                            ->take(5)
                            ->get();
                    @endphp

                    @if($todayAppointments->count() > 0)
                        <div class="appointments-list">
                            @foreach($todayAppointments as $apt)
                            <div class="appointment-item">
                                <span class="apt-time">{{ \Carbon\Carbon::parse($apt->appointment_time)->format('h:i A') }}</span>
                                <span class="apt-patient">{{ $apt->patient->user->name }}</span>
                                <span class="apt-status {{ $apt->status }}">{{ ucfirst($apt->status) }}</span>
                            </div>
                            @endforeach
                        </div>
                        <div class="appointment-count">
                            Total: {{ $doctor->appointments()->whereDate('appointment_date', today())->count() }} appointments
                        </div>
                    @else
                        <p class="no-appointments">No appointments today</p>
                    @endif
                </div>

                <!-- Leave Information -->
                @php
                    $currentLeave = $doctor->leaves()
                        ->where('status', 'Approved')
                        ->where('start_date', '<=', today())
                        ->where('end_date', '>=', today())
                        ->first();
                @endphp

                @if($currentLeave)
                <div class="leave-info">
                    <h4>🏖️ On Leave</h4>
                    <p><strong>Period:</strong> {{ \Carbon\Carbon::parse($currentLeave->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($currentLeave->end_date)->format('M d, Y') }}</p>
                    @if($currentLeave->reason)
                    <p><strong>Reason:</strong> {{ $currentLeave->reason }}</p>
                    @endif
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="card-actions">
                    <a href="{{ route('receptionist.appointments.create', ['doctor_id' => $doctor->doctor_id]) }}" class="btn-book">
                        📅 Book Appointment
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        @if($doctors->isEmpty())
        <div class="no-doctors">
            <p>No doctors found in the system</p>
        </div>
        @endif
    </div>

    <script>
        function changeDate(days) {
            const dateInput = document.getElementById('selected-date');
            const currentDate = new Date(dateInput.value);
            currentDate.setDate(currentDate.getDate() + days);
            dateInput.value = currentDate.toISOString().split('T')[0];
            loadSchedule();
        }

        function setToday() {
            const today = new Date();
            document.getElementById('selected-date').value = today.toISOString().split('T')[0];
            loadSchedule();
        }

        function loadSchedule() {
            const date = document.getElementById('selected-date').value;
            window.location.href = `{{ route('receptionist.doctor-availability') }}?date=${date}`;
        }
    </script>
</body>
</html>