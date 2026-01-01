<div class="sidebar">
    <h2>DOCTOR DASHBOARD</h2>
    <div class="logo">
        <img src="{{ asset('assets/logo.png') }}" alt="MediLink Logo">
    </div>
    
    <a href="{{ route('doctor.dashboard') }}" class="{{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>
    
    <a href="{{ route('doctor.appointments') }}" class="{{ request()->routeIs('doctor.appointments*') ? 'active' : '' }}">
        My Appointments
    </a>
    
    <a href="{{ route('doctor.patients') }}" class="{{ request()->routeIs('doctor.patients*') ? 'active' : '' }}">
        My Patients
    </a>

    {{-- ✅ NEW: Team & Schedule --}}
    <a href="{{ route('doctor.team-schedule') }}" class="{{ request()->routeIs('doctor.team-schedule') ? 'active' : '' }}">
        <span style="display: flex; align-items: center; justify-content: space-between;">
            <span>👥 My Team & Schedule</span>
            @php
                $pendingLeaves = \App\Models\LeaveRequest::where('user_id', auth()->id())
                    ->where('status', 'pending')
                    ->count();
            @endphp
            @if($pendingLeaves > 0)
                <span class="badge" style="background: #ff9800;">{{ $pendingLeaves }}</span>
            @endif
        </span>
    </a>

    <a href="{{ route('doctor.reports') }}" class="{{ request()->routeIs('doctor.reports') ? 'active' : '' }}">
        Reports & Analytics
    </a>

    <a href="{{ route('doctor.messages') }}" class="{{ request()->routeIs('doctor.messages') ? 'active' : '' }}">
        Messages
    </a>

    {{-- Alerts Menu Item --}}
    @php
        $unreadAlerts = \App\Models\StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'doctor')
            ->where('is_read', false)
            ->count();
    @endphp

    <a href="{{ route('doctor.alerts.inbox') }}" class="{{ request()->is('doctor/alerts*') ? 'active' : '' }}">
        <span style="display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" 
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                Alerts
            </span>
            @if($unreadAlerts > 0)
                <span class="badge">{{ $unreadAlerts }}</span>
            @endif
        </span>
    </a>
    
    <a href="{{ route('doctor.setting') }}" class="{{ request()->routeIs('doctor.setting') ? 'active' : '' }}">
        Settings
    </a>
    
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

<style>
.logout-btn {
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    color: white;
    padding: 12px 25px;
    font-size: 15px;
    cursor: pointer;
    font-family: "Poppins", sans-serif;
    transition: all 0.2s ease;
}
.logout-btn:hover {
    background-color: #1b3b5f;
    border-left: 4px solid #00aaff;
}

.badge {
    background-color: #ff4d4d;
    color: white;
    border-radius: 50%;
    padding: 3px 7px;
    font-size: 12px;
    font-weight: bold;
}
</style>