{{-- resources/views/doctor/doctor_dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MediLink | Doctor Dashboard</title>
  @vite(['resources/css/doctor/doctor_sidebar.css', 'resources/css/doctor/doctor_dashboard.css'])
</head>

<body>

@include('doctor.sidebar.doctor_sidebar')

<div class="main">
    <!-- Header Cards -->
    <div class="header">
        <div class="card">
            <h3>Today's Appointments</h3>
            <p>{{ $todayAppointments }}</p>
            <span>{{ $completedAppointments }} completed</span>
        </div>
        <div class="card">
            <h3>Total Patients</h3>
            <p>{{ $totalPatients }}</p>
            <span>+5 new this week</span>
        </div>
        <div class="card">
            <h3>Pending Prescriptions</h3>
            <p style="color:#ff9900;">{{ $pendingPrescriptions }}</p>
            <span>Need review</span>
        </div>
        <div class="card">
            <h3>Unread Messages</h3>
            <p style="color:#0077cc;">{{ $unreadMessages }}</p>
            <span>2 urgent</span>
        </div>
    </div>

    <!-- Appointment Progress -->
    <div class="section">
        <h3>Today's Appointment Progress</h3>
        <p><b>{{ $completedAppointments }} of {{ $todayAppointments }}</b> appointments completed</p>
        <div class="progress-container">
            <div class="progress-bar" style="width: {{ $todayAppointments > 0 ? ($completedAppointments / $todayAppointments * 100) : 0 }}%;"></div>
        </div>
    </div>

    <!-- Today's Appointment Schedule -->
    <div class="section">
        <h3>Today's Appointment Schedule</h3>
        <table>
            <tr>
                <th>Patient</th>
                <th>Time</th>
                <th>Type</th>
                <th>Status</th>
            </tr>
            @forelse($todaySchedule as $schedule)
            <tr>
                <td>{{ $schedule->patient }}</td>
                <td>{{ \Carbon\Carbon::parse($schedule->time)->format('g:i A') }}</td>
                <td>{{ $schedule->type ?? 'Consultation' }}</td>
                <td>
                    @php
                        // ✅ USE APPOINTMENT MODEL METHOD FOR PROPER STATUS DISPLAY
                        $appointment = \App\Models\Appointment::find($schedule->appointment_id);
                        $statusIcon = $appointment ? $appointment->getCurrentStageDisplay() : '⚪ Unknown';
                    @endphp
                    {{ $statusIcon }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center;">No appointments today.</td>
            </tr>
            @endforelse
        </table>

        {{-- Pagination --}}
        <div class="pagination">
            <p>
                Showing {{ $todaySchedule->firstItem() }}–{{ $todaySchedule->lastItem() }} of {{ $todaySchedule->total() }} appointments
            </p>
            <div class="pages">
                {{-- Previous --}}
                @if($todaySchedule->onFirstPage())
                    <button disabled>&laquo; Prev</button>
                @else
                    <a href="{{ $todaySchedule->previousPageUrl() }}"><button>&laquo; Prev</button></a>
                @endif

                {{-- Page Numbers --}}
                @foreach ($todaySchedule->getUrlRange(1, $todaySchedule->lastPage()) as $page => $url)
                    @if ($page == $todaySchedule->currentPage())
                        <button class="active">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}"><button>{{ $page }}</button></a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($todaySchedule->hasMorePages())
                    <a href="{{ $todaySchedule->nextPageUrl() }}"><button>Next &raquo;</button></a>
                @else
                    <button disabled>Next &raquo;</button>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Patient Activity -->
    <div class="section">
        <h3>Recent Patient Activity</h3>
        <ul>
            @foreach($recentActivities as $activity)
                <li>{{ $activity['icon'] }} {{ $activity['text'] }}</li>
            @endforeach
        </ul>
    </div>

    <!-- Footer Sections -->
    <div class="footer-grid">
        <div class="section">
            <h3>Recent Notifications</h3>
            <ul>
                @foreach($notifications as $note)
                    <li>{{ $note }}</li>
                @endforeach
            </ul>
        </div>

        <div class="section">
            <h3>Recent Messages</h3>
            @foreach($messages as $msg)
                <p><b>{{ $msg }}</b></p>
                <div class="message-line"></div>
            @endforeach
        </div>

        <div class="section">
            <h3>Quick Actions</h3>
            <button class="action-btn">➕ Add Prescription</button>
            <button class="action-btn">📅 View Appointments</button>
            <button class="action-btn">💬 Message Patient</button>
            <button class="action-btn">🧾 Add Record</button>
        </div>

        <div class="section">
            <h3>Patient Satisfaction</h3>
            <p style="font-size:40px; color:#00aaff; margin:0; text-align:center;">
                ⭐ {{ number_format($rating, 1) }} / 5.0
            </p>
            <p style="text-align:center; color:#555;">
                Based on {{ $ratingCount }} feedback forms
            </p>
        </div>
    </div>
</div>

</body>
</html>