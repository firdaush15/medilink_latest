{{-- ============================================================
     MEDILINK — PHARMACIST SIDEBAR
     Requires: sidebar.css, sidebar.js
     ============================================================ --}}

@php
    $pharmacist      = auth()->user()->pharmacist;
    $pendingCount    = \App\Models\RestockRequest::where('requested_by', $pharmacist->pharmacist_id)->where('status', 'Pending')->count();
    $approvedCount   = \App\Models\RestockRequest::where('requested_by', $pharmacist->pharmacist_id)->where('status', 'Approved')->count();
    $stockBadge      = $pendingCount + $approvedCount;
    $unreadAlerts    = auth()->user()->pharmacist->getUnreadAlertsCount();
    $stockActive     = request()->routeIs('pharmacist.restock*', 'pharmacist.receipts*', 'pharmacist.disposals*');

    $pharmUnreadMessages = \App\Models\Conversation::where('pharmacist_id', $pharmacist->pharmacist_id)
        ->where('status', 'active')
        ->get()
        ->sum(fn($c) => $c->getUnreadCount(auth()->id()));
@endphp

<div class="sb-overlay"></div>
<button class="sb-toggle" aria-label="Open navigation">
    <span></span><span></span><span></span>
</button>

<aside class="sidebar" role="navigation" aria-label="Pharmacist navigation">

    <div class="sb-header">
        <p class="sb-role">Pharmacist Dashboard</p>
        <div class="sb-logo">
            <img src="{{ asset('assets/logo.png') }}" alt="MediLink">
        </div>
    </div>

    <div class="sb-divider"></div>

    <a href="{{ route('pharmacist.dashboard') }}"
       class="sb-link {{ request()->routeIs('pharmacist.dashboard') ? 'active' : '' }}">
        <span class="sb-icon">🏠</span>
        <span class="sb-label">Dashboard</span>
    </a>

    <div class="sb-divider"></div>
    <p class="sb-section">Pharmacy</p>

    <a href="{{ route('pharmacist.prescriptions') }}"
       class="sb-link {{ request()->routeIs('pharmacist.prescriptions*') ? 'active' : '' }}">
        <span class="sb-icon">📋</span>
        <span class="sb-label">Prescription Verification</span>
    </a>

    <a href="{{ route('pharmacist.inventory') }}"
       class="sb-link {{ request()->routeIs('pharmacist.inventory*') ? 'active' : '' }}">
        <span class="sb-icon">💊</span>
        <span class="sb-label">Medication Inventory</span>
    </a>

    {{-- Stock Management dropdown --}}
    <div class="sb-dropdown">
        <button type="button"
                class="sb-drop-trigger {{ $stockActive ? 'open active' : '' }}"
                aria-expanded="{{ $stockActive ? 'true' : 'false' }}">
            <span class="sb-icon">📦</span>
            <span class="sb-label">Stock Management</span>
            @if($stockBadge > 0)
                <span class="sb-badge sb-badge--orange">{{ $stockBadge }}</span>
            @endif
            <span class="sb-drop-arrow">▼</span>
        </button>
        <div class="sb-drop-content {{ $stockActive ? 'open' : '' }}">
            <a href="{{ route('pharmacist.restock.index') }}"
               class="sb-sub-link {{ request()->routeIs('pharmacist.restock*') ? 'active' : '' }}">
                <span class="sb-label">Restock Requests</span>
                @if($stockBadge > 0)
                    <span class="sb-badge sb-badge--orange" style="font-size:10px;min-width:18px;height:18px">{{ $stockBadge }}</span>
                @endif
            </a>
            <a href="{{ route('pharmacist.receipts.index') }}"
               class="sb-sub-link {{ request()->routeIs('pharmacist.receipts*') ? 'active' : '' }}">
                <span class="sb-label">Receive Stock</span>
            </a>
            <a href="{{ route('pharmacist.disposals.index') }}"
               class="sb-sub-link {{ request()->routeIs('pharmacist.disposals*') ? 'active' : '' }}">
                <span class="sb-label">Dispose Items</span>
            </a>
        </div>
    </div>

    <div class="sb-divider"></div>
    <p class="sb-section">Communication</p>

    <a href="{{ route('pharmacist.alerts') }}"
       class="sb-link {{ request()->routeIs('pharmacist.alerts') ? 'active' : '' }}">
        <span class="sb-icon">🔔</span>
        <span class="sb-label">Alerts &amp; Notifications</span>
        @if($unreadAlerts > 0)
            <span class="sb-badge sb-badge--pulse" id="pharmacistAlertsBadge">{{ $unreadAlerts }}</span>
        @else
            <span class="sb-badge" id="pharmacistAlertsBadge" style="display:none">0</span>
        @endif
    </a>

    <a href="{{ route('pharmacist.messages') }}"
       class="sb-link {{ request()->routeIs('pharmacist.messages') ? 'active' : '' }}">
        <span class="sb-icon">💬</span>
        <span class="sb-label">Messages</span>
        @if($pharmUnreadMessages > 0)
            <span class="sb-badge sb-badge--purple" id="pharmacistMessagesBadge">{{ $pharmUnreadMessages }}</span>
        @else
            <span class="sb-badge sb-badge--purple" id="pharmacistMessagesBadge" style="display:none">0</span>
        @endif
    </a>

    <div class="sb-divider"></div>
    <p class="sb-section">Reports</p>

    <a href="{{ route('pharmacist.reports') }}"
       class="sb-link {{ request()->routeIs('pharmacist.reports') ? 'active' : '' }}">
        <span class="sb-icon">📊</span>
        <span class="sb-label">Reports &amp; Analytics</span>
    </a>

    <a href="{{ route('pharmacist.setting') }}"
       class="sb-link {{ request()->routeIs('pharmacist.setting') ? 'active' : '' }}">
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
    MediLink.registerPoll('pharmacistMessagesBadge', '{{ route("pharmacist.messages.unread-count") }}', 'normal');
});
</script>