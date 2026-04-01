{{-- ============================================================
     MEDILINK — RECEPTIONIST SIDEBAR
     Requires: sidebar.css, sidebar.js
     $unreadCount injected by AppServiceProvider view composer
     ============================================================ --}}

@php
    $__receptionist = \App\Models\Receptionist::where('user_id', auth()->id())->first();
    $receptionistUnreadMessages = 0;
    if ($__receptionist) {
        $receptionistUnreadMessages = \App\Models\Conversation::where('receptionist_id', $__receptionist->receptionist_id)
            ->where('status', 'active')
            ->get()
            ->sum(fn($c) => $c->getUnreadCount(auth()->id()));
    }
@endphp

<div class="sb-overlay"></div>
<button class="sb-toggle" aria-label="Open navigation">
    <span></span><span></span><span></span>
</button>

<aside class="sidebar" role="navigation" aria-label="Receptionist navigation">

    <div class="sb-header">
        <p class="sb-role">Receptionist Panel</p>
        <div class="sb-logo">
            <img src="{{ asset('assets/logo.png') }}" alt="MediLink">
        </div>
    </div>

    <div class="sb-divider"></div>

    <a href="{{ route('receptionist.dashboard') }}"
       class="sb-link {{ request()->routeIs('receptionist.dashboard') ? 'active' : '' }}">
        <span class="sb-icon">🏠</span>
        <span class="sb-label">Dashboard</span>
    </a>

    <div class="sb-divider"></div>
    <p class="sb-section">Patients</p>

    <a href="{{ route('receptionist.patients.register') }}"
       class="sb-link {{ request()->routeIs('receptionist.patients.register') ? 'active' : '' }}">
        <span class="sb-icon">🧑</span>
        <span class="sb-label">Patient Registration</span>
    </a>

    <a href="{{ route('receptionist.walk-in.create') }}"
       class="sb-link {{ request()->routeIs('receptionist.walk-in.*') ? 'active' : '' }}">
        <span class="sb-icon">🚶</span>
        <span class="sb-label">Walk-In Patient</span>
    </a>

    <a href="{{ route('receptionist.appointments') }}"
       class="sb-link {{ request()->routeIs('receptionist.appointments*') ? 'active' : '' }}">
        <span class="sb-icon">📋</span>
        <span class="sb-label">Appointments</span>
    </a>

    <a href="{{ route('receptionist.check-in') }}"
       class="sb-link {{ request()->routeIs('receptionist.check-in*') ? 'active' : '' }}">
        <span class="sb-icon">✅</span>
        <span class="sb-label">Check-In / Queue</span>
    </a>

    <a href="{{ route('receptionist.checkout.index') }}"
       class="sb-link {{ request()->routeIs('receptionist.checkout.*') ? 'active' : '' }}">
        <span class="sb-icon">💰</span>
        <span class="sb-label">Checkout &amp; Payment</span>
    </a>

    <div class="sb-divider"></div>
    <p class="sb-section">Tools</p>

    <a href="{{ route('receptionist.search.advanced') }}"
       class="sb-link {{ request()->routeIs('receptionist.search.*') ? 'active' : '' }}">
        <span class="sb-icon">🔍</span>
        <span class="sb-label">Advanced Search</span>
    </a>

    <a href="{{ route('receptionist.doctor-availability') }}"
       class="sb-link {{ request()->routeIs('receptionist.doctor-availability') ? 'active' : '' }}">
        <span class="sb-icon">🩺</span>
        <span class="sb-label">Doctor Availability</span>
    </a>

    <a href="{{ route('receptionist.reminders.index') }}"
       class="sb-link {{ request()->routeIs('receptionist.reminders.*') ? 'active' : '' }}">
        <span class="sb-icon">⏰</span>
        <span class="sb-label">Reminders</span>
    </a>

    <div class="sb-divider"></div>
    <p class="sb-section">Communication</p>

    <a href="{{ route('receptionist.alerts.index') }}"
       class="sb-link {{ request()->routeIs('receptionist.alerts.*') ? 'active' : '' }}">
        <span class="sb-icon">🔔</span>
        <span class="sb-label">Alerts</span>
        @if(isset($unreadCount) && $unreadCount > 0)
            <span class="sb-badge sb-badge--pulse" id="receptionistAlertsBadge">{{ $unreadCount }}</span>
        @else
            <span class="sb-badge" id="receptionistAlertsBadge" style="display:none">0</span>
        @endif
    </a>

    <a href="{{ route('receptionist.messages') }}"
       class="sb-link {{ request()->routeIs('receptionist.messages') ? 'active' : '' }}">
        <span class="sb-icon">💬</span>
        <span class="sb-label">Messages</span>
        @if($receptionistUnreadMessages > 0)
            <span class="sb-badge sb-badge--purple" id="receptionistMessagesBadge">{{ $receptionistUnreadMessages }}</span>
        @else
            <span class="sb-badge sb-badge--purple" id="receptionistMessagesBadge" style="display:none">0</span>
        @endif
    </a>

    <a href="{{ route('receptionist.setting') }}"
       class="sb-link {{ request()->routeIs('receptionist.setting') ? 'active' : '' }}">
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
    MediLink.registerPoll('receptionistMessagesBadge', '{{ route("receptionist.messages.unread-count") }}', 'normal');
    MediLink.registerPoll('receptionistAlertsBadge', '{{ route("receptionist.alerts.unread-count") }}', 'critical');
});
</script>