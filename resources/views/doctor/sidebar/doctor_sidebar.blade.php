{{-- ============================================================
     MEDILINK — DOCTOR SIDEBAR
     Requires: sidebar.css, sidebar.js
     ============================================================ --}}

@php
    $pendingLeaves = \App\Models\LeaveRequest::where('user_id', auth()->id())
        ->where('status', 'pending')->count();

    $unreadAlerts = \App\Models\StaffAlert::where('recipient_id', auth()->id())
        ->where('recipient_type', 'doctor')
        ->where('is_read', false)->count();

    $__doctor = \App\Models\Doctor::where('user_id', auth()->id())->first();
    $doctorUnreadMessages = 0;
    if ($__doctor) {
        $doctorUnreadMessages = \App\Models\Conversation::where('doctor_id', $__doctor->doctor_id)
            ->where('status', 'active')
            ->get()
            ->sum(fn($c) => $c->getUnreadCount(auth()->id()));
    }
@endphp

<div class="sb-overlay"></div>
<button class="sb-toggle" aria-label="Open navigation">
    <span></span><span></span><span></span>
</button>

<aside class="sidebar" role="navigation" aria-label="Doctor navigation">

    <div class="sb-header">
        <p class="sb-role">Doctor Dashboard</p>
        <div class="sb-logo">
            <img src="{{ asset('assets/logo.png') }}" alt="MediLink">
        </div>
    </div>

    <div class="sb-divider"></div>

    <a href="{{ route('doctor.dashboard') }}"
       class="sb-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}">
        <span class="sb-icon">🏠</span>
        <span class="sb-label">Dashboard</span>
    </a>

    <a href="{{ route('doctor.appointments') }}"
       class="sb-link {{ request()->routeIs('doctor.appointments*') ? 'active' : '' }}">
        <span class="sb-icon">📋</span>
        <span class="sb-label">My Appointments</span>
    </a>

    <a href="{{ route('doctor.patients') }}"
       class="sb-link {{ request()->routeIs('doctor.patients*') ? 'active' : '' }}">
        <span class="sb-icon">🧑‍🤝‍🧑</span>
        <span class="sb-label">My Patients</span>
    </a>

    <div class="sb-divider"></div>
    <p class="sb-section">Team</p>

    <a href="{{ route('doctor.team-schedule') }}"
       class="sb-link {{ request()->routeIs('doctor.team-schedule') ? 'active' : '' }}">
        <span class="sb-icon">👥</span>
        <span class="sb-label">My Team &amp; Schedule</span>
        @if($pendingLeaves > 0)
            <span class="sb-badge sb-badge--orange">{{ $pendingLeaves }}</span>
        @endif
    </a>

    <div class="sb-divider"></div>
    <p class="sb-section">Communication</p>

    <a href="{{ route('doctor.alerts.inbox') }}"
       class="sb-link {{ request()->is('doctor/alerts*') ? 'active' : '' }}">
        <span class="sb-icon">🔔</span>
        <span class="sb-label">Alerts</span>
        @if($unreadAlerts > 0)
            <span class="sb-badge sb-badge--pulse" id="doctorAlertsBadge">{{ $unreadAlerts }}</span>
        @else
            <span class="sb-badge" id="doctorAlertsBadge" style="display:none">0</span>
        @endif
    </a>

    <a href="{{ route('doctor.messages') }}"
       class="sb-link {{ request()->routeIs('doctor.messages') ? 'active' : '' }}">
        <span class="sb-icon">💬</span>
        <span class="sb-label">Messages</span>
        @if($doctorUnreadMessages > 0)
            <span class="sb-badge sb-badge--purple" id="doctorMessagesBadge">{{ $doctorUnreadMessages }}</span>
        @else
            <span class="sb-badge sb-badge--purple" id="doctorMessagesBadge" style="display:none">0</span>
        @endif
    </a>

    <div class="sb-divider"></div>
    <p class="sb-section">Analytics</p>

    <a href="{{ route('doctor.reports') }}"
       class="sb-link {{ request()->routeIs('doctor.reports') ? 'active' : '' }}">
        <span class="sb-icon">📊</span>
        <span class="sb-label">Reports &amp; Analytics</span>
    </a>

    <a href="{{ route('doctor.setting') }}"
       class="sb-link {{ request()->routeIs('doctor.setting') ? 'active' : '' }}">
        <span class="sb-icon">⚙️</span>
        <span class="sb-label">Settings</span>
    </a>

    <div class="sb-footer">
        <div class="sb-divider" style="margin: 0 6px 12px;"></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sb-logout">
                <span class="sb-icon">🚪</span>
                <span class="sb-label">Logout</span>
            </button>
        </form>
    </div>

</aside>

<script>
document.addEventListener('DOMContentLoaded', function () {
    MediLink.registerPoll('doctorAlertsBadge', '{{ route("doctor.alerts.unread-count") }}', 'critical');
    MediLink.registerPoll('doctorMessagesBadge', '{{ route("doctor.messages.unread-count") }}', 'normal');
});
</script>