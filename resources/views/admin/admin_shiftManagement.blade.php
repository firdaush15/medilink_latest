<!--admin_shiftManagement.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staff Shift Management - MediLink Admin</title>
    @vite(['resources/css/admin/admin_sidebar.css', 'resources/css/admin/admin_shiftManagement.css'])
</head>
<body>
    @include('admin.sidebar.admin_sidebar')

    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>📅 Staff Shift Management</h1>
                <p>Manage staff schedules, coverage, and availability</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-secondary" onclick="window.location.reload()">
                    🔄 Refresh
                </button>
                <button class="btn btn-primary" onclick="openCreateModal()">
                    + Create New Shift
                </button>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif

        @if($errors->any())
        <div class="alert alert-error">⚠ {{ $errors->first() }}</div>
        @endif

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card green">
                <div class="stat-value">{{ $stats['total_shifts'] ?? 0 }}</div>
                <div class="stat-label">Total Shifts This Week</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-value">{{ $stats['doctors_on_duty'] ?? 0 }}</div>
                <div class="stat-label">Doctors On Duty Today</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-value">{{ $stats['nurses_on_duty'] ?? 0 }}</div>
                <div class="stat-label">Nurses On Duty Today</div>
            </div>
            <div class="stat-card purple">
                <div class="stat-value">{{ $stats['coverage_rate'] ?? 100 }}%</div>
                <div class="stat-label">Coverage Rate</div>
            </div>
        </div>

        <!-- Enhanced Filters -->
        <div class="filters-bar">
            <form method="GET" action="{{ route('admin.shifts.index') }}" id="filterForm">
                <input type="hidden" name="week_start" value="{{ $weekStart->format('Y-m-d') }}">
                <input type="hidden" name="view_mode" id="viewModeInput" value="{{ $viewMode }}">
                
                <div class="filters-row">
                    <div class="filter-group search">
                        <label>🔍 Search Staff</label>
                        <input type="text" name="search" placeholder="Search by name..." value="{{ $searchQuery }}">
                    </div>

                    <div class="filter-group">
                        <label>View Mode</label>
                        <div class="view-toggle">
                            <button type="button" class="view-toggle-btn {{ $viewMode === 'grid' ? 'active' : '' }}" onclick="changeViewMode('grid')">📊 Grid</button>
                            <button type="button" class="view-toggle-btn {{ $viewMode === 'compact' ? 'active' : '' }}" onclick="changeViewMode('compact')">📋 Compact</button>
                        </div>
                    </div>

                    <div class="filter-group" style="min-width: 120px;">
                        <label>Show</label>
                        <select name="per_page" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>
                </div>

                <div class="role-filters">
                    <a href="{{ route('admin.shifts.index', array_merge(request()->except('role_filter'), ['week_start' => $weekStart->format('Y-m-d')])) }}" 
                       class="role-badge {{ $roleFilter === 'all' ? 'active' : '' }}">
                        👥 All <span class="count">{{ array_sum($roleCounts ?? []) }}</span>
                    </a>
                    <a href="{{ route('admin.shifts.index', array_merge(request()->all(), ['role_filter' => 'doctor'])) }}" 
                       class="role-badge {{ $roleFilter === 'doctor' ? 'active' : '' }}">
                        👨‍⚕️ Doctors <span class="count">{{ $roleCounts['doctor'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('admin.shifts.index', array_merge(request()->all(), ['role_filter' => 'nurse'])) }}" 
                       class="role-badge {{ $roleFilter === 'nurse' ? 'active' : '' }}">
                        👩‍⚕️ Nurses <span class="count">{{ $roleCounts['nurse'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('admin.shifts.index', array_merge(request()->all(), ['role_filter' => 'pharmacist'])) }}" 
                       class="role-badge {{ $roleFilter === 'pharmacist' ? 'active' : '' }}">
                        💊 Pharmacists <span class="count">{{ $roleCounts['pharmacist'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('admin.shifts.index', array_merge(request()->all(), ['role_filter' => 'receptionist'])) }}" 
                       class="role-badge {{ $roleFilter === 'receptionist' ? 'active' : '' }}">
                        📋 Receptionists <span class="count">{{ $roleCounts['receptionist'] ?? 0 }}</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Calendar Container -->
        <div class="calendar-container">
            <div class="calendar-header">
                <div>
                    <div class="calendar-title">Weekly Staff Roster</div>
                    <div style="color: #718096; font-size: 13px; margin-top: 5px;">
                        @if($viewMode === 'grid')
                            Click cells to add shifts • Click shifts to edit
                        @else
                            Click cards to expand • Click + to add shifts
                        @endif
                    </div>
                </div>
                <div class="week-nav">
                    <button onclick="previousWeek()">◀ Previous</button>
                    <span class="current-week">{{ $weekStart->format('M d') }} - {{ $weekEnd->format('M d, Y') }}</span>
                    <button onclick="nextWeek()">Next ▶</button>
                </div>
            </div>

            @if($staff->isEmpty())
                <div class="no-results">
                    <div class="no-results-icon">🔍</div>
                    <h3>No Staff Found</h3>
                    <p>Try adjusting your filters</p>
                </div>
            @elseif($viewMode === 'compact')
                <!-- COMPACT VIEW -->
                @include('admin.partials.shift_compact_view')
            @else
                <!-- GRID VIEW -->
                @include('admin.partials.shift_grid_view')
            @endif

            <!-- Pagination -->
            @if($staff->hasPages())
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing {{ $staff->firstItem() }} to {{ $staff->lastItem() }} of {{ $staff->total() }} staff
                </div>
                <div class="pagination-controls">
                    @if($staff->onFirstPage())
                        <button class="pagination-btn" disabled>◀ Prev</button>
                    @else
                        <a href="{{ $staff->previousPageUrl() }}" class="pagination-btn">◀ Prev</a>
                    @endif

                    @foreach($staff->getUrlRange(max(1, $staff->currentPage() - 2), min($staff->lastPage(), $staff->currentPage() + 2)) as $page => $url)
                        <a href="{{ $url }}" class="pagination-btn {{ $page == $staff->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                    @endforeach

                    @if($staff->hasMorePages())
                        <a href="{{ $staff->nextPageUrl() }}" class="pagination-btn">Next ▶</a>
                    @else
                        <button class="pagination-btn" disabled>Next ▶</button>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        // shift-management.js

/**
 * Change view mode between grid and compact
 */
function changeViewMode(mode) {
    document.getElementById('viewModeInput').value = mode;
    document.getElementById('filterForm').submit();
}

/**
 * Navigate to previous week
 */
function previousWeek() {
    const currentStart = new Date(document.querySelector('.current-week').dataset.weekStart || '{{ $weekStart->format("Y-m-d") }}');
    currentStart.setDate(currentStart.getDate() - 7);
    const newStart = currentStart.toISOString().split('T')[0];
    
    const url = new URL(window.location.href);
    url.searchParams.set('week_start', newStart);
    window.location.href = url.toString();
}

/**
 * Navigate to next week
 */
function nextWeek() {
    const currentStart = new Date(document.querySelector('.current-week').dataset.weekStart || '{{ $weekStart->format("Y-m-d") }}');
    currentStart.setDate(currentStart.getDate() + 7);
    const newStart = currentStart.toISOString().split('T')[0];
    
    const url = new URL(window.location.href);
    url.searchParams.set('week_start', newStart);
    window.location.href = url.toString();
}

/**
 * Open create shift modal (you'll need to implement the modal)
 */
function openCreateModal() {
    alert('Please click on a specific cell in the calendar to add a shift for a staff member.');
}

/**
 * Add shift for specific staff and date
 */
function addShift(userId, userName, userRole, date) {
    // Redirect to create page with pre-filled data
    const url = `/admin/shifts/create?user_id=${userId}&shift_date=${date}`;
    window.location.href = url;
}

/**
 * Edit existing shift
 */
function editShift(shiftId) {
    window.location.href = `/admin/shifts/${shiftId}/edit`;
}

/**
 * Delete shift with confirmation
 */
function deleteShift(shiftId) {
    if (confirm('Are you sure you want to delete this shift? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/shifts/${shiftId}`;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${csrfToken}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Toggle compact view expansion
 */
function toggleCompactExpand(staffId) {
    const detailElement = document.getElementById(`detail-${staffId}`);
    const iconElement = document.getElementById(`expand-icon-${staffId}`);
    
    if (detailElement.classList.contains('expanded')) {
        detailElement.classList.remove('expanded');
        iconElement.classList.remove('expanded');
    } else {
        detailElement.classList.add('expanded');
        iconElement.classList.add('expanded');
    }
}

/**
 * Real-time search with debouncing
 */
let searchTimeout = null;
const searchInput = document.querySelector('input[name="search"]');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    });
}

/**
 * Initialize: Store week start date in DOM for JS access
 */
document.addEventListener('DOMContentLoaded', function() {
    const weekElement = document.querySelector('.current-week');
    if (weekElement) {
        // Get week start from URL or default
        const params = new URLSearchParams(window.location.search);
        const weekStart = params.get('week_start') || new Date().toISOString().split('T')[0];
        weekElement.dataset.weekStart = weekStart;
    }
    
    // Auto-expand first compact card if there's only one staff
    const compactCards = document.querySelectorAll('.compact-staff-card');
    if (compactCards.length === 1) {
        const firstId = compactCards[0].dataset.staffId;
        if (firstId) {
            toggleCompactExpand(firstId);
        }
    }
});
    </script>
</body>
</html>