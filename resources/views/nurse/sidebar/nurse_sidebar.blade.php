{{-- ============================================================
     MEDILINK — NURSE SIDEBAR
     Requires: sidebar.css, sidebar.js
     $criticalAlerts and $unreadAlerts injected by AppServiceProvider view composer
     ============================================================ --}}

@php
    $nurse = auth()->user()->nurse;

    $myAssignedWaiting = \App\Models\Appointment::whereDate('appointment_date', today())
        ->where('assigned_nurse_id', $nurse->nurse_id ?? null)
        ->whereIn('status', ['checked_in', 'vitals_pending'])
        ->count();

    $nursePendingLeaves = \App\Models\LeaveRequest::where('user_id', auth()->id())
        ->where('status', 'pending')->count();

    $nurseUnreadMessages = 0;
    if ($nurse) {
        $nurseUnreadMessages = \App\Models\Conversation::where('nurse_id', $nurse->nurse_id)
            ->where('status', 'active')
            ->get()
            ->sum(fn($c) => $c->getUnreadCount(auth()->id()));
    }
@endphp

<div class="sb-overlay"></div>
<button class="sb-toggle" aria-label="Open navigation">
    <span></span><span></span><span></span>
</button>

<aside class="sidebar" role="navigation" aria-label="Nurse navigation">

    <div class="sb-header">
        <p class="sb-role">Nurse Dashboard</p>
        <div class="sb-logo">
            <img src="{{ asset('assets/logo.png') }}" alt="MediLink">
        </div>
    </div>

    <div class="sb-divider"></div>

    <a href="{{ route('nurse.dashboard') }}"
       class="sb-link {{ request()->routeIs('nurse.dashboard') ? 'active' : '' }}">
        <span class="sb-icon">🏠</span>
        <span class="sb-label">Dashboard</span>
    </a>

    <div class="sb-divider"></div>
    <p class="sb-section">Patients</p>

    <a href="{{ route('nurse.queue-management') }}"
       class="sb-link {{ request()->routeIs('nurse.queue-management*') ? 'active' : '' }}">
        <span class="sb-icon">📋</span>
        <span class="sb-label">My Patient Queue</span>
        @if($myAssignedWaiting > 0)
            <span class="sb-badge sb-badge--orange" id="nurseQueueBadge">{{ $myAssignedWaiting }}</span>
        @else
            <span class="sb-badge sb-badge--orange" id="nurseQueueBadge" style="display:none">0</span>
        @endif
    </a>

    <a href="{{ route('nurse.appointments') }}"
       class="sb-link {{ request()->routeIs('nurse.appointments*') ? 'active' : '' }}">
        <span class="sb-icon">✅</span>
        <span class="sb-label">Appointments &amp; Check-In</span>
    </a>

    <a href="{{ route('nurse.patients') }}"
       class="sb-link {{ request()->routeIs('nurse.patients*') ? 'active' : '' }}">
        <span class="sb-icon">🩺</span>
        <span class="sb-label">Patient Vitals &amp; Records</span>
    </a>

    <a href="{{ route('nurse.vitals-analytics') }}"
       class="sb-link {{ request()->routeIs('nurse.vitals-analytics*') ? 'active' : '' }}">
        <span class="sb-icon">📈</span>
        <span class="sb-label">Vitals Analytics</span>
    </a>

    <div class="sb-divider"></div>
    <p class="sb-section">Communication</p>

    <a href="{{ route('nurse.alerts') }}"
       class="sb-link {{ request()->routeIs('nurse.alerts*') ? 'active' : '' }}">
        <span class="sb-icon">🔔</span>
        <span class="sb-label">Alerts</span>
        @if($criticalAlerts > 0)
            <span class="sb-badge sb-badge--pulse" id="nurseAlertsBadge">{{ $criticalAlerts }}</span>
        @elseif($unreadAlerts > 0)
            <span class="sb-badge" id="nurseAlertsBadge">{{ $unreadAlerts }}</span>
        @else
            <span class="sb-badge" id="nurseAlertsBadge" style="display:none">0</span>
        @endif
    </a>

    <a href="{{ route('nurse.team-schedule') }}"
       class="sb-link {{ request()->routeIs('nurse.team-schedule') ? 'active' : '' }}">
        <span class="sb-icon">👥</span>
        <span class="sb-label">My Team &amp; Schedule</span>
        @if($nursePendingLeaves > 0)
            <span class="sb-badge sb-badge--orange">{{ $nursePendingLeaves }}</span>
        @endif
    </a>

    @if(Route::has('nurse.messages'))
    <a href="{{ route('nurse.messages') }}"
       class="sb-link {{ request()->routeIs('nurse.messages') ? 'active' : '' }}">
        <span class="sb-icon">💬</span>
        <span class="sb-label">Messages</span>
        @if($nurseUnreadMessages > 0)
            <span class="sb-badge sb-badge--purple" id="nurseMessagesBadge">{{ $nurseUnreadMessages }}</span>
        @else
            <span class="sb-badge sb-badge--purple" id="nurseMessagesBadge" style="display:none">0</span>
        @endif
    </a>
    @else
    <span class="sb-link" style="opacity:0.45;cursor:not-allowed" title="Coming soon">
        <span class="sb-icon">💬</span>
        <span class="sb-label">Messages <small style="font-size:10px;opacity:0.7">(soon)</small></span>
    </span>
    @endif

    <div class="sb-divider"></div>
    <p class="sb-section">Reports</p>

    <a href="{{ route('nurse.reports-documentation') }}"
       class="sb-link {{ request()->routeIs('nurse.reports-documentation*') ? 'active' : '' }}">
        <span class="sb-icon">📝</span>
        <span class="sb-label">Reports &amp; Docs</span>
    </a>

    @if(Route::has('nurse.settings'))
    <a href="{{ route('nurse.settings') }}"
       class="sb-link {{ request()->routeIs('nurse.settings') ? 'active' : '' }}">
        <span class="sb-icon">⚙️</span>
        <span class="sb-label">Settings</span>
    </a>
    @endif

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
    MediLink.registerPoll('nurseAlertsBadge', '{{ route("nurse.alerts.unread-count") }}', 'critical');
    MediLink.registerPoll('nurseQueueBadge', '{{ route("nurse.queue-management") }}/count', 'warning');
    MediLink.registerPoll('nurseMessagesBadge', '{{ route("nurse.messages.unread-count") }}', 'normal');
});
</script>