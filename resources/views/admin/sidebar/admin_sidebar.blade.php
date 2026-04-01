{{-- ============================================================
     MEDILINK — ADMIN SIDEBAR
     Requires: sidebar.css, sidebar.js
     ============================================================ --}}

{{-- Mobile backdrop + hamburger --}}
<div class="sb-overlay"></div>
<button class="sb-toggle" aria-label="Open navigation">
    <span></span><span></span><span></span>
</button>

<aside class="sidebar" role="navigation" aria-label="Admin navigation">

    {{-- Header --}}
    <div class="sb-header">
        <p class="sb-role">Admin Dashboard</p>
        <div class="sb-logo">
            <img src="{{ asset('assets/logo.png') }}" alt="MediLink">
        </div>
    </div>

    <div class="sb-divider"></div>

    <a href="{{ route('admin.dashboard') }}"
        class="sb-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <span class="sb-icon">🏠</span>
        <span class="sb-label">Dashboard</span>
    </a>

    <a href="{{ route('admin.doctors') }}"
        class="sb-link {{ request()->routeIs('admin.doctors') ? 'active' : '' }}">
        <span class="sb-icon">👨‍⚕️</span>
        <span class="sb-label">Manage Doctors</span>
    </a>

    <a href="{{ route('admin.patients') }}"
        class="sb-link {{ request()->routeIs('admin.patients*') ? 'active' : '' }}">
        <span class="sb-icon">🧑‍🤝‍🧑</span>
        <span class="sb-label">Manage Patients</span>
    </a>

    <a href="{{ route('admin.appointments') }}"
        class="sb-link {{ request()->routeIs('admin.appointments') ? 'active' : '' }}">
        <span class="sb-icon">📋</span>
        <span class="sb-label">Appointments</span>
    </a>

    <div class="sb-divider"></div>
    <p class="sb-section">Staff</p>

    <a href="{{ route('admin.teams.index') }}"
        class="sb-link {{ request()->routeIs('admin.teams.*') ? 'active' : '' }}">
        <span class="sb-icon">👥</span>
        <span class="sb-label">Team Management</span>
    </a>

    <a href="{{ route('admin.shifts.index') }}"
        class="sb-link {{ request()->routeIs('admin.shifts.*') ? 'active' : '' }}">
        <span class="sb-icon">📅</span>
        <span class="sb-label">Staff Shifts</span>
    </a>

    <a href="{{ route('admin.leaves.index') }}"
        class="sb-link {{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}">
        <span class="sb-icon">🏖️</span>
        <span class="sb-label">Leave Management</span>
    </a>

    <div class="sb-divider"></div>
    <p class="sb-section">Clinical</p>

    <a href="{{ route('admin.diagnoses.index') }}"
        class="sb-link {{ request()->routeIs('admin.diagnoses*') ? 'active' : '' }}">
        <span class="sb-icon">🩺</span>
        <span class="sb-label">Diagnosis Codes</span>
    </a>

    {{-- Pharmacy dropdown --}}
    @php
        $pharmacyActive  = request()->routeIs('admin.pharmacy-inventory*')
                        || request()->routeIs('admin.restock*');
        $pendingRestock  = \App\Models\RestockRequest::where('status', 'Pending')->count();
    @endphp
    <div class="sb-dropdown">
        <button type="button"
            class="sb-drop-trigger {{ $pharmacyActive ? 'open active' : '' }}"
            aria-expanded="{{ $pharmacyActive ? 'true' : 'false' }}">
            <span class="sb-icon">💊</span>
            <span class="sb-label">Pharmacy Management</span>
            @if($pendingRestock > 0)
                <span class="sb-badge sb-badge--orange" id="adminRestockBadge">{{ $pendingRestock }}</span>
            @endif
            <span class="sb-drop-arrow">▼</span>
        </button>
        <div class="sb-drop-content {{ $pharmacyActive ? 'open' : '' }}">
            <a href="{{ route('admin.pharmacy-inventory.index') }}"
                class="sb-sub-link {{ request()->routeIs('admin.pharmacy-inventory.index') ? 'active' : '' }}">
                <span class="sb-label">Inventory Overview</span>
            </a>
            <a href="{{ route('admin.restock.index') }}"
                class="sb-sub-link {{ request()->routeIs('admin.restock.index', 'admin.restock.show') ? 'active' : '' }}">
                <span class="sb-label">Restock Approvals</span>
                @if($pendingRestock > 0)
                    <span class="sb-badge sb-badge--orange"
                          style="font-size:10px;min-width:18px;height:18px">{{ $pendingRestock }}</span>
                @endif
            </a>
            <a href="{{ route('admin.restock.receipts') }}"
                class="sb-sub-link {{ request()->routeIs('admin.restock.receipts*') ? 'active' : '' }}">
                <span class="sb-label">Stock Receipts</span>
            </a>
            <a href="{{ route('admin.restock.disposals') }}"
                class="sb-sub-link {{ request()->routeIs('admin.restock.disposals*') ? 'active' : '' }}">
                <span class="sb-label">Disposals</span>
            </a>
            <a href="{{ route('admin.restock.reports') }}"
                class="sb-sub-link {{ request()->routeIs('admin.restock.reports') ? 'active' : '' }}">
                <span class="sb-label">Restock Reports</span>
            </a>
            <a href="{{ route('admin.pharmacy-inventory.reports') }}"
                class="sb-sub-link {{ request()->routeIs('admin.pharmacy-inventory.reports') ? 'active' : '' }}">
                <span class="sb-label">Inventory Reports</span>
            </a>
            <a href="{{ route('admin.pharmacy-inventory.analytics') }}"
                class="sb-sub-link {{ request()->routeIs('admin.pharmacy-inventory.analytics') ? 'active' : '' }}">
                <span class="sb-label">Analytics</span>
            </a>
        </div>
    </div>

    <div class="sb-divider"></div>
    <p class="sb-section">System</p>

    <a href="{{ route('admin.articles.index') }}"
        class="sb-link {{ request()->routeIs('admin.articles*') ? 'active' : '' }}">
        <span class="sb-icon">📰</span>
        <span class="sb-label">Manage Articles</span>
    </a>

    {{-- Alerts with unread badge --}}
    @php
        $adminAlertUnread = \App\Models\StaffAlert::where('recipient_id', auth()->id())
            ->where('recipient_type', 'admin')
            ->where('is_read', false)
            ->count();
    @endphp
    <a href="{{ route('admin.alerts.index') }}"
        class="sb-link {{ request()->routeIs('admin.alerts*') ? 'active' : '' }}">
        <span class="sb-icon">🔔</span>
        <span class="sb-label">Alerts</span>
        @if($adminAlertUnread > 0)
            <span class="sb-badge sb-badge--red" id="adminAlertBadge">{{ $adminAlertUnread }}</span>
        @else
            <span class="sb-badge sb-badge--red" id="adminAlertBadge" style="display:none">0</span>
        @endif
    </a>

    {{-- Messages with unread badge --}}
    @php
        $adminUnreadMessages = \App\Models\Conversation::where('admin_id', auth()->id())
            ->where('conversation_type', 'doctor_admin')
            ->where('status', '!=', 'archived')
            ->get()
            ->sum(fn($c) => $c->getUnreadCount(auth()->id()));
    @endphp
    <a href="{{ route('admin.messages') }}"
        class="sb-link {{ request()->routeIs('admin.messages*') ? 'active' : '' }}">
        <span class="sb-icon">💬</span>
        <span class="sb-label">Messages</span>
        @if($adminUnreadMessages > 0)
            <span class="sb-badge sb-badge--purple" id="adminMessagesBadge">{{ $adminUnreadMessages }}</span>
        @else
            <span class="sb-badge sb-badge--purple" id="adminMessagesBadge" style="display:none">0</span>
        @endif
    </a>

    <a href="{{ route('admin.reports') }}"
        class="sb-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
        <span class="sb-icon">📊</span>
        <span class="sb-label">Reports</span>
    </a>

    <a href="{{ route('admin.settings') }}"
        class="sb-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
        <span class="sb-icon">⚙️</span>
        <span class="sb-label">Settings</span>
    </a>

    {{-- Logout --}}
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
        MediLink.registerPoll('adminAlertBadge',   '{{ route("admin.alerts.unread-count") }}',   'normal');
        MediLink.registerPoll('adminMessagesBadge','{{ route("admin.messages.unread-count") }}', 'normal');
    });
</script>