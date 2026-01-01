<!--admin_pharmacyInventory.blade.php-->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Inventory - MediLink Admin</title>
    @vite(['resources/css/admin/admin_sidebar.css', 'resources/css/admin/admin_dashboard.css'])
    <style>
        .action-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .action-header h2 {
            margin: 0;
            font-size: 24px;
            color: #2c5282;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons a {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #2d3748;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        .btn-primary {
            background: #3182ce;
            color: white;
        }

        .btn-primary:hover {
            background: #2c5282;
        }

        .critical-alert {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            border-left: 4px solid #d32f2f;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .critical-alert .icon {
            font-size: 32px;
        }

        .critical-alert .content h3 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #c62828;
        }

        .critical-alert .content p {
            margin: 0;
            color: #666;
        }

        .critical-alert .action {
            margin-left: auto;
            padding: 8px 20px;
            background: white;
            border-radius: 6px;
            text-decoration: none;
            color: #d32f2f;
            font-weight: 600;
        }

        .filter-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .filter-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            min-width: 180px;
        }

        .btn-clear {
            padding: 10px 20px;
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-clear:hover {
            background: #edf2f7;
        }

        .medicine-name-col {
            min-width: 200px;
        }

        .medicine-name-col strong {
            display: block;
            font-size: 15px;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .brand-badge {
            display: inline-block;
            padding: 2px 8px;
            background: #e6fffa;
            color: #047857;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .controlled-badge {
            display: inline-block;
            padding: 2px 8px;
            background: #fee;
            color: #c53030;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 5px;
        }

        .category-tag {
            display: inline-block;
            padding: 4px 12px;
            background: #ebf4ff;
            color: #2c5282;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
        }

        .form-strength {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .form-badge {
            display: inline-block;
            padding: 3px 10px;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .strength-text {
            font-size: 13px;
            color: #4a5568;
            font-weight: 600;
        }

        /* Stock number colors */
        .stock-number {
            font-weight: 700;
            font-size: 18px;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .stock-number.normal-stock {
            color: #22543d;
            background: #c6f6d5;
        }

        .stock-number.low-stock {
            color: #7c2d12;
            background: #feebc8;
        }

        .stock-number.out-stock {
            color: #742a2a;
            background: #fed7d7;
        }

        .warning-icon {
            font-size: 16px;
            margin-left: 4px;
        }

        .stock-display {
            min-width: 150px;
        }

        .stock-bar-outer {
            height: 8px;
            background: #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 6px;
        }

        .stock-bar-inner {
            height: 100%;
            transition: width 0.3s ease;
        }

        .stock-text {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
        }

        .stock-current {
            font-weight: 700;
            color: #2d3748;
        }

        .stock-separator {
            color: #cbd5e0;
        }

        .stock-reorder {
            color: #a0aec0;
        }

        .status-active {
            background: #c6f6d5;
            color: #22543d;
        }

        .status-low {
            background: #feebc8;
            color: #7c2d12;
        }

        .status-out {
            background: #fed7d7;
            color: #742a2a;
        }

        .status-expired {
            background: #e2e8f0;
            color: #4a5568;
        }

        .expiry-col {
            min-width: 140px;
        }

        .expiry-date-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .expiry-critical {
            background: #ffebee;
            color: #c62828;
        }

        .expiry-warning {
            background: #fff3e0;
            color: #e65100;
        }

        .expiry-safe {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .expiry-expired {
            background: #f5f5f5;
            color: #757575;
            text-decoration: line-through;
        }

        .expiry-none {
            background: #f5f5f5;
            color: #999;
            font-style: italic;
        }

        .days-remaining {
            font-size: 11px;
            color: #d32f2f;
            font-weight: 600;
            display: block;
        }

        .action-btn {
            padding: 6px 12px;
            background: #3182ce;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            display: inline-block;
        }

        .action-btn:hover {
            background: #2c5282;
        }

        .empty-row {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-icon {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
        }

        .critical-row-bg {
            background: #fff5f5 !important;
        }

        .movements-table {
            margin-top: 20px;
        }

        .movement-type-in {
            background: #c6f6d5;
            color: #22543d;
        }

        .movement-type-out {
            background: #bee3f8;
            color: #2c5282;
        }

        .quantity-positive {
            color: #22543d;
            font-weight: 600;
        }

        .quantity-negative {
            color: #c53030;
            font-weight: 600;
        }
    </style>
</head>

<body>
    @include('admin.sidebar.admin_sidebar')

    <div class="main">
        <!-- Page Header with Actions -->
        <div class="action-header">
            <h2>💊 Pharmacy Inventory Management</h2>
            <div class="action-buttons">
                <a href="{{ route('admin.pharmacy-inventory.reports') }}" class="btn-secondary">
                    📊 View Reports
                </a>
                <a href="{{ route('admin.pharmacy-inventory.analytics') }}" class="btn-secondary">
                    📈 Analytics
                </a>
                <a href="{{ route('admin.pharmacy-inventory.export') }}" class="btn-primary">
                    📥 Export Data
                </a>
            </div>
        </div>

        <!-- Stats Cards (using admin dashboard card style) -->
        <div class="header">
            <div class="card">
                <h3>📦 Total Medicines</h3>
                <p>{{ $totalMedicines }}</p>
                <span>Active inventory items</span>
            </div>

            <div class="card">
                <h3>⚠️ Low Stock Alerts</h3>
                <p style="color: #ff9800;">{{ $lowStockCount }}</p>
                <span>Need reordering</span>
            </div>

            <div class="card">
                <h3>🚨 Out of Stock</h3>
                <p style="color: #d32f2f;">{{ $outOfStockCount }}</p>
                <span>Critical restocking</span>
            </div>

            <div class="card">
                <h3>⏰ Expiring Soon</h3>
                <p style="color: #f57c00;">{{ $expiringCount }}</p>
                <span>Within 90 days</span>
            </div>
        </div>

        <!-- Critical Alert Banner -->
        @if(isset($criticalAlerts) && $criticalAlerts->count() > 0)
        <div class="critical-alert">
            <div class="icon">🚨</div>
            <div class="content">
                <h3>Critical Inventory Issues Require Immediate Attention</h3>
                <p>{{ $criticalAlerts->count() }} medicines need urgent restocking or replacement</p>
            </div>
            <a href="#medicines-table" class="action">Review Now →</a>
        </div>
        @endif

        <!-- Filter and Search Section -->
        <div class="filter-box">
            <input type="text"
                id="searchMedicine"
                class="search-input"
                placeholder="🔍 Search by medicine name, category, or supplier..."
                value="{{ request('search') }}">

            <div class="filter-row">
                <select id="filterStatus" class="filter-select">
                    <option value="">All Status</option>
                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Low Stock" {{ request('status') == 'Low Stock' ? 'selected' : '' }}>Low Stock</option>
                    <option value="Out of Stock" {{ request('status') == 'Out of Stock' ? 'selected' : '' }}>Out of Stock</option>
                    <option value="Expired" {{ request('status') == 'Expired' ? 'selected' : '' }}>Expired</option>
                </select>

                <select id="filterCategory" class="filter-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                        {{ $category }}
                    </option>
                    @endforeach
                </select>

                <select id="filterExpiry" class="filter-select">
                    <option value="">All Expiry</option>
                    <option value="critical" {{ request('expiry') == 'critical' ? 'selected' : '' }}>Critical (≤90 days)</option>
                    <option value="warning" {{ request('expiry') == 'warning' ? 'selected' : '' }}>Warning (91-180 days)</option>
                    <option value="safe" {{ request('expiry') == 'safe' ? 'selected' : '' }}>Safe (>180 days)</option>
                </select>

                <button class="btn-clear" onclick="clearFilters()">Clear Filters</button>
            </div>
        </div>

        <!-- Medicines Inventory Table -->
        <div class="section" id="medicines-table">
            <h3>📦 Inventory Overview ({{ $medicines->total() }} medicines)</h3>
            <table>
                <tr>
                    <th>Medicine Name</th>
                    <th>Category</th>
                    <th>Form & Strength</th>
                    <th>Stock Level</th>
                    <th>Status</th>
                    <th>Expiry Date</th>
                    <th>Actions</th>
                </tr>
                @forelse($medicines as $medicine)
                <tr class="{{ $medicine->status == 'Out of Stock' ? 'critical-row-bg' : '' }}">
                    <td>
                        <div class="medicine-name-col">
                            <strong>{{ $medicine->medicine_name }}</strong>
                            @if($medicine->brand_name)
                            <span class="brand-badge">{{ $medicine->brand_name }}</span>
                            @endif
                            @if($medicine->is_controlled_substance)
                            <span class="controlled-badge">🔒 Controlled</span>
                            @endif
                        </div>
                    </td>

                    <td>
                        <span class="category-tag">{{ $medicine->category }}</span>
                    </td>

                    <td>
                        <div class="form-strength">
                            <span class="form-badge">{{ $medicine->form }}</span>
                            <span class="strength-text">{{ $medicine->strength }}</span>
                        </div>
                    </td>

                    <td>
                        <div class="stock-display">
                            <div class="stock-bar-outer">
                                @php
                                // Calculate percentage for visual bar
                                $percentage = $medicine->reorder_level > 0
                                ? ($medicine->quantity_in_stock / $medicine->reorder_level) * 100
                                : 0;
                                $barColor = $percentage > 100 ? '#4caf50' :
                                ($percentage > 50 ? '#ff9800' : '#f44336');
                                @endphp
                                <div class="stock-bar-inner"
                                    style="width: {{ min($percentage, 100) }}%; background: {{ $barColor }};"></div>
                            </div>
                            <div class="stock-text">
                                <span class="stock-current">{{ $medicine->quantity_in_stock }}</span>
                                <span class="stock-separator">/</span>
                                <span class="stock-reorder">{{ $medicine->reorder_level }}</span>
                            </div>
                        </div>
                    </td>

                    <td>
                        @php
                        $statusClass = match($medicine->status) {
                        'Active' => 'status-active',
                        'Low Stock' => 'status-low',
                        'Out of Stock' => 'status-out',
                        'Expired' => 'status-expired',
                        default => ''
                        };
                        @endphp
                        <span style="padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;"
                            class="{{ $statusClass }}">
                            {{ $medicine->status }}
                        </span>
                    </td>

                    <td>
                        <div class="expiry-col">
                            @php
                            // ✅ FIXED: Get expiry date from nearest expiring batch
                            $nearestBatch = $medicine->activeBatches->first();
                            $expiryClass = 'expiry-safe';
                            
                            if (!$nearestBatch) {
                                $expiryClass = 'expiry-none';
                            } elseif ($medicine->isExpired()) {
                                $expiryClass = 'expiry-expired';
                            } elseif ($medicine->isExpiringCritical()) {
                                $expiryClass = 'expiry-critical';
                            } elseif ($medicine->isExpiringSoon()) {
                                $expiryClass = 'expiry-warning';
                            }
                            @endphp
                            
                            @if($nearestBatch)
                                <span class="expiry-date-badge {{ $expiryClass }}">
                                    {{ $nearestBatch->expiry_date->format('M d, Y') }}
                                </span>
                                @if($medicine->isExpiringCritical())
                                <span class="days-remaining">
                                    ⚠️ {{ $medicine->getDaysUntilExpiry() }} days left
                                </span>
                                @elseif($medicine->isExpiringSoon())
                                <span class="days-remaining" style="color: #e65100;">
                                    📅 {{ $medicine->getMonthsUntilExpiry() }} months left
                                </span>
                                @endif
                            @else
                                <span class="expiry-date-badge expiry-none">
                                    No batches available
                                </span>
                            @endif
                        </div>
                    </td>

                    <td>
                        <a href="{{ route('admin.pharmacy-inventory.show', $medicine->medicine_id) }}"
                            class="action-btn">
                            View Details
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-row">
                        <span class="empty-icon">📦</span>
                        <p>No medicines found matching your filters</p>
                    </td>
                </tr>
                @endforelse
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <p>
                    Showing {{ $medicines->firstItem() }}–{{ $medicines->lastItem() }}
                    of {{ $medicines->total() }} medicines
                </p>

                <div class="pages">
                    @if ($medicines->onFirstPage())
                    <button disabled>&laquo; Prev</button>
                    @else
                    <a href="{{ $medicines->previousPageUrl() }}">
                        <button>&laquo; Prev</button>
                    </a>
                    @endif

                    @foreach ($medicines->getUrlRange(1, $medicines->lastPage()) as $page => $url)
                    @if ($page == $medicines->currentPage())
                    <button class="active">{{ $page }}</button>
                    @else
                    <a href="{{ $url }}">
                        <button>{{ $page }}</button>
                    </a>
                    @endif
                    @endforeach

                    @if ($medicines->hasMorePages())
                    <a href="{{ $medicines->nextPageUrl() }}">
                        <button>Next &raquo;</button>
                    </a>
                    @else
                    <button disabled>Next &raquo;</button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Stock Movements -->
        <div class="section movements-table">
            <h3>📊 Recent Stock Movements</h3>
            <table>
                <tr>
                    <th>Time</th>
                    <th>Medicine</th>
                    <th>Movement Type</th>
                    <th>Quantity</th>
                    <th>Balance After</th>
                    <th>Pharmacist</th>
                </tr>
                @forelse($recentMovements as $movement)
                <tr>
                    <td>{{ $movement->created_at->diffForHumans() }}</td>
                    <td>{{ $movement->medicine->medicine_name }}</td>
                    <td>
                        <span style="padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 600;"
                            class="{{ $movement->movement_type == 'Stock In' ? 'movement-type-in' : 'movement-type-out' }}">
                            {{ $movement->movement_type }}
                        </span>
                    </td>
                    <td class="{{ $movement->quantity > 0 ? 'quantity-positive' : 'quantity-negative' }}">
                        {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                    </td>
                    <td>{{ $movement->balance_after }}</td>
                    <td>{{ $movement->pharmacist->user->name ?? 'System' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">
                        No recent stock movements
                    </td>
                </tr>
                @endforelse
            </table>
        </div>
    </div>

    <script>
        // Search on Enter key
        document.getElementById('searchMedicine').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });

        // Auto-apply filters on select change
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', applyFilters);
        });

        function applyFilters() {
            const search = document.getElementById('searchMedicine').value;
            const status = document.getElementById('filterStatus').value;
            const category = document.getElementById('filterCategory').value;
            const expiry = document.getElementById('filterExpiry').value;

            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (status) params.append('status', status);
            if (category) params.append('category', category);
            if (expiry) params.append('expiry', expiry);

            window.location.href = `{{ route('admin.pharmacy-inventory.index') }}?${params.toString()}`;
        }

        function clearFilters() {
            window.location.href = '{{ route("admin.pharmacy-inventory.index") }}';
        }

        // Auto-refresh every 2 minutes for real-time updates
        setInterval(() => {
            location.reload();
        }, 120000);
    </script>

</body>

</html>