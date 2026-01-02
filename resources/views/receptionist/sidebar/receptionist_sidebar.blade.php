<div class="sidebar">
    <h2>RECEPTIONIST PANEL</h2>
    <div class="logo">
      <img src="{{ asset('assets/logo.png') }}" alt="MediLink Logo">
    </div>
    
    <a href="{{ route('receptionist.dashboard') }}" 
       class="{{ request()->routeIs('receptionist.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>
    
    <a href="{{ route('receptionist.patients.register') }}" 
       class="{{ request()->routeIs('receptionist.patients.register') ? 'active' : '' }}">
        Patient Registration
    </a>
    
    <a href="{{ route('receptionist.walk-in.create') }}" 
       class="{{ request()->routeIs('receptionist.walk-in.*') ? 'active' : '' }}">
        Walk-In Patient
    </a>
    
    <a href="{{ route('receptionist.appointments') }}" 
       class="{{ request()->routeIs('receptionist.appointments*') ? 'active' : '' }}">
        Appointments
    </a>
    
    <a href="{{ route('receptionist.check-in') }}" 
       class="{{ request()->routeIs('receptionist.check-in*') ? 'active' : '' }}">
        Check-In / Queue
    </a>
    
    <a href="{{ route('receptionist.search.advanced') }}" 
       class="{{ request()->routeIs('receptionist.search.*') ? 'active' : '' }}">
        Advanced Search
    </a>
    
    <a href="{{ route('receptionist.reminders.index') }}" 
       class="{{ request()->routeIs('receptionist.reminders.*') ? 'active' : '' }}">
        Reminders
    </a>

    <a href="{{ route('receptionist.alerts.index') }}" 
       class="sidebar-link {{ request()->routeIs('receptionist.alerts.*') ? 'active' : '' }}">
        <span class="icon">🔔</span>
        <span>Alerts</span>
        @if($unreadCount > 0)
        <span class="badge">{{ $unreadCount }}</span>
        @endif
    </a>

    <a href="{{ route('receptionist.checkout.index') }}" 
       class="sidebar-link {{ request()->routeIs('receptionist.checkout.*') ? 'active' : '' }}">
        <span class="icon">💰</span>
        <span>Checkout & Payment</span>
    </a>
    
    <a href="{{ route('receptionist.doctor-availability') }}" 
       class="{{ request()->routeIs('receptionist.doctor-availability') ? 'active' : '' }}">
        Doctor Availability
    </a>
    
    <a href="{{ route('receptionist.messages') }}" 
       class="{{ request()->routeIs('receptionist.messages') ? 'active' : '' }}">
        Messages
    </a>
    
    <a href="{{ route('receptionist.setting') }}" 
       class="{{ request()->routeIs('receptionist.setting') ? 'active' : '' }}">
        Settings
    </a>
    
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>