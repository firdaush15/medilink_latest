<!-- resources/views/pharmacist/pharmacist_reports.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reports & Analytics - MediLink</title>
    @vite(['resources/css/sidebar.css', 'resources/css/pharmacist/pharmacist_reports.css'])
</head>
<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')

    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1>📊 Reports & Analytics</h1>
            <div class="user-info">
                <span>{{ auth()->user()->name }}</span>
                <img src="{{ auth()->user()->profile_photo ?? asset('assets/default-avatar.png') }}" alt="Profile" class="profile-pic">
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="stats-overview">
            <div class="stat-box">
                <div class="stat-icon">💊</div>
                <div class="stat-info">
                    <h3>{{ number_format($stats['prescriptions']['count']) }}</h3>
                    <p>Prescriptions This Month</p>
                    <span class="stat-change {{ $stats['prescriptions']['change'] >= 0 ? 'positive' : 'negative' }}">
                        {{ $stats['prescriptions']['change'] >= 0 ? '+' : '' }}{{ $stats['prescriptions']['change'] }}%
                    </span>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3>{{ number_format($stats['verified']['count']) }}</h3>
                    <p>Verified Prescriptions</p>
                    <span class="stat-change {{ $stats['verified']['change'] >= 0 ? 'positive' : 'negative' }}">
                        {{ $stats['verified']['change'] >= 0 ? '+' : '' }}{{ $stats['verified']['change'] }}%
                    </span>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>RM {{ number_format($stats['revenue']['amount'], 2) }}</h3>
                    <p>Total Revenue</p>
                    <span class="stat-change {{ $stats['revenue']['change'] >= 0 ? 'positive' : 'negative' }}">
                        {{ $stats['revenue']['change'] >= 0 ? '+' : '' }}{{ $stats['revenue']['change'] }}%
                    </span>
                </div>
            </div>

            <div class="stat-box">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <h3>{{ number_format($stats['items_dispensed']['count']) }}</h3>
                    <p>Items Dispensed</p>
                    <span class="stat-change {{ $stats['items_dispensed']['change'] >= 0 ? 'positive' : 'neutral' }}">
                        {{ $stats['items_dispensed']['change'] >= 0 ? '+' : '' }}{{ $stats['items_dispensed']['change'] }}%
                    </span>
                </div>
            </div>
        </div>

        <!-- Report Categories -->
        <div class="report-categories">
            <h2>📋 Available Reports</h2>
            
            <div class="categories-grid">
                <!-- Inventory Reports -->
                <div class="category-card">
                    <div class="category-header">
                        <span class="category-icon">📦</span>
                        <h3>Inventory Reports</h3>
                    </div>
                    <p class="category-description">Stock levels, expiry tracking, and reorder analysis</p>
                    <div class="report-list">
                        <a href="{{ route('pharmacist.inventory') }}" class="report-item">
                            <span>📊 Current Inventory Status ({{ number_format($inventory_counts['total']) }} items)</span>
                            <span class="report-arrow">→</span>
                        </a>
                        <a href="{{ route('pharmacist.inventory.low-stock-report') }}" class="report-item">
                            <span>⚠️ Low Stock Report ({{ $inventory_counts['low_stock'] }} items)</span>
                            <span class="report-arrow">→</span>
                        </a>
                        <a href="{{ route('pharmacist.inventory.export') }}?status=Expiring Soon" class="report-item">
                            <span>⏰ Expiring Medicines ({{ $inventory_counts['expiring_soon'] }} items)</span>
                            <span class="report-arrow">→</span>
                        </a>
                        <a href="{{ route('pharmacist.inventory.export') }}?status=Expired" class="report-item">
                            <span>🚫 Expired Medicines Report ({{ $inventory_counts['expired'] }} items)</span>
                            <span class="report-arrow">→</span>
                        </a>
                    </div>
                </div>

                <!-- Prescription Reports -->
                <div class="category-card">
                    <div class="category-header">
                        <span class="category-icon">💊</span>
                        <h3>Prescription Reports</h3>
                    </div>
                    <p class="category-description">Verification, dispensing, and rejection analytics</p>
                    <div class="report-list">
                        <a href="{{ route('pharmacist.prescriptions') }}?status=pending" class="report-item">
                            <span>⏳ Pending Verifications ({{ $prescription_counts['pending'] }})</span>
                            <span class="report-arrow">→</span>
                        </a>
                        <a href="{{ route('pharmacist.prescriptions') }}?status=dispensed" class="report-item">
                            <span>✅ Dispensed This Month ({{ $prescription_counts['dispensed'] }})</span>
                            <span class="report-arrow">→</span>
                        </a>
                        <a href="{{ route('pharmacist.prescriptions') }}?status=rejected" class="report-item">
                            <span>❌ Rejected This Month ({{ $prescription_counts['rejected'] }})</span>
                            <span class="report-arrow">→</span>
                        </a>
                        <button onclick="generateReport('prescription-summary')" class="report-item">
                            <span>📈 Monthly Summary</span>
                            <span class="report-arrow">→</span>
                        </button>
                    </div>
                </div>

                <!-- Financial Reports -->
                <div class="category-card">
                    <div class="category-header">
                        <span class="category-icon">💰</span>
                        <h3>Financial Reports</h3>
                    </div>
                    <p class="category-description">Revenue, sales, and inventory value analysis</p>
                    <div class="report-list">
                        <button onclick="generateReport('sales-summary')" class="report-item">
                            <span>💵 Sales Summary</span>
                            <span class="report-arrow">→</span>
                        </button>
                        <button onclick="generateReport('inventory-value')" class="report-item">
                            <span>💎 Inventory Valuation</span>
                            <span class="report-arrow">→</span>
                        </button>
                        <button onclick="generateReport('revenue-analysis')" class="report-item">
                            <span>📊 Revenue Analysis</span>
                            <span class="report-arrow">→</span>
                        </button>
                        <button onclick="alert('Coming soon!')" class="report-item">
                            <span>📉 Cost Analysis</span>
                            <span class="report-arrow">→</span>
                        </button>
                    </div>
                </div>

                <!-- Compliance Reports -->
                <div class="category-card">
                    <div class="category-header">
                        <span class="category-icon">📋</span>
                        <h3>Compliance & Audit</h3>
                    </div>
                    <p class="category-description">Regulatory compliance and audit trails</p>
                    <div class="report-list">
                        <button onclick="generateReport('controlled-substances')" class="report-item">
                            <span>🔒 Controlled Substances Log</span>
                            <span class="report-arrow">→</span>
                        </button>
                        <button onclick="generateReport('allergy-checks')" class="report-item">
                            <span>🚨 Allergy Verification Log</span>
                            <span class="report-arrow">→</span>
                        </button>
                        <button onclick="alert('Coming soon!')" class="report-item">
                            <span>📝 Audit Trail</span>
                            <span class="report-arrow">→</span>
                        </button>
                        <button onclick="alert('Coming soon!')" class="report-item">
                            <span>⚠️ Safety Incidents</span>
                            <span class="report-arrow">→</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Report Generator -->
        <div class="custom-report-section">
            <h2>🔧 Custom Report Generator</h2>
            <div class="custom-report-card">
                <form id="customReportForm" action="{{ route('pharmacist.reports.generate') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label>Report Type</label>
                            <select name="report_type" id="reportType" required>
                                <option value="">-- Select Type --</option>
                                <option value="inventory">Inventory Report</option>
                                <option value="prescriptions">Prescription Report</option>
                                <option value="sales">Sales Report</option>
                                <option value="stock-movements">Stock Movements</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Date Range</label>
                            <select name="date_range" id="dateRange" required>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month" selected>This Month</option>
                                <option value="quarter">This Quarter</option>
                                <option value="year">This Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>

                        <div class="form-group" id="customDateRange" style="display: none;">
                            <label>From Date</label>
                            <input type="date" name="start_date" id="startDate">
                        </div>

                        <div class="form-group" id="customDateRange2" style="display: none;">
                            <label>To Date</label>
                            <input type="date" name="end_date" id="endDate">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Format</label>
                            <select name="format" id="format" required>
                                <option value="csv">CSV Spreadsheet</option>
                                <option value="excel">Excel Workbook</option>
                                <option value="pdf">PDF Document</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Group By</label>
                            <select name="group_by" id="groupBy">
                                <option value="">None</option>
                                <option value="category">Category</option>
                                <option value="supplier">Supplier</option>
                                <option value="doctor">Doctor</option>
                                <option value="date">Date</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            📊 Generate Report
                        </button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // ========================================
        // DATE RANGE TOGGLE
        // ========================================
        document.getElementById('dateRange').addEventListener('change', function() {
            const customRanges = document.querySelectorAll('#customDateRange, #customDateRange2');
            if (this.value === 'custom') {
                customRanges.forEach(el => el.style.display = 'block');
            } else {
                customRanges.forEach(el => el.style.display = 'none');
            }
        });

        // ========================================
        // GENERATE PREDEFINED REPORT
        // ========================================
        function generateReport(type) {
            alert(`Generating ${type} report...\n\nThis feature will be available soon!`);
            // TODO: Implement actual report generation
        }
    </script>
    @vite(['resources/js/sidebar.js'])
</body>
</html>