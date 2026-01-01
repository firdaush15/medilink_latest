<!-- resources\views\pharmacist\pharmacist_inventoryEdit.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Medicine - {{ $medicine->medicine_name }} - MediLink</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css', 'resources/css/pharmacist/pharmacist_inventory.css'])
    <style>
        .edit-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 24px;
            color: #1f2937;
            margin: 0 0 8px 0;
        }

        .breadcrumb {
            display: flex;
            gap: 8px;
            align-items: center;
            color: #6b7280;
            font-size: 14px;
        }

        .breadcrumb a {
            color: #3b82f6;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .edit-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 24px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f3f4f6;
        }

        .card-header h2 {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .form-section {
            margin-bottom: 32px;
        }

        .form-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .required {
            color: #dc2626;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .checkbox-group label {
            margin: 0;
            font-weight: 500;
            cursor: pointer;
        }

        .help-text {
            font-size: 13px;
            color: #6b7280;
            margin-top: 6px;
            display: block;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 24px;
            margin-bottom: 24px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
        }

        .info-value {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .stock-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
        }

        .stock-status.active {
            background: #d1fae5;
            color: #065f46;
        }

        .stock-status.low {
            background: #fef3c7;
            color: #92400e;
        }

        .stock-status.out {
            background: #fee2e2;
            color: #991b1b;
        }

        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .warning-box strong {
            color: #92400e;
            display: block;
            margin-bottom: 4px;
        }

        .warning-box p {
            color: #78350f;
            margin: 0;
            font-size: 14px;
        }

        .recent-movements {
            margin-top: 24px;
        }

        .movement-item {
            padding: 12px;
            border-radius: 8px;
            background: #f9fafb;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .movement-type {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .movement-type.in {
            color: #059669;
        }

        .movement-type.out {
            color: #dc2626;
        }

        .movement-details {
            color: #6b7280;
            font-size: 12px;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding: 24px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            position: sticky;
            bottom: 20px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #dc2626;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')

    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1>💊 Edit Medicine Information</h1>
            <div class="user-info">
                <span>{{ auth()->user()->name }}</span>
                <img src="{{ auth()->user()->profile_photo ?? asset('assets/default-avatar.png') }}" alt="Profile" class="profile-pic">
            </div>
        </div>

        <div class="edit-container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Edit: {{ $medicine->medicine_name }}</h1>
                <div class="breadcrumb">
                    <a href="{{ route('pharmacist.inventory') }}">Inventory</a>
                    <span>/</span>
                    <a href="{{ route('pharmacist.inventory.show', $medicine->medicine_id) }}">{{ $medicine->medicine_name }}</a>
                    <span>/</span>
                    <span>Edit</span>
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

            @if($errors->any())
            <div class="alert alert-error">
                <strong>⚠️ Validation Errors:</strong>
                <ul style="margin: 8px 0 0 20px;">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Important Notice -->
            <div class="warning-box">
                <strong>⚠️ Important: Metadata Editing Only</strong>
                <p>This form edits medicine information (name, category, dosage, etc.). To adjust stock quantities, use <a href="{{ route('pharmacist.receipts.create') }}" style="color: #92400e; text-decoration: underline;">Stock Receipts</a> to add stock or the dispensing system to reduce stock.</p>
            </div>

            <!-- Main Content Grid -->
            <div class="content-grid">
                <!-- Left Column: Edit Form -->
                <div>
                    <form method="POST" action="{{ route('pharmacist.inventory.update', $medicine->medicine_id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="edit-card">
                            <div class="card-header">
                                <h2>📋 Basic Information</h2>
                            </div>

                            <div class="form-section">
                                <div class="form-group">
                                    <label>Medicine Name <span class="required">*</span></label>
                                    <input type="text" name="medicine_name" value="{{ old('medicine_name', $medicine->medicine_name) }}" required>
                                    <span class="help-text">Primary name used in the system</span>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Generic Name</label>
                                        <input type="text" name="generic_name" value="{{ old('generic_name', $medicine->generic_name) }}">
                                        <span class="help-text">e.g., Paracetamol, Amoxicillin</span>
                                    </div>

                                    <div class="form-group">
                                        <label>Brand Name</label>
                                        <input type="text" name="brand_name" value="{{ old('brand_name', $medicine->brand_name) }}">
                                        <span class="help-text">e.g., Panadol, Augmentin</span>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Category <span class="required">*</span></label>
                                        <select name="category" required>
                                            <option value="">Select Category</option>
                                            @foreach($categories as $cat)
                                            <option value="{{ $cat }}" {{ old('category', $medicine->category) == $cat ? 'selected' : '' }}>
                                                {{ $cat }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Form <span class="required">*</span></label>
                                        <select name="form" required>
                                            <option value="">Select Form</option>
                                            @foreach($forms as $form)
                                            <option value="{{ $form }}" {{ old('form', $medicine->form) == $form ? 'selected' : '' }}>
                                                {{ $form }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Strength <span class="required">*</span></label>
                                    <input type="text" name="strength" value="{{ old('strength', $medicine->strength) }}" required>
                                    <span class="help-text">e.g., 500mg, 250mg/5ml, 10%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Management -->
                        <div class="edit-card" style="margin-top: 24px;">
                            <div class="card-header">
                                <h2>📦 Stock Management Settings</h2>
                            </div>

                            <div class="form-section">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Reorder Level <span class="required">*</span></label>
                                        <input type="number" name="reorder_level" value="{{ old('reorder_level', $medicine->reorder_level) }}" min="0" required>
                                        <span class="help-text">Alert when stock falls below this number</span>
                                    </div>

                                    <div class="form-group">
                                        <label>Unit Price (RM) <span class="required">*</span></label>
                                        <input type="number" name="unit_price" value="{{ old('unit_price', $medicine->unit_price) }}" step="0.01" min="0" required>
                                        <span class="help-text">Average price per unit</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Clinical Information -->
                        <div class="edit-card" style="margin-top: 24px;">
                            <div class="card-header">
                                <h2>🩺 Clinical Information</h2>
                            </div>

                            <div class="form-section">
                                <div class="form-group">
                                    <label>Storage Instructions</label>
                                    <textarea name="storage_instructions">{{ old('storage_instructions', $medicine->storage_instructions) }}</textarea>
                                    <span class="help-text">e.g., Store below 25°C, Keep refrigerated</span>
                                </div>

                                <div class="form-group">
                                    <label>Side Effects</label>
                                    <textarea name="side_effects">{{ old('side_effects', $medicine->side_effects) }}</textarea>
                                    <span class="help-text">Common side effects patients should be aware of</span>
                                </div>

                                <div class="form-group">
                                    <label>Contraindications</label>
                                    <textarea name="contraindications">{{ old('contraindications', $medicine->contraindications) }}</textarea>
                                    <span class="help-text">When NOT to use this medicine</span>
                                </div>
                            </div>
                        </div>

                        <!-- Regulatory -->
                        <div class="edit-card" style="margin-top: 24px;">
                            <div class="card-header">
                                <h2>⚖️ Regulatory Classification</h2>
                            </div>

                            <div class="form-section">
                                <div class="checkbox-group">
                                    <input type="checkbox" name="requires_prescription" id="requires_prescription" value="1" {{ old('requires_prescription', $medicine->requires_prescription) ? 'checked' : '' }}>
                                    <label for="requires_prescription">Requires Prescription</label>
                                </div>

                                <div class="checkbox-group">
                                    <input type="checkbox" name="is_controlled_substance" id="is_controlled_substance" value="1" {{ old('is_controlled_substance', $medicine->is_controlled_substance) ? 'checked' : '' }}>
                                    <label for="is_controlled_substance">Controlled Substance (requires special tracking)</label>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons" style="margin-top: 24px;">
                            <a href="{{ route('pharmacist.inventory.show', $medicine->medicine_id) }}" class="btn btn-secondary">
                                ← Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                💾 Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Current Info & Activity -->
                <div>
                    <!-- Current Stock Info (Read-Only) -->
                    <div class="info-card">
                        <div class="card-header">
                            <h2>📊 Current Stock Status</h2>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Current Stock</span>
                            <span class="info-value">{{ $medicine->quantity_in_stock }} units</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Active Batches</span>
                            <span class="info-value">{{ $medicine->activeBatches()->count() }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status</span>
                            <span class="stock-status {{ $medicine->status == 'Active' ? 'active' : ($medicine->status == 'Low Stock' ? 'low' : 'out') }}">
                                {{ $medicine->status }}
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Last Updated</span>
                            <span class="info-value">{{ $medicine->updated_at->format('d M Y') }}</span>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="info-card">
                        <div class="card-header">
                            <h2>⚡ Quick Actions</h2>
                        </div>
                        <a href="{{ route('pharmacist.receipts.create', ['medicine_id' => $medicine->medicine_id]) }}" class="btn btn-primary" style="width: 100%; justify-content: center; margin-bottom: 12px;">
                            📥 Receive Stock
                        </a>
                        <a href="{{ route('pharmacist.inventory.stock-history', $medicine->medicine_id) }}" class="btn btn-secondary" style="width: 100%; justify-content: center; margin-bottom: 12px;">
                            📊 View Stock History
                        </a>
                        @if($medicine->isLowStock())
                        <a href="{{ route('pharmacist.restock.create', ['medicine_id' => $medicine->medicine_id]) }}" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                            📦 Request Restock
                        </a>
                        @endif
                    </div>

                    <!-- Recent Activity -->
                    @if($recentMovements->count() > 0)
                    <div class="info-card">
                        <div class="card-header">
                            <h2>📝 Recent Movements</h2>
                        </div>
                        <div class="recent-movements">
                            @foreach($recentMovements->take(5) as $movement)
                            <div class="movement-item">
                                <div class="movement-type {{ $movement->quantity > 0 ? 'in' : 'out' }}">
                                    {{ $movement->movement_type }}
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }} units
                                </div>
                                <div class="movement-details">
                                    {{ $movement->created_at->format('d M Y, h:i A') }}
                                    @if($movement->pharmacist)
                                    by {{ $movement->pharmacist->user->name }}
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Confirm before leaving if form is modified
        let formModified = false;
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('change', () => {
                formModified = true;
            });
        });

        window.addEventListener('beforeunload', (e) => {
            if (formModified) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Reset flag on form submit
        document.querySelector('form').addEventListener('submit', () => {
            formModified = false;
        });
    </script>
</body>
</html>