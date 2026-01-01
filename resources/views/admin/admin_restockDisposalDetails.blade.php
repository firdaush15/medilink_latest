<!-- resources/views/admin/admin_restockDisposalDetails.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disposal Details - {{ $disposal->disposal_number }}</title>
    @vite(['resources/css/admin/admin_sidebar.css'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        .container { 
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
        }
        
        .breadcrumb {
            display: flex;
            gap: 8px;
            align-items: center;
            font-size: 14px;
            color: #64748b;
            margin-bottom: 20px;
        }
        
        .breadcrumb a { color: #3182ce; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .header-left h1 { font-size: 28px; color: #1a202c; margin: 0 0 8px 0; }
        
        .header-meta {
            display: flex;
            gap: 12px;
            margin-top: 10px;
        }
        
        .badge {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .badge-expired { background: #fee2e2; color: #991b1b; }
        .badge-damaged { background: #fef3c7; color: #92400e; }
        .badge-high-value { background: #ffebee; color: #c62828; }
        
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
        
        .btn-secondary { background: #e2e8f0; color: #2d3748; }
        .btn-secondary:hover { background: #cbd5e0; }
        
        .alert-box {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-danger {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            color: #991b1b;
        }
        
        .alert-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .detail-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .detail-card h3 {
            font-size: 16px;
            color: #2d3748;
            margin: 0 0 16px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f7fafc;
        }
        
        .detail-row:last-child { border-bottom: none; }
        
        .detail-label {
            color: #718096;
            font-size: 14px;
        }
        
        .detail-value {
            color: #2d3748;
            font-weight: 600;
            font-size: 14px;
            text-align: right;
        }
        
        .loss-indicator {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 24px;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .loss-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(220, 38, 38, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            color: #991b1b;
        }
        
        .loss-info h4 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #991b1b;
        }
        
        .loss-info p {
            margin: 0;
            color: #7f1d1d;
            font-size: 14px;
        }
        
        .notes-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .notes-section h3 {
            font-size: 18px;
            color: #2d3748;
            margin: 0 0 16px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .notes-content {
            padding: 16px;
            background: #f7fafc;
            border-radius: 8px;
            color: #2d3748;
            line-height: 1.6;
        }
        
        .timeline-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .timeline-section h3 {
            font-size: 18px;
            color: #2d3748;
            margin: 0 0 20px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .timeline-item {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .timeline-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .timeline-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fee2e2;
            color: #991b1b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .timeline-content {
            flex: 1;
        }
        
        .timeline-title {
            font-size: 15px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }
        
        .timeline-meta {
            font-size: 13px;
            color: #718096;
        }
        
        @media (max-width: 1024px) {
            .container { margin-left: 220px; }
        }
        
        @media (max-width: 768px) {
            .container { 
                margin-left: 0; 
                padding: 15px;
                margin-top: 60px;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @include('admin.sidebar.admin_sidebar')
    
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.pharmacy-inventory.index') }}">Pharmacy</a>
            <span>/</span>
            <a href="{{ route('admin.restock.disposals') }}">Disposals</a>
            <span>/</span>
            <span>{{ $disposal->disposal_number }}</span>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h1>🗑️ Disposal Record: {{ $disposal->disposal_number }}</h1>
                <div class="header-meta">
                    <span class="badge badge-{{ strtolower(str_replace(' ', '-', $disposal->reason)) }}">
                        {{ $disposal->reason }}
                    </span>
                    @if($disposal->estimated_loss > 1000)
                        <span class="badge badge-high-value">💰 High Value Loss</span>
                    @endif
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-secondary" onclick="window.print()">🖨️ Print</button>
                <a href="{{ route('admin.restock.disposals') }}" class="btn btn-secondary">← Back to Disposals</a>
            </div>
        </div>

        <!-- High Value Alert -->
        @if($disposal->estimated_loss > 1000)
        <div class="alert-box alert-danger">
            <span style="font-size: 24px;">💰</span>
            <div>
                <strong>High-Value Disposal Alert:</strong> This disposal resulted in a significant financial loss exceeding RM 1,000.
            </div>
        </div>
        @endif

        <!-- Compliance Alert for Expired -->
        @if($disposal->reason === 'Expired')
        <div class="alert-box alert-warning">
            <span style="font-size: 24px;">⚠️</span>
            <div>
                <strong>Compliance Notice:</strong> This medicine was disposed due to expiration. Ensure proper disposal procedures were followed according to pharmaceutical waste regulations.
            </div>
        </div>
        @endif

        <!-- Financial Loss Indicator -->
        <div class="loss-indicator">
            <div class="loss-circle">
                RM {{ number_format($disposal->estimated_loss, 0) }}
            </div>
            <div class="loss-info">
                <h4>Estimated Financial Loss</h4>
                <p>{{ $disposal->quantity_disposed }} units × RM {{ number_format($disposal->medicine->unit_price, 2) }} per unit</p>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="details-grid">
            <!-- Medicine Information -->
            <div class="detail-card">
                <h3>💊 Medicine Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Medicine Name</span>
                    <span class="detail-value">{{ $disposal->medicine->medicine_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Category</span>
                    <span class="detail-value">{{ $disposal->medicine->category }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Form & Strength</span>
                    <span class="detail-value">{{ $disposal->medicine->form }} {{ $disposal->medicine->strength }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Unit Price</span>
                    <span class="detail-value">RM {{ number_format($disposal->medicine->unit_price, 2) }}</span>
                </div>
            </div>

            <!-- Disposal Information -->
            <div class="detail-card">
                <h3>🗑️ Disposal Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Quantity Disposed</span>
                    <span class="detail-value" style="color: #dc2626;">{{ number_format($disposal->quantity_disposed) }} units</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Batch Number</span>
                    <span class="detail-value">{{ $disposal->batch_number ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Disposal Method</span>
                    <span class="detail-value">{{ $disposal->disposal_method }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Reason</span>
                    <span class="detail-value">{{ $disposal->reason }}</span>
                </div>
            </div>

            <!-- Personnel & Date -->
            <div class="detail-card">
                <h3>👥 Personnel & Timeline</h3>
                <div class="detail-row">
                    <span class="detail-label">Disposed By</span>
                    <span class="detail-value">{{ $disposal->disposedBy->user->name }}</span>
                </div>
                @if($disposal->witnessedBy)
                <div class="detail-row">
                    <span class="detail-label">Witnessed By</span>
                    <span class="detail-value">{{ $disposal->witnessedBy->name }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Disposal Date</span>
                    <span class="detail-value">{{ $disposal->disposed_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Time Ago</span>
                    <span class="detail-value">{{ $disposal->disposed_at->diffForHumans() }}</span>
                </div>
            </div>

            <!-- Financial Impact -->
            <div class="detail-card">
                <h3>💸 Financial Impact</h3>
                <div class="detail-row">
                    <span class="detail-label">Unit Price</span>
                    <span class="detail-value">RM {{ number_format($disposal->medicine->unit_price, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Units Disposed</span>
                    <span class="detail-value">{{ number_format($disposal->quantity_disposed) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Loss</span>
                    <span class="detail-value" style="color: #dc2626; font-size: 18px;">
                        RM {{ number_format($disposal->estimated_loss, 2) }}
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Loss Category</span>
                    <span class="detail-value">
                        @if($disposal->estimated_loss > 1000)
                            <span style="color: #dc2626;">High Value</span>
                        @elseif($disposal->estimated_loss > 500)
                            <span style="color: #f59e0b;">Moderate</span>
                        @else
                            <span style="color: #10b981;">Low Impact</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Disposal Notes -->
        @if($disposal->notes)
        <div class="notes-section">
            <h3>📝 Disposal Notes</h3>
            <div class="notes-content">
                {{ $disposal->notes }}
            </div>
        </div>
        @endif

        <!-- Disposal Timeline -->
        <div class="timeline-section">
            <h3>📋 Disposal Process Timeline</h3>
            
            <div class="timeline-item">
                <div class="timeline-icon">🗑️</div>
                <div class="timeline-content">
                    <div class="timeline-title">Disposal Recorded</div>
                    <div class="timeline-meta">
                        {{ $disposal->disposed_at->format('F d, Y \a\t H:i') }} by {{ $disposal->disposedBy->user->name }}
                    </div>
                </div>
            </div>

            @if($disposal->witnessedBy)
            <div class="timeline-item">
                <div class="timeline-icon">👁️</div>
                <div class="timeline-content">
                    <div class="timeline-title">Disposal Witnessed</div>
                    <div class="timeline-meta">
                        Witnessed by {{ $disposal->witnessedBy->name }} ({{ $disposal->witnessedBy->role }})
                    </div>
                </div>
            </div>
            @endif

            <div class="timeline-item">
                <div class="timeline-icon">📊</div>
                <div class="timeline-content">
                    <div class="timeline-title">Reason for Disposal</div>
                    <div class="timeline-meta">
                        {{ $disposal->reason }}
                        @if($disposal->reason === 'Expired')
                            - Medicine exceeded safe use date
                        @elseif($disposal->reason === 'Damaged')
                            - Physical damage to medicine packaging or contents
                        @elseif($disposal->reason === 'Contaminated')
                            - Potential contamination detected
                        @endif
                    </div>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-icon">♻️</div>
                <div class="timeline-content">
                    <div class="timeline-title">Disposal Method Used</div>
                    <div class="timeline-meta">
                        {{ $disposal->disposal_method }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>