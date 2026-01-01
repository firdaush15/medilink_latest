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

    {{-- ✅ NEW: Diagnosis Management --}}
    <a href="{{ route('admin.diagnoses.index') }}" class="{{ request()->routeIs('admin.diagnoses*') ? 'active' : '' }}">
        🩺 Diagnosis Codes
    </a>

    {{-- ✅ Pharmacy Inventory (Dropdown) --}}
    <div class="dropdown-menu">
        <a href="#"
            class="dropdown-trigger {{ request()->routeIs('admin.pharmacy-inventory*') || request()->routeIs('admin.restock*') ? 'active' : '' }}">
            💊 Pharmacy Management
            <span class="dropdown-arrow">▼</span>
        </a>
        <div class="dropdown-content {{ request()->routeIs('admin.pharmacy-inventory*') || request()->routeIs('admin.restock*') ? 'show' : '' }}">
            <a href="{{ route('admin.pharmacy-inventory.index') }}"
                class="{{ request()->routeIs('admin.pharmacy-inventory.index') ? 'active' : '' }}">
                📦 Inventory Overview
            </a>
            <a href="{{ route('admin.restock.index') }}"
                class="{{ request()->routeIs('admin.restock.index') || request()->routeIs('admin.restock.show') ? 'active' : '' }}">
                ✅ Restock Approvals
                @php
                $pendingRequests = \App\Models\RestockRequest::where('status', 'Pending')->count();
                @endphp
                @if($pendingRequests > 0)
                <span class="notification-badge">{{ $pendingRequests }}</span>
                @endif
            </a>
            <a href="{{ route('admin.restock.receipts') }}"
                class="{{ request()->routeIs('admin.restock.receipts*') ? 'active' : '' }}">
                📥 Stock Receipts
            </a>
            <a href="{{ route('admin.restock.disposals') }}"
                class="{{ request()->routeIs('admin.restock.disposals*') ? 'active' : '' }}">
                🗑️ Disposals
            </a>
            <a href="{{ route('admin.restock.reports') }}"
                class="{{ request()->routeIs('admin.restock.reports') ? 'active' : '' }}">
                📊 Restock Reports
            </a>
            <a href="{{ route('admin.pharmacy-inventory.reports') }}"
                class="{{ request()->routeIs('admin.pharmacy-inventory.reports') ? 'active' : '' }}">
                📋 Inventory Reports
            </a>
            <a href="{{ route('admin.pharmacy-inventory.analytics') }}"
                class="{{ request()->routeIs('admin.pharmacy-inventory.analytics') ? 'active' : '' }}">
                📈 Analytics
            </a>
        </div>
    </div>

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

<style>
    /* Dropdown Menu Styles */
    .dropdown-menu {
        position: relative;
    }

    .dropdown-trigger {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    }

    .dropdown-arrow {
        font-size: 10px;
        transition: transform 0.3s ease;
    }

    .dropdown-trigger.active .dropdown-arrow {
        transform: rotate(180deg);
    }

    .dropdown-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        background-color: rgba(0, 0, 0, 0.1);
    }

    .dropdown-content.show {
        max-height: 500px;
    }

    .dropdown-content a {
        padding: 10px 25px 10px 45px;
        font-size: 14px;
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

    /* Toggle dropdown on click */
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownTrigger = document.querySelector('.dropdown-trigger');
        const dropdownContent = document.querySelector('.dropdown-content');

        if (dropdownTrigger && dropdownContent) {
            dropdownTrigger.addEventListener('click', function(e) {
                e.preventDefault();
                dropdownContent.classList.toggle('show');
                this.classList.toggle('active');
            });
        }
    });
</script>