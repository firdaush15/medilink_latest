<!-- resources/views/pharmacist/pharmacist_inventory.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Medication Inventory - MediLink</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css', 'resources/css/pharmacist/pharmacist_inventory.css'])
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

        @if(session('error'))
        <div class="alert alert-error">
            <strong>✗</strong> {{ session('error') }}
        </div>
        @endif

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <h3>{{ $stats['total_items'] }}</h3>
                    <p>Total Medicines</p>
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
                    <p>Expiring Soon (6 months)</p>
                </div>
                @if($stats['expiring_soon'] > 0)
                <a href="?status=expiring" class="stat-link">View →</a>
                @endif
            </div>

            <div class="stat-card expired">
                <div class="stat-icon">🚫</div>
                <div class="stat-info">
                    <h3>{{ $stats['expired'] }}</h3>
                    <p>Expired</p>
                </div>
                @if($stats['expired'] > 0)
                <a href="?status=Expired" class="stat-link">View →</a>
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
                <a href="{{ route('pharmacist.inventory.create') }}" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                    Add New Medicine
                </a>
                <a href="{{ route('pharmacist.inventory.low-stock-report') }}" class="btn btn-warning">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    Low Stock Report
                </a>
            </div>
            <div class="right-actions">
                <a href="{{ route('pharmacist.inventory.export') }}" class="btn btn-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Export CSV
                </a>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="filter-bar">
            <form method="GET" class="filter-form">
                <div class="search-box">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search medicine name, generic, batch number...">
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
                    <option value="expiring" {{ $status == 'expiring' ? 'selected' : '' }}>Expiring Soon (≤6 months)</option>
                    <option value="Expired" {{ $status == 'Expired' ? 'selected' : '' }}>Expired</option>
                    <option value="Discontinued" {{ $status == 'Discontinued' ? 'selected' : '' }}>Discontinued</option>
                </select>

                <select name="sort" onchange="this.form.submit()">
                    <option value="medicine_name" {{ $sortBy == 'medicine_name' ? 'selected' : '' }}>Sort: Name</option>
                    <option value="category" {{ $sortBy == 'category' ? 'selected' : '' }}>Sort: Category</option>
                    <option value="quantity_in_stock" {{ $sortBy == 'quantity_in_stock' ? 'selected' : '' }}>Sort: Stock</option>
                    <option value="expiry_date" {{ $sortBy == 'expiry_date' ? 'selected' : '' }}>Sort: Expiry Date</option>
                    <option value="unit_price" {{ $sortBy == 'unit_price' ? 'selected' : '' }}>Sort: Price</option>
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
                        <th>Stock</th>
                        <th>Reorder Level</th>
                        <th>Expiry Date</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($medicines as $medicine)
                    <tr class="medicine-row {{ $medicine->isLowStock() ? 'low-stock-row' : '' }} {{ $medicine->isExpired() ? 'expired-row' : '' }}">
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
                                <span class="stock-number {{ $medicine->isOutOfStock() ? 'out-stock' : ($medicine->isLowStock() ? 'low-stock' : 'normal-stock') }}">
                                    {{ $medicine->quantity_in_stock }}
                                </span>
                                @if($medicine->isLowStock() && !$medicine->isOutOfStock())
                                <span class="warning-icon" title="Low Stock">⚠️</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $medicine->reorder_level }}</td>
                        <td>
                            @php
                                $daysLeft = $medicine->getDaysUntilExpiry();
                                $monthsLeft = $medicine->getMonthsUntilExpiry();
                                
                                // Round to whole numbers for clean display
                                $daysLeftRounded = round($daysLeft);
                                $monthsLeftRounded = round($monthsLeft);
                            @endphp

                            @if($medicine->isExpired())
                                <!-- EXPIRED - Already past expiry date -->
                                <span class="expired-date">
                                    {{ $medicine->expiry_date->format('d M Y') }}
                                </span>
                                <small class="expired-warning">❌ EXPIRED</small>
                            
                            @elseif($medicine->isExpiringCritical())
                                <!-- CRITICAL - Red (≤90 days / 3 months) -->
                                <span class="critical-expiry-date">
                                    {{ $medicine->expiry_date->format('d M Y') }}
                                </span>
                                <small class="critical-expiry-warning">
                                    @if($monthsLeftRounded > 0)
                                        🚨 {{ $daysLeftRounded }} days ({{ $monthsLeftRounded }} month{{ $monthsLeftRounded != 1 ? 's' : '' }})
                                    @else
                                        🚨 {{ $daysLeftRounded }} day{{ $daysLeftRounded != 1 ? 's' : '' }} left
                                    @endif
                                </small>
                            
                            @elseif($medicine->isExpiringSoon())
                                <!-- WARNING - Orange (91-180 days / 3-6 months) -->
                                <span class="warning-expiry-date">
                                    {{ $medicine->expiry_date->format('d M Y') }}
                                </span>
                                <small class="warning-expiry-text">
                                    ⚠️ {{ $monthsLeftRounded }} month{{ $monthsLeftRounded != 1 ? 's' : '' }} left
                                </small>
                            
                            @else
                                <!-- SAFE - Green (>180 days / 6+ months) -->
                                <span class="normal-expiry-date">
                                    {{ $medicine->expiry_date->format('d M Y') }}
                                </span>
                                <small class="safe-expiry-text">
                                    ✓ {{ $monthsLeftRounded }} month{{ $monthsLeftRounded != 1 ? 's' : '' }}
                                </small>
                            @endif
                        </td>
                        <td>RM {{ number_format($medicine->unit_price, 2) }}</td>
                        <td>
                            <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $medicine->status)) }}">
                                {{ $medicine->status }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('pharmacist.inventory.edit', $medicine->medicine_id) }}" class="btn-action btn-edit" title="Edit">
                                    ✏️
                                </a>
                                <button onclick="openStockModal({{ $medicine->medicine_id }}, '{{ $medicine->medicine_name }}', {{ $medicine->quantity_in_stock }})" class="btn-action btn-stock" title="Adjust Stock">
                                    📦
                                </button>
                                <a href="{{ route('pharmacist.inventory.stock-history', $medicine->medicine_id) }}" class="btn-action btn-history" title="Stock History">
                                    📊
                                </a>
                                @if(!$medicine->isExpired())
                                <form action="{{ route('pharmacist.inventory.mark-expired', $medicine->medicine_id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Mark this medicine as expired?');">
                                    @csrf
                                    <button type="submit" class="btn-action btn-expire" title="Mark Expired">
                                        🚫
                                    </button>
                                </form>
                                @endif
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

        <!-- Bulk Actions (appears when items selected) -->
        <div id="bulk-actions" class="bulk-actions" style="display: none;">
            <span id="selected-count">0 items selected</span>
            <button onclick="bulkAction('mark_expired')" class="btn btn-danger">Mark as Expired</button>
            <button onclick="bulkAction('discontinue')" class="btn btn-warning">Discontinue</button>
            <button onclick="bulkAction('activate')" class="btn btn-success">Activate</button>
        </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <div id="stockModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeStockModal()">&times;</span>
            <h2>Adjust Stock</h2>
            <form id="stockForm" method="POST">
                @csrf
                <input type="hidden" id="stock_medicine_id">
                
                <div class="form-group">
                    <label><strong>Medicine:</strong></label>
                    <p id="stock_medicine_name" class="medicine-name-display"></p>
                </div>

                <div class="form-group">
                    <label><strong>Current Stock:</strong></label>
                    <p id="current_stock" class="current-stock-display"></p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Adjustment Type <span class="required">*</span></label>
                        <select name="adjustment_type" required>
                            <option value="add">Add Stock</option>
                            <option value="reduce">Reduce Stock</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Quantity <span class="required">*</span></label>
                        <input type="number" name="quantity" min="1" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Reason <span class="required">*</span></label>
                    <select name="reason" required>
                        <option value="purchase">Purchase/Delivery</option>
                        <option value="return">Customer Return</option>
                        <option value="damaged">Damaged/Broken</option>
                        <option value="expired">Expired</option>
                        <option value="dispensed">Dispensed (Manual Entry)</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" rows="3" placeholder="Optional notes about this adjustment..."></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" onclick="closeStockModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Stock Modal
        function openStockModal(medicineId, medicineName, currentStock) {
            document.getElementById('stock_medicine_id').value = medicineId;
            document.getElementById('stock_medicine_name').textContent = medicineName;
            document.getElementById('current_stock').textContent = currentStock + ' units';
            document.getElementById('stockForm').action = `/pharmacist/inventory/${medicineId}/adjust-stock`;
            document.getElementById('stockModal').style.display = 'block';
        }

        function closeStockModal() {
            document.getElementById('stockModal').style.display = 'none';
            document.getElementById('stockForm').reset();
        }

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

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('stockModal');
            if (event.target == modal) {
                closeStockModal();
            }
        }
    </script>
</body>
</html>