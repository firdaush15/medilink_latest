<!--admin_dashboard.blade.php-->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediLink Admin Dashboard</title>
    @vite(['resources/css/admin/admin_sidebar.css', 'resources/css/admin/admin_dashboard.css'])
</head>

<body>
@include('admin.sidebar.admin_sidebar')

<div class="main">
    <div class="header">
        <div class="card">
            <h3>Total Doctors</h3>
            <p>{{ $totalDoctors }}</p>
            <span>+{{ $newDoctors }} new this week</span>
        </div>

        <div class="card">
            <h3>Total Patients</h3>
            <p>{{ $totalPatients }}</p>
            <span>+{{ $newPatients }} new this week</span>
        </div>

        <div class="card">
            <h3>Appointments Today</h3>
            <p>{{ $todayAppointments }}</p>
            <span>{{ $completedToday }} completed</span>
        </div>

        <div class="card">
            <h3>Cancelled Appointments</h3>
            <p style="color:red;">{{ $cancelledAppointments }}</p>
            <span>+{{ $cancelledThisWeek }} this week</span>
        </div>
    </div>

    <div class="grid">
        <div class="section">
            <h3>Pending Approvals / Requests</h3>
            <table>
                <tr>
                    <th>Patient Name</th>
                    <th>Request Type</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
                @forelse($pendingAppointments as $appt)
                    <tr>
                        <td>{{ $appt->patient->user->name ?? 'N/A' }}</td>
                        <td>Appointment Approval</td>
                        <td>{{ \Carbon\Carbon::parse($appt->appointment_date)->format('d/m/Y') }}</td>
                        <td style="color: orange;">Pending</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;">No pending requests</td></tr>
                @endforelse
            </table>
        </div>

        <div class="section">
            <h3>Doctor Activity</h3>
            <ul>
                @forelse($doctorActivities as $doc)
                    <li>{{ $doc->user->name ?? 'N/A' }} -
                        {{ $doc->appointments->count() }} appointments this week
                    </li>
                @empty
                    <li>No doctor activity this week</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="section" style="margin-top:15px;">
        <h3>Upcoming Appointments</h3>
        <table>
            <tr>
                <th>Patients Name</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
            @forelse($upcomingAppointments as $appt)
                <tr>
                    <td>{{ $appt->patient->user->name ?? 'N/A' }}</td>
                    <td>{{ $appt->doctor->user->name ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($appt->appointment_date)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($appt->appointment_time)->format('h:i A') }}</td>
                    <td>{{ ucfirst($appt->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;">No upcoming appointments</td></tr>
            @endforelse
        </table>

        {{-- ✅ Custom Pagination (consistent with other pages) --}}
<div class="pagination">
    <p>
        Showing {{ $upcomingAppointments->firstItem() }}–{{ $upcomingAppointments->lastItem() }}
        of {{ $upcomingAppointments->total() }} appointments
    </p>

    <div class="pages">
        {{-- Previous Page --}}
        @if ($upcomingAppointments->onFirstPage())
            <button disabled>&laquo; Prev</button>
        @else
            <a href="{{ $upcomingAppointments->previousPageUrl() }}">
                <button>&laquo; Prev</button>
            </a>
        @endif

        {{-- Page Numbers --}}
        @foreach ($upcomingAppointments->getUrlRange(1, $upcomingAppointments->lastPage()) as $page => $url)
            @if ($page == $upcomingAppointments->currentPage())
                <button class="active">{{ $page }}</button>
            @else
                <a href="{{ $url }}">
                    <button>{{ $page }}</button>
                </a>
            @endif
        @endforeach

        {{-- Next Page --}}
        @if ($upcomingAppointments->hasMorePages())
            <a href="{{ $upcomingAppointments->nextPageUrl() }}">
                <button>Next &raquo;</button>
            </a>
        @else
            <button disabled>Next &raquo;</button>
        @endif
    </div>
</div>

    </div>

    <div class="footer-grid">
        <div class="section">
            <h3>Recent Notifications</h3>
            <ul>
                <li>New doctor registration: Dr. Farah Zahra</li>
                <li>System maintenance scheduled on 15 Oct 2025</li>
                <li>Appointment cancelled by patient Adam Ali</li>
            </ul>
        </div>

        <div class="section">
            <h3>Recent Messages</h3>
            <p><b>From:</b> Dr. Sarah Lim<br>Requesting schedule update approval.</p>
            <div class="message-line"></div>
            <p><b>From:</b> Patient - Ardell Aryana<br>Issue with appointment booking time.</p>
            <div class="message-line"></div>
            <p><b>From:</b> System<br>Daily database backup completed successfully.</p>
        </div>
    </div>
</div>
</body>
</html>
