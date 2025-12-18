<div class="sidebar">
    <h2>ADMIN DASHBOARD</h2>
    <div class="logo">
        <img src="{{ asset('assets/logo.png') }}" alt="MediLink Logo">
    </div>

    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        🏠 Dashboard
    </a>
    
    <a href="{{ route('admin.doctors') }}" class="{{ request()->routeIs('admin.doctors') ? 'active' : '' }}">
        👨‍⚕️ Manage Doctors
    </a>
    
    <a href="{{ route('admin.patients') }}" class="{{ request()->routeIs('admin.patients*') ? 'active' : '' }}">
        🧑‍🤝‍🧑 Manage Patients
    </a>
    
    <a href="{{ route('admin.appointments') }}" class="{{ request()->routeIs('admin.appointments') ? 'active' : '' }}">
        📋 Appointments
    </a>
    
    {{-- ✅ Team Management --}}
    <a href="{{ route('admin.teams.index') }}" class="{{ request()->routeIs('admin.teams.*') ? 'active' : '' }}">
        👥 Team Management
    </a>
    
    {{-- ✅ Staff Shifts --}}
    <a href="{{ route('admin.shifts.index') }}" class="{{ request()->routeIs('admin.shifts.*') ? 'active' : '' }}">
        📅 Staff Shifts
    </a>
    
    {{-- ✅ Leave Management --}}
    <a href="{{ route('admin.leaves.index') }}" class="{{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}">
        🏖️ Leave Management
    </a>
    
    {{-- ✅ Pharmacy Inventory --}}
    <a href="{{ route('admin.pharmacy-inventory') }}" class="{{ request()->routeIs('admin.pharmacy-inventory*') ? 'active' : '' }}">
        💊 Pharmacy Inventory
    </a>
    
    <a href="{{ route('admin.medical_records') }}" class="{{ request()->routeIs('admin.medical_records') ? 'active' : '' }}">
        📰 Manage Article
    </a>
    
    {{-- ✅ Messages (keeping your original route name) --}}
    <a href="{{ route('admin.messages') }}" class="{{ request()->routeIs('admin.messages*') ? 'active' : '' }}">
        💬 Messages
    </a>
    
    <a href="{{ route('admin.reports') }}" class="{{ request()->routeIs('admin.reports') ? 'active' : '' }}">
        📊 Reports
    </a>
    
    <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
        ⚙️ Settings
    </a>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-btn">🚪 Logout</button>
    </form>
</div>