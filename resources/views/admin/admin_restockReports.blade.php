<!-- resources/views/admin/admin_restockReports.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restock Reports - Admin</title>
    @vite(['resources/css/admin/admin_sidebar.css'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        .container { 
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .page-header h1 { font-size: 28px; color: #1a202c; }
        
        .report-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .report-section h2 {
            font-size: 20px;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .chart-container {
            height: 300px;
            margin-bottom: 20px;
        }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        
        th {
            background: #f7fafc;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 2px solid #e2e8f0;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            color: #2d3748;
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
        
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:hover { background: #2563eb; }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .summary-card {
            background: #f7fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }
        
        .summary-card .label {
            font-size: 13px;
            color: #718096;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        .summary-card .value {
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
        }
    </style>
</head>
<body>
    @include('admin.sidebar.admin_sidebar')
    
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>📊 Restock Reports & Analytics</h1>
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-primary" onclick="window.print()">🖨️ Print Report</button>
                <a href="{{ route('admin.restock.index') }}" class="btn" style="background: #e2e8f0; color: #2d3748;">← Back</a>
            </div>
        </div>

        <!-- Monthly Trend -->
        <div class="report-section">
            <h2>📈 Monthly Restock Trend (Last 6 Months)</h2>
            
            <div class="chart-container">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Requests Created</th>
                        <th>Requests Approved</th>
                        <th>Total Received Value</th>
                        <th>Approval Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyData as $data)
                    <tr>
                        <td><strong>{{ $data['month'] }}</strong></td>
                        <td>{{ $data['requests'] }}</td>
                        <td style="color: #10b981; font-weight: 600;">{{ $data['approved'] }}</td>
                        <td style="color: #3b82f6; font-weight: 700;">RM {{ number_format($data['received_value'], 2) }}</td>
                        <td>
                            @php
                                $rate = $data['requests'] > 0 ? ($data['approved'] / $data['requests']) * 100 : 0;
                            @endphp
                            <span style="color: {{ $rate >= 80 ? '#10b981' : '#f59e0b' }}; font-weight: 600;">
                                {{ number_format($rate, 1) }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Top Requested Medicines -->
        <div class="report-section">
            <h2>🏆 Top 10 Most Requested Medicines</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Medicine Name</th>
                        <th>Category</th>
                        <th>Total Requests</th>
                        <th>Total Quantity Requested</th>
                        <th>Current Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topMedicines as $index => $item)
                    <tr>
                        <td><strong>#{{ $index + 1 }}</strong></td>
                        <td>
                            <strong>{{ $item->medicine->medicine_name }}</strong><br>
                            <small style="color: #6b7280;">{{ $item->medicine->form }} {{ $item->medicine->strength }}</small>
                        </td>
                        <td>{{ $item->medicine->category }}</td>
                        <td><strong>{{ $item->request_count }}</strong> times</td>
                        <td><strong>{{ number_format($item->total_qty) }}</strong> units</td>
                        <td style="color: {{ $item->medicine->quantity_in_stock <= $item->medicine->reorder_level ? '#ef4444' : '#10b981' }}; font-weight: 600;">
                            {{ $item->medicine->quantity_in_stock }} units
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Disposal Breakdown -->
        <div class="report-section">
            <h2>🗑️ Disposal Analysis by Reason</h2>
            
            <div class="summary-grid">
                @foreach($disposalByReason as $disposal)
                <div class="summary-card" style="border-left-color: {{ $disposal->reason == 'Expired' ? '#ef4444' : '#f59e0b' }};">
                    <div class="label">{{ $disposal->reason }}</div>
                    <div class="value">{{ $disposal->count }}</div>
                    <div style="font-size: 13px; color: #6b7280; margin-top: 5px;">
                        Loss: RM {{ number_format($disposal->total_loss, 2) }}
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="chart-container">
                <canvas id="disposalReasonChart"></canvas>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="report-section">
            <h2>📊 Overall Summary</h2>
            
            <div class="summary-grid">
                @php
                    $totalRequests = collect($monthlyData)->sum('requests');
                    $totalApproved = collect($monthlyData)->sum('approved');
                    $totalValue = collect($monthlyData)->sum('received_value');
                    $totalDisposals = $disposalByReason->sum('count');
                    $totalDisposalLoss = $disposalByReason->sum('total_loss');
                @endphp
                
                <div class="summary-card">
                    <div class="label">Total Restock Requests (6 months)</div>
                    <div class="value">{{ $totalRequests }}</div>
                </div>
                
                <div class="summary-card" style="border-left-color: #10b981;">
                    <div class="label">Total Approved</div>
                    <div class="value">{{ $totalApproved }}</div>
                    <div style="font-size: 13px; color: #6b7280; margin-top: 5px;">
                        {{ $totalRequests > 0 ? number_format(($totalApproved / $totalRequests) * 100, 1) : 0 }}% approval rate
                    </div>
                </div>
                
                <div class="summary-card" style="border-left-color: #3b82f6;">
                    <div class="label">Total Inventory Value Received</div>
                    <div class="value">RM {{ number_format($totalValue, 2) }}</div>
                </div>
                
                <div class="summary-card" style="border-left-color: #ef4444;">
                    <div class="label">Total Disposals (All Time)</div>
                    <div class="value">{{ $totalDisposals }}</div>
                    <div style="font-size: 13px; color: #6b7280; margin-top: 5px;">
                        Loss: RM {{ number_format($totalDisposalLoss, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Monthly Trend Chart
        const monthlyCtx = document.getElementById('monthlyTrendChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: @json(array_column($monthlyData, 'month')),
                datasets: [
                    {
                        label: 'Requests Created',
                        data: @json(array_column($monthlyData, 'requests')),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Requests Approved',
                        data: @json(array_column($monthlyData, 'approved')),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Disposal Reason Chart
        const disposalCtx = document.getElementById('disposalReasonChart').getContext('2d');
        new Chart(disposalCtx, {
            type: 'doughnut',
            data: {
                labels: @json($disposalByReason->pluck('reason')),
                datasets: [{
                    data: @json($disposalByReason->pluck('count')),
                    backgroundColor: [
                        '#ef4444', '#f59e0b', '#eab308', '#84cc16', 
                        '#22c55e', '#14b8a6', '#3b82f6', '#8b5cf6'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    </script>
</body>
</html>