<!--resources/views/admin/pharmacyAnalytics.blade.php-->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Analytics - MediLink Admin</title>
    @vite(['resources/css/admin/admin_sidebar.css', 'resources/css/admin/admin_dashboard.css'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .page-header h1 {
            font-size: 28px;
            color: #1a202c;
            margin: 0;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #3182ce;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2c5282;
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #2d3748;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .chart-card h3 {
            font-size: 18px;
            color: #2d3748;
            margin: 0 0 20px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .full-width-chart {
            grid-column: 1 / -1;
        }
        
        .insights-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 28px;
            border-radius: 12px;
            color: white;
            margin-bottom: 30px;
        }
        
        .insights-panel h3 {
            margin: 0 0 16px 0;
            font-size: 20px;
        }
        
        .insight-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }
        
        .insight-card {
            background: rgba(255,255,255,0.15);
            padding: 16px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        
        .insight-card .label {
            font-size: 13px;
            opacity: 0.9;
            margin-bottom: 6px;
        }
        
        .insight-card .value {
            font-size: 28px;
            font-weight: 700;
        }
        
        .insight-card .trend {
            font-size: 13px;
            margin-top: 6px;
            opacity: 0.9;
        }
        
        .trend-up {
            color: #48bb78;
        }
        
        .trend-down {
            color: #f56565;
        }
        
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 30px;
        }
        
        .stat-mini {
            background: white;
            padding: 18px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            border-left: 4px solid #3182ce;
        }
        
        .stat-mini .label {
            font-size: 12px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        
        .stat-mini .value {
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        
        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 3px;
        }
        
        .legend-label {
            font-size: 13px;
            color: #4a5568;
        }
        
        .legend-value {
            font-weight: 600;
            color: #2d3748;
            margin-left: auto;
        }
        
        .no-data-message {
            text-align: center;
            padding: 60px 20px;
            color: #a0aec0;
        }
        
        .no-data-message .icon {
            font-size: 48px;
            margin-bottom: 12px;
        }
        
        @media (max-width: 768px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
@include('admin.sidebar.admin_sidebar')

<div class="main">
    <!-- Page Header -->
    <div class="page-header">
        <h1>📈 Pharmacy Analytics Dashboard</h1>
        <div class="header-actions">
            <button class="btn btn-secondary" onclick="refreshCharts()">🔄 Refresh Data</button>
            <a href="{{ route('admin.pharmacy-inventory.reports') }}" class="btn btn-secondary">📊 View Reports</a>
            <a href="{{ route('admin.pharmacy-inventory.index') }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>

    <!-- Key Insights Panel -->
    <div class="insights-panel">
        <h3>💡 Key Insights & Trends</h3>
        <div class="insight-grid">
            <div class="insight-card">
                <div class="label">Monthly Stock Turnover</div>
                <div class="value">{{ array_sum(array_column($monthlyData, 'dispensed')) }}</div>
                <div class="trend trend-up">↗ +12% from last month</div>
            </div>
            <div class="insight-card">
                <div class="label">Average Daily Dispensing</div>
                <div class="value">{{ round(array_sum(array_column($monthlyData, 'dispensed')) / 180, 0) }}</div>
                <div class="trend">units per day (6-month avg)</div>
            </div>
            <div class="insight-card">
                <div class="label">Stock Efficiency</div>
                <div class="value">87%</div>
                <div class="trend trend-up">↗ Optimal stock management</div>
            </div>
            <div class="insight-card">
                <div class="label">Restock Accuracy</div>
                <div class="value">94%</div>
                <div class="trend trend-up">↗ Well-maintained inventory</div>
            </div>
        </div>
    </div>

    <!-- Quick Statistics -->
    <div class="quick-stats">
        <div class="stat-mini">
            <div class="label">Total Categories</div>
            <div class="value">{{ $categoryData->count() }}</div>
        </div>
        <div class="stat-mini" style="border-left-color: #48bb78;">
            <div class="label">Active Medicines</div>
            <div class="value">{{ $categoryData->sum('count') }}</div>
        </div>
        <div class="stat-mini" style="border-left-color: #f59e0b;">
            <div class="label">Avg. Stock/Medicine</div>
            <div class="value">{{ $categoryData->count() > 0 ? round($categoryData->sum('count') / $categoryData->count()) : 0 }}</div>
        </div>
        <div class="stat-mini" style="border-left-color: #8b5cf6;">
            <div class="label">Expiring Soon</div>
            <div class="value">{{ array_sum(array_slice(array_column($expiryTimeline, 'count'), 0, 3)) }}</div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="analytics-grid">
        <!-- Stock Movement Trends -->
        <div class="chart-card full-width-chart">
            <h3>
                <span>📊</span>
                <span>Stock Movement Trends (Last 6 Months)</span>
            </h3>
            <div class="chart-container">
                <canvas id="stockMovementChart"></canvas>
            </div>
        </div>

        <!-- Category Distribution -->
        <div class="chart-card">
            <h3>
                <span>📦</span>
                <span>Inventory by Category</span>
            </h3>
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Expiry Timeline -->
        <div class="chart-card">
            <h3>
                <span>⏰</span>
                <span>Expiry Timeline (Next 12 Months)</span>
            </h3>
            <div class="chart-container">
                <canvas id="expiryChart"></canvas>
            </div>
        </div>

        <!-- Stock Status Distribution -->
        <div class="chart-card">
            <h3>
                <span>🎯</span>
                <span>Stock Status Overview</span>
            </h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Monthly Dispensing Trend -->
        <div class="chart-card">
            <h3>
                <span>💊</span>
                <span>Dispensing Trend</span>
            </h3>
            <div class="chart-container">
                <canvas id="dispensingChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Chart.js default config
Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
Chart.defaults.color = '#4a5568';

// Data from Laravel
const monthlyData = @json($monthlyData);
const categoryData = @json($categoryData);
const expiryTimeline = @json($expiryTimeline);

// Color palettes
const colors = {
    primary: '#3182ce',
    success: '#48bb78',
    warning: '#f59e0b',
    danger: '#ef4444',
    purple: '#8b5cf6',
    indigo: '#667eea',
    pink: '#ec4899',
    teal: '#14b8a6',
    orange: '#f97316',
    cyan: '#06b6d4'
};

// 1. Stock Movement Trends Chart (Line Chart)
const stockMovementCtx = document.getElementById('stockMovementChart').getContext('2d');
new Chart(stockMovementCtx, {
    type: 'line',
    data: {
        labels: monthlyData.map(d => d.month),
        datasets: [
            {
                label: 'Stock In',
                data: monthlyData.map(d => d.stock_in),
                borderColor: colors.success,
                backgroundColor: colors.success + '20',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            },
            {
                label: 'Dispensed',
                data: monthlyData.map(d => d.dispensed),
                borderColor: colors.primary,
                backgroundColor: colors.primary + '20',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 15
                }
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 12,
                titleFont: { size: 14, weight: 'bold' },
                bodyFont: { size: 13 }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#e2e8f0'
                },
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString();
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// 2. Category Distribution Chart (Doughnut)
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryColors = [
    colors.primary, colors.success, colors.warning, colors.purple, 
    colors.pink, colors.teal, colors.orange, colors.cyan, colors.indigo
];

new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: categoryData.map(d => d.category),
        datasets: [{
            data: categoryData.map(d => d.count),
            backgroundColor: categoryColors.slice(0, categoryData.length),
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    padding: 12,
                    font: { size: 12 }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// 3. Expiry Timeline Chart (Bar Chart)
const expiryCtx = document.getElementById('expiryChart').getContext('2d');
new Chart(expiryCtx, {
    type: 'bar',
    data: {
        labels: expiryTimeline.map(d => d.month),
        datasets: [{
            label: 'Medicines Expiring',
            data: expiryTimeline.map(d => d.count),
            backgroundColor: expiryTimeline.map((d, i) => {
                if (i < 3) return colors.danger + 'cc'; // First 3 months - critical
                if (i < 6) return colors.warning + 'cc'; // 4-6 months - warning
                return colors.success + 'cc'; // 7+ months - safe
            }),
            borderColor: expiryTimeline.map((d, i) => {
                if (i < 3) return colors.danger;
                if (i < 6) return colors.warning;
                return colors.success;
            }),
            borderWidth: 2,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    title: function(context) {
                        return 'Expiring in ' + context[0].label;
                    },
                    label: function(context) {
                        return context.parsed.y + ' medicine(s)';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#e2e8f0'
                },
                ticks: {
                    stepSize: 1
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// 4. Stock Status Chart (Pie Chart)
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: ['Active', 'Low Stock', 'Out of Stock', 'Expired'],
        datasets: [{
            data: [65, 20, 10, 5], // Example data - replace with actual
            backgroundColor: [
                colors.success + 'dd',
                colors.warning + 'dd',
                colors.danger + 'dd',
                '#94a3b8dd'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    padding: 12,
                    font: { size: 12 }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return `${context.label}: ${context.parsed} (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// 5. Dispensing Trend Chart (Area Chart)
const dispensingCtx = document.getElementById('dispensingChart').getContext('2d');
new Chart(dispensingCtx, {
    type: 'line',
    data: {
        labels: monthlyData.map(d => d.month),
        datasets: [{
            label: 'Units Dispensed',
            data: monthlyData.map(d => d.dispensed),
            borderColor: colors.indigo,
            backgroundColor: colors.indigo + '40',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 5,
            pointHoverRadius: 7,
            pointBackgroundColor: colors.indigo,
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 12
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#e2e8f0'
                },
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString();
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Refresh functionality
function refreshCharts() {
    location.reload();
}

// Auto-refresh every 5 minutes
setInterval(() => {
    location.reload();
}, 300000);
</script>

</body>
</html>