<div class="sidebar">
    <h2>PHARMACIST DASHBOARD</h2>
    <div class="logo">
      <img src="{{ asset('assets/logo.png') }}" alt="MediLink Logo">
    </div>
    
    <a href="{{ route('pharmacist.dashboard') }}" class="{{ request()->routeIs('pharmacist.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>
    
    <a href="{{ route('pharmacist.prescriptions') }}" class="{{ request()->routeIs('pharmacist.prescriptions*') ? 'active' : '' }}">
        Prescription Verification
    </a>
    
    <a href="{{ route('pharmacist.inventory') }}" class="{{ request()->routeIs('pharmacist.inventory*') ? 'active' : '' }}">
        Medication Inventory
    </a>
    
    <a href="{{ route('pharmacist.alerts') }}" class="{{ request()->routeIs('pharmacist.alerts') ? 'active' : '' }}">
        Alerts & Notifications
        @php
            $unreadAlerts = auth()->user()->pharmacist->getUnreadAlertsCount();
        @endphp
        @if($unreadAlerts > 0)
            <span class="notification-badge">{{ $unreadAlerts }}</span>
        @endif
    </a>
    
    <a href="{{ route('pharmacist.reports') }}" class="{{ request()->routeIs('pharmacist.reports') ? 'active' : '' }}">
        Reports & Analytics
    </a>
    
    <a href="{{ route('pharmacist.messages') }}" class="{{ request()->routeIs('pharmacist.messages') ? 'active' : '' }}">
        Messages
    </a>
    
    <a href="{{ route('pharmacist.setting') }}" class="{{ request()->routeIs('pharmacist.setting') ? 'active' : '' }}">
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

.notification-badge {
    background-color: #ff4444;
    color: white;
    border-radius: 50%;
    padding: 2px 7px;
    font-size: 11px;
    font-weight: bold;
    margin-left: 8px;
    display: inline-block;
    min-width: 20px;
    text-align: center;
}
</style>