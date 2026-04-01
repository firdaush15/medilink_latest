{{-- resources/views/admin/admin_dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediLink – Admin Dashboard</title>
    @vite(['resources/css/sidebar.css', 'resources/css/admin/admin_dashboard.css'])
</head>

<body>
@include('admin.sidebar.admin_sidebar')

<div class="main">

    {{-- ══════════════════════════════════════════
         PAGE HEADER
    ══════════════════════════════════════════ --}}
    <div class="dash-header">
        <div>
            <h1>Admin Dashboard</h1>
            <p>{{ now()->format('l, d F Y') }}</p>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         ROW 1 — PRIMARY KPI CARDS
    ══════════════════════════════════════════ --}}
    <div class="kpi-grid">

        <div class="card kpi-blue">
            <div class="card-icon">👨‍⚕️</div>
            <div class="card-body">
                <span class="card-label">Total Doctors</span>
                <span class="card-value">{{ number_format($totalDoctors) }}</span>
                <span class="card-sub">{{ $activeDoctors }} available · +{{ $newDoctors }} this week</span>
            </div>
        </div>

        <div class="card kpi-green">
            <div class="card-icon">🧑‍🦱</div>
            <div class="card-body">
                <span class="card-label">Total Patients</span>
                <span class="card-value">{{ number_format($totalPatients) }}</span>
                <span class="card-sub">+{{ $newPatients }} new this week</span>
            </div>
        </div>

        <div class="card kpi-purple">
            <div class="card-icon">📅</div>
            <div class="card-body">
                <span class="card-label">Appointments Today</span>
                <span class="card-value">{{ $todayAppointments }}</span>
                <span class="card-sub">{{ $completedToday }} completed · {{ $inProgressToday }} in progress</span>
            </div>
        </div>

        <div class="card kpi-red">
            <div class="card-icon">❌</div>
            <div class="card-body">
                <span class="card-label">Cancelled Appointments</span>
                <span class="card-value">{{ number_format($cancelledAppointments) }}</span>
                <span class="card-sub">+{{ $cancelledThisWeek }} this week</span>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         ROW 2 — QUICK-ACTION ALERT COUNTERS
    ══════════════════════════════════════════ --}}
    <div class="quick-grid">

        <a href="{{ route('admin.leaves.index') }}" class="quick-card quick-orange">
            <span class="quick-icon">🏖️</span>
            <span class="quick-count">{{ $pendingLeaveCount }}</span>
            <span class="quick-label">Pending Leave Requests</span>
        </a>

        <a href="{{ route('admin.restock.index') }}" class="quick-card quick-yellow">
            <span class="quick-icon">📦</span>
            <span class="quick-count">{{ $pendingRestockCount }}</span>
            <span class="quick-label">Pending Restock Approvals</span>
        </a>

        <a href="{{ route('admin.pharmacy-inventory.index') }}" class="quick-card quick-red">
            <span class="quick-icon">⚠️</span>
            <span class="quick-count">{{ $lowStockCount }}</span>
            <span class="quick-label">Low-Stock Medicines</span>
        </a>

        <a href="{{ route('admin.pharmacy-inventory.index') }}" class="quick-card quick-darkred">
            <span class="quick-icon">🚫</span>
            <span class="quick-count">{{ $outOfStockCount }}</span>
            <span class="quick-label">Out-of-Stock Medicines</span>
        </a>

        <a href="{{ route('admin.reports') }}" class="quick-card quick-blue">
            <span class="quick-icon">💊</span>
            <span class="quick-count">{{ $pendingRxCount }}</span>
            <span class="quick-label">Pending Prescriptions</span>
        </a>

    </div>

    {{-- ══════════════════════════════════════════
         ROW 3 — TODAY'S PIPELINE + TOP DOCTORS
    ══════════════════════════════════════════ --}}
    <div class="dash-row">

        <div class="section section-half">
            <div class="section-head">
                <h3>Today's Appointment Pipeline</h3>
                <a href="{{ route('admin.appointments') }}" class="section-link">View All →</a>
            </div>
            @if($todayStatusBreakdown->isEmpty())
                <p class="empty-msg">No appointments scheduled for today.</p>
            @else
                <div class="pipeline-list">
                    @foreach($todayStatusBreakdown as $row)
                        @php
                            $label = ucfirst(str_replace('_', ' ', $row->status));
                            $pct   = $todayAppointments > 0
                                ? round(($row->count / $todayAppointments) * 100, 1)
                                : 0;
                        @endphp
                        <div class="pipeline-row">
                            <span class="status-dot status-{{ Str::slug($row->status) }}"></span>
                            <span class="pipeline-label">{{ $label }}</span>
                            <div class="bar-track">
                                <div class="bar-fill bar-{{ Str::slug($row->status) }}"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="pipeline-count">{{ $row->count }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="section section-half">
            <div class="section-head">
                <h3>Top Doctors This Week</h3>
                <a href="{{ route('admin.doctors') }}" class="section-link">View All →</a>
            </div>
            @if($topDoctorsThisWeek->isEmpty())
                <p class="empty-msg">No appointment data for this week yet.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Doctor</th>
                            <th>Specialisation</th>
                            <th>Appts</th>
                            <th>Done</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topDoctorsThisWeek as $i => $doc)
                            <tr>
                                <td class="rank">{{ $i + 1 }}</td>
                                <td>{{ $doc->name }}</td>
                                <td>{{ $doc->specialization }}</td>
                                <td>{{ $doc->total }}</td>
                                <td>{{ $doc->completed }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         ROW 4 — PENDING LEAVE + LOW STOCK
    ══════════════════════════════════════════ --}}
    <div class="dash-row">

        <div class="section section-half">
            <div class="section-head">
                <h3>Pending Leave Requests</h3>
                <a href="{{ route('admin.leaves.index') }}" class="section-link">View All →</a>
            </div>
            @if($pendingLeaves->isEmpty())
                <p class="empty-msg">No pending leave requests.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingLeaves as $leave)
                            <tr>
                                <td>{{ $leave->user->name ?? 'N/A' }}</td>
                                <td>{{ $leave->leave_type }}</td>
                                <td>
                                    {{ $leave->start_date->format('d M') }}
                                    –
                                    {{ $leave->end_date->format('d M Y') }}
                                </td>
                                <td>{{ $leave->days }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="section section-half">
            <div class="section-head">
                <h3>Low-Stock Medicines</h3>
                <a href="{{ route('admin.pharmacy-inventory.index') }}" class="section-link">View All →</a>
            </div>
            @if($lowStockMedicines->isEmpty())
                <p class="empty-msg">All medicines are adequately stocked.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Category</th>
                            <th>In Stock</th>
                            <th>Reorder At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockMedicines as $med)
                            <tr>
                                <td>{{ $med->medicine_name }}</td>
                                <td>{{ $med->category }}</td>
                                <td class="text-warn">{{ $med->quantity_in_stock }}</td>
                                <td>{{ $med->reorder_level }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         ROW 5 — RECENT ALERTS + RECENT MESSAGES
    ══════════════════════════════════════════ --}}
    <div class="dash-row">

        <div class="section section-half">
            <div class="section-head">
                <h3>Recent Alerts</h3>
            </div>
            @if($recentAlerts->isEmpty())
                <p class="empty-msg">No recent alerts.</p>
            @else
                <ul class="alert-list">
                    @foreach($recentAlerts as $alert)
                        <li class="alert-item {{ $alert->is_read ? '' : 'alert-unread' }}">
                            <span class="alert-priority priority-{{ strtolower($alert->priority) }}">
                                {{ $alert->getPriorityIcon() }}
                            </span>
                            <div class="alert-content">
                                <span class="alert-title">{{ $alert->alert_title }}</span>
                                <span class="alert-meta">
                                    {{ $alert->sender->name ?? 'System' }}
                                    · {{ $alert->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="section section-half">
            <div class="section-head">
                <h3>Recent Messages</h3>
                <a href="{{ route('admin.messages') }}" class="section-link">View All →</a>
            </div>
            @if($recentConversations->isEmpty())
                <p class="empty-msg">No recent messages.</p>
            @else
                <ul class="msg-list">
                    @foreach($recentConversations as $conv)
                        @php
                            $unread = $conv->getUnreadCount(auth()->id());
                        @endphp
                        <li class="msg-item">
                            <div class="msg-from">
                                <strong>{{ $conv->doctor->user->name ?? 'Unknown' }}</strong>
                                @if($unread > 0)
                                    <span class="msg-badge">{{ $unread }}</span>
                                @endif
                            </div>
                            <div class="msg-preview">
                                {{ Str::limit($conv->latestMessage->message_content ?? '—', 60) }}
                            </div>
                            <div class="msg-time">
                                {{ $conv->last_message_at ? $conv->last_message_at->diffForHumans() : '' }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         ROW 6 — UPCOMING APPOINTMENTS (PAGINATED)
    ══════════════════════════════════════════ --}}
    <div class="section" style="margin-top: 24px;">
        <div class="section-head">
            <h3>Upcoming Appointments</h3>
            <a href="{{ route('admin.appointments') }}" class="section-link">View All →</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($upcomingAppointments as $appt)
                    <tr>
                        <td>{{ $appt->patient->user->name ?? 'N/A' }}</td>
                        <td>{{ $appt->doctor->user->name ?? 'N/A' }}</td>
                        <td>{{ $appt->appointment_date->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($appt->appointment_time)->format('h:i A') }}</td>
                        <td>
                            <span class="status-badge status-{{ Str::slug($appt->status) }}">
                                {{ ucfirst(str_replace('_', ' ', $appt->status)) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-row">No upcoming appointments.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($upcomingAppointments->hasPages())
            <div class="pagination">
                <p>
                    Showing {{ $upcomingAppointments->firstItem() }}–{{ $upcomingAppointments->lastItem() }}
                    of {{ $upcomingAppointments->total() }} appointments
                </p>
                <div class="pages">
                    @if($upcomingAppointments->onFirstPage())
                        <button disabled>&laquo; Prev</button>
                    @else
                        <a href="{{ $upcomingAppointments->previousPageUrl() }}"><button>&laquo; Prev</button></a>
                    @endif

                    @foreach($upcomingAppointments->getUrlRange(1, $upcomingAppointments->lastPage()) as $page => $url)
                        @if($page == $upcomingAppointments->currentPage())
                            <button class="active">{{ $page }}</button>
                        @else
                            <a href="{{ $url }}"><button>{{ $page }}</button></a>
                        @endif
                    @endforeach

                    @if($upcomingAppointments->hasMorePages())
                        <a href="{{ $upcomingAppointments->nextPageUrl() }}"><button>Next &raquo;</button></a>
                    @else
                        <button disabled>Next &raquo;</button>
                    @endif
                </div>
            </div>
        @endif
    </div>

</div>{{-- /.main --}}

@vite(['resources/js/sidebar.js'])
</body>
</html>