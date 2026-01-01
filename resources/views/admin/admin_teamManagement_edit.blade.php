<!--admin_teamManagement_edit.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Assignment - MediLink Admin</title>
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

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .status-badge.active {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .form-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 0 auto;
        }

        .assignment-info-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .assignment-participants {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .participant {
            display: flex;
            flex-direction: column;
        }

        .participant-label {
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 5px;
        }

        .participant-name {
            font-size: 18px;
            font-weight: 700;
        }

        .arrow {
            font-size: 24px;
        }

        .form-section {
            margin-bottom: 35px;
        }

        .form-section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e1e8ed;
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

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:disabled {
            background: #f8f9fa;
            cursor: not-allowed;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 10px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            cursor: pointer;
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        .help-text {
            font-size: 12px;
            color: #718096;
            margin-top: 5px;
        }

        .radio-group {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .radio-item {
            flex: 1;
            padding: 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .radio-item:has(input:checked) {
            border-color: #667eea;
            background: #f8f9fe;
        }

        .radio-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .radio-label strong {
            display: block;
            font-size: 14px;
            margin-bottom: 3px;
        }

        .radio-label small {
            font-size: 12px;
            color: #718096;
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
    </style>
</head>
<body>
    @include('admin.sidebar.admin_sidebar')

    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>✏️ Edit Assignment</h1>
                <div class="breadcrumb">
                    <a href="{{ route('admin.teams.index') }}">Team Management</a> / Edit Assignment
                </div>
            </div>
            <span class="status-badge {{ $assignment->is_active ? 'active' : 'inactive' }}">
                {{ $assignment->is_active ? 'Active' : 'Inactive' }}
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
            <div class="assignment-info-banner">
                <div class="assignment-participants">
                    <div class="participant">
                        <div class="participant-label">Nurse</div>
                        <div class="participant-name">👩‍⚕️ {{ $assignment->nurse->user->name }}</div>
                    </div>
                    <div class="arrow">→</div>
                    <div class="participant">
                        <div class="participant-label">Doctor</div>
                        <div class="participant-name">👨‍⚕️ Dr. {{ $assignment->doctor->user->name }}</div>
                    </div>
                </div>
                <div>
                    <div style="font-size: 12px; opacity: 0.8;">Assignment ID</div>
                    <div style="font-size: 16px; font-weight: 600;">#{{ $assignment->assignment_id }}</div>
                </div>
            </div>

            <form action="{{ route('admin.teams.update', $assignment->assignment_id) }}" method="POST" id="editForm">
                @csrf
                @method('PUT')

                <!-- Assignment Type -->
                <div class="form-section">
                    <div class="form-section-title">🎯 Assignment Type</div>
                    
                    <div class="radio-group">
                        <label class="radio-item">
                            <div class="radio-content">
                                <input type="radio" name="assignment_type" value="primary" 
                                       {{ $assignment->assignment_type === 'primary' ? 'checked' : '' }} required>
                                <div class="radio-label">
                                    <strong>Primary Nurse</strong>
                                    <small>Main nurse for this doctor</small>
                                </div>
                            </div>
                        </label>

                        <label class="radio-item">
                            <div class="radio-content">
                                <input type="radio" name="assignment_type" value="backup"
                                       {{ $assignment->assignment_type === 'backup' ? 'checked' : '' }} required>
                                <div class="radio-label">
                                    <strong>Backup Nurse</strong>
                                    <small>Covers when primary unavailable</small>
                                </div>
                            </div>
                        </label>

                        <label class="radio-item">
                            <div class="radio-content">
                                <input type="radio" name="assignment_type" value="floater"
                                       {{ $assignment->assignment_type === 'floater' ? 'checked' : '' }} required>
                                <div class="radio-label">
                                    <strong>Floater</strong>
                                    <small>Fills in as needed</small>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <label>Priority Order</label>
                        <select name="priority_order" required>
                            @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ $assignment->priority_order == $i ? 'selected' : '' }}>
                                Priority {{ $i }}
                            </option>
                            @endfor
                        </select>
                        <div class="help-text">Lower number = higher priority</div>
                    </div>
                </div>

                <!-- Schedule -->
                <div class="form-section">
                    <div class="form-section-title">📅 Schedule</div>

                    <div class="form-group">
                        <label>Working Days</label>
                        <div class="checkbox-grid">
                            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                            <label class="checkbox-item">
                                <input type="checkbox" name="working_days[]" value="{{ $day }}"
                                       {{ in_array($day, $assignment->working_days ?? []) ? 'checked' : '' }}>
                                <span>{{ $day }}</span>
                            </label>
                            @endforeach
                        </div>
                        <div class="help-text">Leave empty for 24/7 availability</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Shift Start Time</label>
                            <input type="time" name="shift_start" 
                                   value="{{ $assignment->shift_start ? \Carbon\Carbon::parse($assignment->shift_start)->format('H:i') : '' }}">
                        </div>

                        <div class="form-group">
                            <label>Shift End Time</label>
                            <input type="time" name="shift_end"
                                   value="{{ $assignment->shift_end ? \Carbon\Carbon::parse($assignment->shift_end)->format('H:i') : '' }}">
                        </div>
                    </div>
                </div>

                <!-- Status & Duration -->
                <div class="form-section">
                    <div class="form-section-title">⏰ Status & Duration</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="is_active" required>
                                <option value="1" {{ $assignment->is_active ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$assignment->is_active ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>End Date (Optional)</label>
                            <input type="date" name="assigned_until"
                                   value="{{ $assignment->assigned_until ? $assignment->assigned_until->format('Y-m-d') : '' }}">
                            <div class="help-text">Leave empty for permanent assignment</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" value="{{ $assignment->assigned_from->format('Y-m-d') }}" disabled>
                        <div class="help-text">Cannot change start date (created: {{ $assignment->created_at->format('M d, Y') }})</div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        🗑️ Delete Assignment
                    </button>
                    <div style="display: flex; gap: 12px;">
                        <a href="{{ route('admin.teams.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            ✓ Update Assignment
                        </button>
                    </div>
                </div>
            </form>

            <form id="deleteForm" action="{{ route('admin.teams.destroy', $assignment->assignment_id) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <script>
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this assignment? This action cannot be undone.')) {
                document.getElementById('deleteForm').submit();
            }
        }

        document.getElementById('editForm').addEventListener('submit', function(e) {
            const startTime = document.querySelector('input[name="shift_start"]').value;
            const endTime = document.querySelector('input[name="shift_end"]').value;
            
            if (startTime && endTime && endTime <= startTime) {
                e.preventDefault();
                alert('Shift end time must be after start time!');
            }
        });
    </script>
</body>
</html>