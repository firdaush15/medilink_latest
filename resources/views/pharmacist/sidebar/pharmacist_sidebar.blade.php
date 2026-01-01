<div class="sidebar">
    <h2>PHARMACIST DASHBOARD</h2>
    <div class="logo">
      <img src="{{ asset('assets/logo.png') }}" alt="MediLink Logo">
    </div>
    
    <a href="{{ route('pharmacist.dashboard') }}" class="{{ request()->routeIs('pharmacist.dashboard') ? 'active' : '' }}">
        🏠 Dashboard
    </a>
    
    <a href="{{ route('pharmacist.prescriptions') }}" class="{{ request()->routeIs('pharmacist.prescriptions*') ? 'active' : '' }}">
        📋 Prescription Verification
    </a>
    
    <a href="{{ route('pharmacist.inventory') }}" class="{{ request()->routeIs('pharmacist.inventory*') ? 'active' : '' }}">
        💊 Medication Inventory
    </a>
    
    <div class="dropdown-menu">
        <a href="#" 
           class="dropdown-trigger {{ request()->routeIs('pharmacist.restock*') || request()->routeIs('pharmacist.receipts*') || request()->routeIs('pharmacist.disposals*') ? 'active' : '' }}">
            📦 Stock Management
            <span class="dropdown-arrow">▼</span>
        </a>
        <div class="dropdown-content {{ request()->routeIs('pharmacist.restock*') || request()->routeIs('pharmacist.receipts*') || request()->routeIs('pharmacist.disposals*') ? 'show' : '' }}">
            <a href="{{ route('pharmacist.restock.index') }}" 
               class="{{ request()->routeIs('pharmacist.restock*') ? 'active' : '' }}">
                📝 Restock Requests
                @php
                    $pharmacist = auth()->user()->pharmacist;
                    $pendingCount = \App\Models\RestockRequest::where('requested_by', $pharmacist->pharmacist_id)
                        ->where('status', 'Pending')->count();
                    $approvedCount = \App\Models\RestockRequest::where('requested_by', $pharmacist->pharmacist_id)
                        ->where('status', 'Approved')->count();
                @endphp
                @if($pendingCount > 0 || $approvedCount > 0)
                    <span class="notification-badge">{{ $pendingCount + $approvedCount }}</span>
                @endif
            </a>
            <a href="{{ route('pharmacist.receipts.index') }}" 
               class="{{ request()->routeIs('pharmacist.receipts*') ? 'active' : '' }}">
                📥 Receive Stock
            </a>
            <a href="{{ route('pharmacist.disposals.index') }}" 
               class="{{ request()->routeIs('pharmacist.disposals*') ? 'active' : '' }}">
                🗑️ Dispose Items
            </a>
        </div>
    </div>
    
    <a href="{{ route('pharmacist.alerts') }}" class="{{ request()->routeIs('pharmacist.alerts') ? 'active' : '' }}">
        🔔 Alerts & Notifications
        @php
            $unreadAlerts = auth()->user()->pharmacist->getUnreadAlertsCount();
        @endphp
        @if($unreadAlerts > 0)
            <span class="notification-badge">{{ $unreadAlerts }}</span>
        @endif
    </a>
    
    <a href="{{ route('pharmacist.reports') }}" class="{{ request()->routeIs('pharmacist.reports') ? 'active' : '' }}">
        📊 Reports & Analytics
    </a>
    
    <a href="{{ route('pharmacist.messages') }}" class="{{ request()->routeIs('pharmacist.messages') ? 'active' : '' }}">
        💬 Messages
    </a>
    
    <a href="{{ route('pharmacist.setting') }}" class="{{ request()->routeIs('pharmacist.setting') ? 'active' : '' }}">
        ⚙️ Settings
    </a>
    
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-btn">🚪 Logout</button>
    </form>
</div>

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