<!-- resources\views\pharmacist\pharmacist_inventoryShow.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $medicine->medicine_name }} - Details</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css'])
    <style>
        .detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .medicine-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .medicine-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .medicine-subtitle {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
        }
        
        .info-label {
            color: #6b7280;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #1f2937;
            font-size: 18px;
            font-weight: 600;
        }
        
        .section-header {
            font-size: 20px;
            font-weight: 700;
            margin: 30px 0 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .batch-table {
            width: 100%;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .batch-table th {
            background: #f9fafb;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .batch-table td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .batch-table tr:last-child td {
            border-bottom: none;
        }
        
        .batch-table tr:hover {
            background: #f9fafb;
        }
        
        .badge-active {
            background: #d1fae5;
            color: #065f46;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-depleted {
            background: #fee2e2;
            color: #991b1b;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-expired {
            background: #fecaca;
            color: #7f1d1d;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .expiry-critical {
            color: #dc2626;
            font-weight: 600;
        }
        
        .expiry-warning {
            color: #f59e0b;
            font-weight: 600;
        }
        
        .expiry-ok {
            color: #10b981;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .empty-batches {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }
        
        .fefo-indicator {
            background: #dbeafe;
            color: #1e40af;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')
    
    <div class="main-content">
        <div class="detail-container">
            <!-- Medicine Header -->
            <div class="medicine-header">
                <div class="medicine-title">{{ $medicine->medicine_name }}</div>
                <div class="medicine-subtitle">
                    @if($medicine->generic_name)
                        {{ $medicine->generic_name }} •
                    @endif
                    @if($medicine->brand_name)
                        {{ $medicine->brand_name }} •
                    @endif
                    {{ $medicine->form }} {{ $medicine->strength }}
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">Total Stock</div>
                    <div class="info-value">{{ $medicine->quantity_in_stock }} units</div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Active Batches</div>
                    <div class="info-value">{{ $medicine->activeBatches()->count() }}</div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Reorder Level</div>
                    <div class="info-value">{{ $medicine->reorder_level }} units</div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $medicine->status)) }}">
                            {{ $medicine->status }}
                        </span>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Category</div>
                    <div class="info-value">{{ $medicine->category }}</div>
                </div>
                
                <div class="info-card">
                    <div class="info-label">Average Price</div>
                    <div class="info-value">RM {{ number_format($medicine->unit_price, 2) }}</div>
                </div>
            </div>
            
            <!-- Batch Information -->
            <div class="section-header">
                📦 Batch Information
            </div>
            
            @if($medicine->batches->count() > 0)
                <div class="fefo-indicator">
                    ℹ️ <strong>FEFO System:</strong> Batches listed by expiry date (oldest first). System automatically dispenses from oldest batch.
                </div>
                
                <table class="batch-table">
                    <thead>
                        <tr>
                            <th>FEFO Order</th>
                            <th>Batch Number</th>
                            <th>Quantity</th>
                            <th>Supplier</th>
                            <th>Received Date</th>
                            <th>Expiry Date</th>
                            <th>Unit Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($medicine->batches->sortBy('expiry_date') as $index => $batch)
                        <tr>
                            <td>
                                @if($batch->status === 'active' && $batch->quantity > 0)
                                    <strong>#{{ $index + 1 }}</strong>
                                    @if($index === 0)
                                        <small style="color: #3b82f6;">← Next to dispense</small>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td><strong>{{ $batch->batch_number }}</strong></td>
                            <td>
                                <strong>{{ $batch->quantity }}</strong> units
                                @if($batch->quantity === 0)
                                    <br><small style="color: #6b7280;">Depleted</small>
                                @endif
                            </td>
                            <td>{{ $batch->supplier ?? '-' }}</td>
                            <td>{{ $batch->received_date->format('d M Y') }}</td>
                            <td>
                                @php
                                    $daysLeft = $batch->getDaysUntilExpiry();
                                    $monthsLeft = $batch->getMonthsUntilExpiry();
                                @endphp
                                
                                {{ $batch->expiry_date->format('d M Y') }}
                                
                                @if($batch->isExpired())
                                    <br><span class="expiry-critical">❌ EXPIRED</span>
                                @elseif($batch->isExpiringCritical())
                                    <br><span class="expiry-critical">🚨 {{ round($daysLeft) }} days left</span>
                                @elseif($batch->isExpiringSoon())
                                    <br><span class="expiry-warning">⚠️ {{ round($monthsLeft) }} months left</span>
                                @else
                                    <br><span class="expiry-ok">✓ {{ round($monthsLeft) }} months left</span>
                                @endif
                            </td>
                            <td>RM {{ number_format($batch->unit_price, 2) }}</td>
                            <td>
                                @if($batch->status === 'active')
                                    <span class="badge-active">Active</span>
                                @elseif($batch->status === 'depleted')
                                    <span class="badge-depleted">Depleted</span>
                                @elseif($batch->status === 'expired')
                                    <span class="badge-expired">Expired</span>
                                @else
                                    <span class="badge-{{ $batch->status }}">{{ ucfirst($batch->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-batches">
                    <h3>No Batches Found</h3>
                    <p>This medicine has no batch records yet.</p>
                </div>
            @endif
            
            <!-- Additional Information -->
            @if($medicine->storage_instructions || $medicine->side_effects || $medicine->contraindications)
            <div class="section-header">
                ℹ️ Additional Information
            </div>
            
            <div class="info-grid">
                @if($medicine->storage_instructions)
                <div class="info-card">
                    <div class="info-label">Storage Instructions</div>
                    <div style="color: #4b5563; margin-top: 8px;">{{ $medicine->storage_instructions }}</div>
                </div>
                @endif
                
                @if($medicine->side_effects)
                <div class="info-card">
                    <div class="info-label">Side Effects</div>
                    <div style="color: #4b5563; margin-top: 8px;">{{ $medicine->side_effects }}</div>
                </div>
                @endif
                
                @if($medicine->contraindications)
                <div class="info-card">
                    <div class="info-label">Contraindications</div>
                    <div style="color: #4b5563; margin-top: 8px;">{{ $medicine->contraindications }}</div>
                </div>
                @endif
            </div>
            @endif
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('pharmacist.inventory') }}" class="btn btn-secondary">
                    ← Back to Inventory
                </a>
                <a href="{{ route('pharmacist.inventory.edit', $medicine->medicine_id) }}" class="btn btn-primary">
                    ✏️ Edit Medicine Info
                </a>
                <a href="{{ route('pharmacist.receipts.create') }}?medicine_id={{ $medicine->medicine_id }}" class="btn btn-success">
                    📥 Add New Batch
                </a>
                <a href="{{ route('pharmacist.inventory.stock-history', $medicine->medicine_id) }}" class="btn btn-secondary">
                    📊 View Stock History
                </a>
            </div>
        </div>
    </div>
</body>
</html>