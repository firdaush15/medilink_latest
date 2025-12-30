<!-- resources\views\pharmacist\pharmacist_inventory.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Medication Inventory - MediLink</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css', 'resources/css/pharmacist/pharmacist_inventory.css'])
    <style>
        .batch-count {
            background: #e0f2fe;
            color: #0369a1;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }
        
        .batch-tooltip {
            position: relative;
            cursor: help;
        }
        
        .batch-details {
            display: none;
            position: absolute;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            min-width: 300px;
            left: 0;
            top: 100%;
            margin-top: 5px;
        }
        
        .batch-tooltip:hover .batch-details {
            display: block;
        }
        
        .batch-item {
            padding: 8px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 12px;
        }
        
        .batch-item:last-child {
            border-bottom: none;
        }
        
        .batch-number {
            font-weight: 600;
            color: #1f2937;
        }
        
        .batch-expiry {
            color: #6b7280;
        }
        
        .batch-expiry.critical {
            color: #dc2626;
            font-weight: 600;
        }
        
        .batch-expiry.warning {
            color: #f59e0b;
            font-weight: 600;
        }
    </style>
</head>
<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')

    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1>💊 Medication Inventory Management</h1>
            <div class="user-info">
                <span>{{ auth()->user()->name }}</span>
                <img src="{{ auth()->user()->profile_photo ?? asset('assets/default-avatar.png') }}" alt="Profile" class="profile-pic">
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="alert alert-success">
            <strong>✓</strong> {{ session('success') }}
        </div>
        @endif

        @if(session('info'))
        <div class="alert alert-info">
            <strong>ℹ️</strong> {{ session('info') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error">
            <strong>✗</strong> {{ session('error') }}
        </div>
        @endif

        <!-- Workflow Notice -->
        <div class="workflow-notice">
            <div class="notice-icon">📋</div>
            <div class="notice-content">
                <h3>Batch Tracking System</h3>
                <p><strong>✓ No duplicate medicines:</strong> Same medicine name/strength/form is stored once</p>
                <p><strong>✓ Multiple batches:</strong> Track different batches with unique expiry dates</p>
                <p><strong>✓ FEFO dispensing:</strong> System automatically uses oldest batches first</p>
                <p><strong>To add stock:</strong> Create a <a href="{{ route('pharmacist.receipts.create') }}">Stock Receipt</a> for new batches</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <h3>{{ $stats['total_items'] }}</h3>
                    <p>Unique Medicines</p>
                </div>
            </div>

            <div class="stat-card low-stock">
                <div class="stat-icon">⚠️</div>
                <div class="stat-info">
                    <h3>{{ $stats['low_stock'] }}</h3>
                    <p>Low Stock</p>
                </div>
                @if($stats['low_stock'] > 0)
                <a href="?status=low" class="stat-link">View →</a>
                @endif
            </div>

            <div class="stat-card out-stock">
                <div class="stat-icon">❌</div>
                <div class="stat-info">
                    <h3>{{ $stats['out_of_stock'] }}</h3>
                    <p>Out of Stock</p>
                </div>
                @if($stats['out_of_stock'] > 0)
                <a href="?status=Out of Stock" class="stat-link">View →</a>
                @endif
            </div>

            <div class="stat-card expiring">
                <div class="stat-icon">⏰</div>
                <div class="stat-info">
                    <h3>{{ $stats['expiring_soon'] }}</h3>
                    <p>With Expiring Batches</p>
                </div>
                @if($stats['expiring_soon'] > 0)
                <a href="?status=expiring" class="stat-link">View →</a>
                @endif
            </div>

            <div class="stat-card expired">
                <div class="stat-icon">🚫</div>
                <div class="stat-info">
                    <h3>{{ $stats['expired'] }}</h3>
                    <p>With Expired Batches</p>
                </div>
                @if($stats['expired'] > 0)
                <a href="?status=expired" class="stat-link">View →</a>
                @endif
            </div>

            <div class="stat-card value">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>RM {{ number_format($stats['total_value'], 2) }}</h3>
                    <p>Total Inventory Value</p>
                </div>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="actions-bar">
            <div class="left-actions">
                <a href="{{ route('pharmacist.inventory.create') }}" class="btn btn-secondary">
                    ➕ Register New Medicine
                </a>
                <a href="{{ route('pharmacist.receipts.create') }}" class="btn btn-success">
                    📥 Receive Stock (Add Batch)
                </a>
                <a href="{{ route('pharmacist.restock.create') }}" class="btn btn-primary">
                    📦 Create Restock Request
                </a>
                <a href="{{ route('pharmacist.inventory.low-stock-report') }}" class="btn btn-warning">
                    ⚠️ Low Stock Report
                </a>
            </div>
            <div class="right-actions">
                <a href="{{ route('pharmacist.inventory.export') }}" class="btn btn-secondary">
                    📥 Export CSV
                </a>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="filter-bar">
            <form method="GET" class="filter-form">
                <div class="search-box">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search medicine name, generic...">
                    <button type="submit" class="btn-search">🔍</button>
                </div>

                <select name="category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="Active" {{ $status == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="low" {{ $status == 'low' ? 'selected' : '' }}>Low Stock</option>
                    <option value="Out of Stock" {{ $status == 'Out of Stock' ? 'selected' : '' }}>Out of Stock</option>
                    <option value="expiring" {{ $status == 'expiring' ? 'selected' : '' }}>Expiring Batches</option>
                    <option value="expired" {{ $status == 'expired' ? 'selected' : '' }}>Expired Batches</option>
                    <option value="Discontinued" {{ $status == 'Discontinued' ? 'selected' : '' }}>Discontinued</option>
                </select>

                <select name="sort" onchange="this.form.submit()">
                    <option value="medicine_name" {{ $sortBy == 'medicine_name' ? 'selected' : '' }}>Sort: Name</option>
                    <option value="category" {{ $sortBy == 'category' ? 'selected' : '' }}>Sort: Category</option>
                    <option value="quantity_in_stock" {{ $sortBy == 'quantity_in_stock' ? 'selected' : '' }}>Sort: Stock</option>
                </select>

                @if($search || $category || $status)
                <a href="{{ route('pharmacist.inventory') }}" class="btn-reset">✖ Clear Filters</a>
                @endif
            </form>
        </div>

        <!-- Medicines Table -->
        <div class="inventory-table-container">
            @if($medicines->count() > 0)
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Medicine Name</th>
                        <th>Generic Name</th>
                        <th>Category</th>
                        <th>Form</th>
                        <th>Strength</th>
                        <th>Total Stock</th>
                        <th>Batches</th>
                        <th>Next Expiry</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($medicines as $medicine)
                    @php
                        $nextBatch = $medicine->getNextExpiringBatch();
                        $totalStock = $medicine->quantity_in_stock;
                        $batchCount = $medicine->activeBatches()->count();
                    @endphp
                    <tr class="medicine-row {{ $medicine->isLowStock() ? 'low-stock-row' : '' }}">
                        <td><input type="checkbox" class="medicine-checkbox" value="{{ $medicine->medicine_id }}"></td>
                        <td>
                            <strong>{{ $medicine->medicine_name }}</strong>
                            @if($medicine->brand_name)
                            <br><small class="text-muted">{{ $medicine->brand_name }}</small>
                            @endif
                        </td>
                        <td>{{ $medicine->generic_name ?? '-' }}</td>
                        <td><span class="badge category-badge">{{ $medicine->category }}</span></td>
                        <td>{{ $medicine->form }}</td>
                        <td>{{ $medicine->strength }}</td>
                        <td>
                            <div class="stock-info">
                                <span class="stock-number {{ $totalStock == 0 ? 'out-stock' : ($totalStock <= $medicine->reorder_level ? 'low-stock' : 'normal-stock') }}">
                                    {{ $totalStock }}
                                </span>
                                @if($totalStock > 0 && $totalStock <= $medicine->reorder_level)
                                <span class="warning-icon" title="Low Stock">⚠️</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="batch-tooltip">
                                <span class="batch-count">{{ $batchCount }} batch{{ $batchCount != 1 ? 'es' : '' }}</span>
                                @if($batchCount > 0)
                                <div class="batch-details">
                                    <strong>Active Batches (FEFO Order):</strong>
                                    @foreach($medicine->activeBatches as $batch)
                                    <div class="batch-item">
                                        <div class="batch-number">{{ $batch->batch_number }}</div>
                                        <div>Qty: {{ $batch->quantity }} units</div>
                                        <div class="batch-expiry {{ $batch->isExpiringCritical() ? 'critical' : ($batch->isExpiringSoon() ? 'warning' : '') }}">
                                            Exp: {{ $batch->expiry_date->format('d M Y') }}
                                            @if($batch->isExpiringCritical())
                                            🚨 {{ round($batch->getDaysUntilExpiry()) }} days
                                            @elseif($batch->isExpiringSoon())
                                            ⚠️ {{ round($batch->getMonthsUntilExpiry()) }} months
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($nextBatch)
                                @php
                                    $daysLeft = $nextBatch->getDaysUntilExpiry();
                                    $monthsLeft = $nextBatch->getMonthsUntilExpiry();
                                @endphp
                                @if($nextBatch->isExpired())
                                    <span class="expired-date">{{ $nextBatch->expiry_date->format('d M Y') }}</span>
                                    <small class="expired-warning">❌ EXPIRED</small>
                                @elseif($nextBatch->isExpiringCritical())
                                    <span class="critical-expiry-date">{{ $nextBatch->expiry_date->format('d M Y') }}</span>
                                    <small class="critical-expiry-warning">🚨 {{ round($daysLeft) }} days</small>
                                @elseif($nextBatch->isExpiringSoon())
                                    <span class="warning-expiry-date">{{ $nextBatch->expiry_date->format('d M Y') }}</span>
                                    <small class="warning-expiry-text">⚠️ {{ round($monthsLeft) }} months</small>
                                @else
                                    <span class="normal-expiry-date">{{ $nextBatch->expiry_date->format('d M Y') }}</span>
                                    <small class="safe-expiry-text">✓ {{ round($monthsLeft) }} months</small>
                                @endif
                            @else
                                <span class="text-muted">No active batches</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $medicine->status)) }}">
                                {{ $medicine->status }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('pharmacist.inventory.show', $medicine->medicine_id) }}" class="btn-action btn-view" title="View Details">
                                    👁️
                                </a>
                                <a href="{{ route('pharmacist.inventory.edit', $medicine->medicine_id) }}" class="btn-action btn-edit" title="Edit Info">
                                    ✏️
                                </a>
                                @if($medicine->isLowStock() || $medicine->isOutOfStock())
                                <a href="{{ route('pharmacist.restock.create', ['medicine_id' => $medicine->medicine_id]) }}" class="btn-action btn-restock" title="Request Restock">
                                    📦
                                </a>
                                @endif
                                <a href="{{ route('pharmacist.inventory.stock-history', $medicine->medicine_id) }}" class="btn-action btn-history" title="Stock History">
                                    📊
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination-container">
                {{ $medicines->appends(request()->query())->links() }}
            </div>
            @else
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <h3>No Medicines Found</h3>
                <p>Try adjusting your search or filters</p>
            </div>
            @endif
        </div>

        <!-- Bulk Actions -->
        <div id="bulk-actions" class="bulk-actions" style="display: none;">
            <span id="selected-count">0 items selected</span>
            <button onclick="bulkAction('discontinue')" class="btn btn-warning">Discontinue</button>
            <button onclick="bulkAction('activate')" class="btn btn-success">Activate</button>
        </div>
    </div>

    <script>
        // Select All Checkbox
        document.getElementById('select-all')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.medicine-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });

        // Individual Checkboxes
        document.querySelectorAll('.medicine-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActions);
        });

        function updateBulkActions() {
            const selected = document.querySelectorAll('.medicine-checkbox:checked');
            const bulkActions = document.getElementById('bulk-actions');
            const selectedCount = document.getElementById('selected-count');

            if (selected.length > 0) {
                bulkActions.style.display = 'flex';
                selectedCount.textContent = `${selected.length} item(s) selected`;
            } else {
                bulkActions.style.display = 'none';
            }
        }

        function bulkAction(action) {
            const selected = Array.from(document.querySelectorAll('.medicine-checkbox:checked')).map(cb => cb.value);

            if (selected.length === 0) {
                alert('Please select at least one medicine');
                return;
            }

            if (!confirm(`Are you sure you want to ${action.replace('_', ' ')} ${selected.length} medicine(s)?`)) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("pharmacist.inventory.bulk-update") }}';

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'medicine_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = action;
            form.appendChild(actionInput);

            document.body.appendChild(form);
            form.submit();
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>