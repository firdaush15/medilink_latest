<!--admin_shiftManagement_edit.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Shift - MediLink Admin</title>
    @vite(['resources/css/admin/admin_sidebar.css'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .main-content {
            margin-left: 260px;
            padding: 30px;
            min-height: 100vh;
        }

        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            font-size: 28px;
            color: #1a202c;
            margin-bottom: 5px;
        }

        .breadcrumb {
            color: #718096;
            font-size: 14px;
            margin-top: 8px;
        }

        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
        }

        .form-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .info-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .info-banner-icon {
            font-size: 32px;
        }

        .info-banner-content h3 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .info-banner-content p {
            font-size: 13px;
            opacity: 0.9;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
            font-size: 14px;
        }

        .form-group label .required {
            color: #e74c3c;
        }

        .form-group select,
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-group select:focus,
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input:disabled {
            background: #f8f9fa;
            cursor: not-allowed;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: space-between;
            margin-top: 35px;
            padding-top: 25px;
            border-top: 2px solid #e1e8ed;
        }

        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
        }

        .btn-secondary:hover {
            background: #e9ecef;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .help-text {
            font-size: 12px;
            color: #718096;
            margin-top: 5px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.scheduled {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-badge.checked-in {
            background: #e8f5e9;
            color: #388e3c;
        }

        .status-badge.cancelled {
            background: #ffebee;
            color: #d32f2f;
        }
    </style>
</head>
<body>
    @include('admin.sidebar.admin_sidebar')

    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>✏️ Edit Shift</h1>
                <div class="breadcrumb">
                    <a href="{{ route('admin.shifts.index') }}">Staff Shifts</a> / Edit Shift
                </div>
            </div>
            <span class="status-badge {{ $shift->status }}">
                {{ ucfirst($shift->status) }}
            </span>
        </div>

        @if($errors->any())
        <div class="alert alert-error">
            <strong>⚠ Please fix the following errors:</strong>
            <ul style="margin-top: 10px; padding-left: 20px;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="form-container">
            <div class="info-banner">
                <div class="info-banner-icon">👤</div>
                <div class="info-banner-content">
                    <h3>{{ $shift->user->name }}</h3>
                    <p>{{ ucfirst($shift->staff_role) }} • Shift ID: #{{ $shift->shift_id }}</p>
                </div>
            </div>

            <form action="{{ route('admin.shifts.update', $shift->shift_id) }}" method="POST" id="editForm">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Staff Member</label>
                    <input type="text" value="{{ $shift->user->name }} ({{ ucfirst($shift->staff_role) }})" disabled>
                    <div class="help-text">Staff member cannot be changed. Delete and recreate shift to reassign.</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Shift Date <span class="required">*</span></label>
                        <input type="date" name="shift_date" required 
                               value="{{ $shift->shift_date->format('Y-m-d') }}">
                    </div>

                    <div class="form-group">
                        <label>Shift Template</label>
                        <select name="template_id" id="template_id" onchange="applyTemplate()">
                            <option value="">-- Custom Times --</option>
                            @foreach($templates as $template)
                            <option value="{{ $template->template_id }}"
                                    data-start="{{ $template->start_time->format('H:i') }}"
                                    data-end="{{ $template->end_time->format('H:i') }}"
                                    {{ $shift->template_id == $template->template_id ? 'selected' : '' }}>
                                {{ $template->template_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Start Time <span class="required">*</span></label>
                        <input type="time" name="start_time" id="start_time" required 
                               value="{{ $shift->start_time->format('H:i') }}">
                    </div>

                    <div class="form-group">
                        <label>End Time <span class="required">*</span></label>
                        <input type="time" name="end_time" id="end_time" required 
                               value="{{ $shift->end_time->format('H:i') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="scheduled" {{ $shift->status == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="checked_in" {{ $shift->status == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                        <option value="checked_out" {{ $shift->status == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                        <option value="cancelled" {{ $shift->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="absent" {{ $shift->status == 'absent' ? 'selected' : '' }}>Absent</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea name="notes" placeholder="Add any notes...">{{ $shift->notes }}</textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        🗑️ Delete Shift
                    </button>
                    <div style="display: flex; gap: 12px;">
                        <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            ✓ Update Shift
                        </button>
                    </div>
                </div>
            </form>

            <form id="deleteForm" action="{{ route('admin.shifts.destroy', $shift->shift_id) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <script>
        function applyTemplate() {
            const select = document.getElementById('template_id');
            const option = select.options[select.selectedIndex];
            
            if (option.dataset.start) {
                document.getElementById('start_time').value = option.dataset.start;
                document.getElementById('end_time').value = option.dataset.end;
            }
        }

        function confirmDelete() {
            if (confirm('Are you sure you want to delete this shift? This action cannot be undone.')) {
                document.getElementById('deleteForm').submit();
            }
        }

        document.getElementById('editForm').addEventListener('submit', function(e) {
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            
            if (endTime <= startTime) {
                e.preventDefault();
                alert('End time must be after start time!');
            }
        });
    </script>
</body>
</html>