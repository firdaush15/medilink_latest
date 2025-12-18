<!--admin_teamManagement_create.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Nurse to Doctor - MediLink Admin</title>
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
            max-width: 900px;
            margin: 0 auto;
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

        .form-group label .required {
            color: #e74c3c;
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
            transition: all 0.2s;
        }

        .checkbox-item:hover {
            background: #e9ecef;
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkbox-item label {
            margin: 0 !important;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
        }

        .help-text {
            font-size: 12px;
            color: #718096;
            margin-top: 5px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
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

        .info-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .info-banner h3 {
            margin-bottom: 8px;
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

        .radio-item:hover {
            border-color: #667eea;
            background: #f8f9fe;
        }

        .radio-item input[type="radio"]:checked + .radio-content {
            color: #667eea;
        }

        .radio-item input[type="radio"]:checked ~ .radio-item {
            border-color: #667eea;
            background: #f8f9fe;
        }

        .radio-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .radio-content input {
            width: 20px;
            height: 20px;
        }

        .radio-label {
            display: flex;
            flex-direction: column;
        }

        .radio-label strong {
            font-size: 14px;
            margin-bottom: 3px;
        }

        .radio-label small {
            font-size: 12px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    @include('admin.sidebar.admin_sidebar')

    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>👥 Assign Nurse to Doctor</h1>
                <div class="breadcrumb">
                    <a href="{{ route('admin.teams.index') }}">Team Management</a> / Assign Nurse
                </div>
            </div>
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
            @if($doctor)
            <div class="info-banner">
                <h3>Assigning Nurse to Dr. {{ $doctor->user->name }}</h3>
                <p>{{ $doctor->specialization }}</p>
            </div>
            @endif

            <form action="{{ route('admin.teams.store') }}" method="POST">
                @csrf

                <!-- Basic Assignment -->
                <div class="form-section">
                    <div class="form-section-title">📋 Basic Assignment</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Doctor <span class="required">*</span></label>
                            <select name="doctor_id" required {{ $doctor ? 'disabled' : '' }}>
                                <option value="">-- Select Doctor --</option>
                                @foreach($doctors as $doc)
                                <option value="{{ $doc->doctor_id }}" 
                                        {{ ($doctor && $doctor->doctor_id == $doc->doctor_id) ? 'selected' : '' }}>
                                    Dr. {{ $doc->user->name }} ({{ $doc->specialization }})
                                </option>
                                @endforeach
                            </select>
                            @if($doctor)
                            <input type="hidden" name="doctor_id" value="{{ $doctor->doctor_id }}">
                            @endif
                            <div class="help-text">Select the doctor to assign the nurse to</div>
                        </div>

                        <div class="form-group">
                            <label>Nurse <span class="required">*</span></label>
                            <select name="nurse_id" required>
                                <option value="">-- Select Nurse --</option>
                                @foreach($nurses as $nurse)
                                <option value="{{ $nurse->nurse_id }}">
                                    {{ $nurse->user->name }} ({{ ucfirst($nurse->availability_status) }})
                                </option>
                                @endforeach
                            </select>
                            <div class="help-text">Choose an available nurse</div>
                        </div>
                    </div>
                </div>

                <!-- Assignment Type -->
                <div class="form-section">
                    <div class="form-section-title">🎯 Assignment Type</div>
                    
                    <div class="radio-group">
                        <label class="radio-item">
                            <div class="radio-content">
                                <input type="radio" name="assignment_type" value="primary" required checked>
                                <div class="radio-label">
                                    <strong>Primary Nurse</strong>
                                    <small>Main nurse for this doctor</small>
                                </div>
                            </div>
                        </label>

                        <label class="radio-item">
                            <div class="radio-content">
                                <input type="radio" name="assignment_type" value="backup" required>
                                <div class="radio-label">
                                    <strong>Backup Nurse</strong>
                                    <small>Covers when primary is unavailable</small>
                                </div>
                            </div>
                        </label>

                        <label class="radio-item">
                            <div class="radio-content">
                                <input type="radio" name="assignment_type" value="floater" required>
                                <div class="radio-label">
                                    <strong>Floater</strong>
                                    <small>Fills in as needed</small>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <label>Priority Order</label>
                        <select name="priority_order">
                            <option value="">Auto-assign next priority</option>
                            @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}">Priority {{ $i }}</option>
                            @endfor
                        </select>
                        <div class="help-text">Lower number = higher priority (leave empty to auto-assign)</div>
                    </div>
                </div>

                <!-- Schedule -->
                <div class="form-section">
                    <div class="form-section-title">📅 Schedule (Optional)</div>

                    <div class="form-group">
                        <label>Working Days</label>
                        <div class="checkbox-grid">
                            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                            <label class="checkbox-item">
                                <input type="checkbox" name="working_days[]" value="{{ $day }}">
                                <span>{{ $day }}</span>
                            </label>
                            @endforeach
                        </div>
                        <div class="help-text">Leave empty for 24/7 availability</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Shift Start Time</label>
                            <input type="time" name="shift_start">
                            <div class="help-text">Optional: Define shift start time</div>
                        </div>

                        <div class="form-group">
                            <label>Shift End Time</label>
                            <input type="time" name="shift_end">
                            <div class="help-text">Optional: Define shift end time</div>
                        </div>
                    </div>
                </div>

                <!-- Duration -->
                <div class="form-section">
                    <div class="form-section-title">⏰ Assignment Duration</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="assigned_from" 
                                   value="{{ now()->format('Y-m-d') }}"
                                   min="{{ now()->format('Y-m-d') }}">
                            <div class="help-text">When this assignment begins</div>
                        </div>

                        <div class="form-group">
                            <label>End Date (Optional)</label>
                            <input type="date" name="assigned_until">
                            <div class="help-text">Leave empty for permanent assignment</div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.teams.index') }}" class="btn btn-secondary">
                        ← Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        ✓ Create Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Validate shift times
        document.querySelector('form').addEventListener('submit', function(e) {
            const shiftStart = document.querySelector('input[name="shift_start"]').value;
            const shiftEnd = document.querySelector('input[name="shift_end"]').value;

            if (shiftStart && shiftEnd && shiftEnd <= shiftStart) {
                e.preventDefault();
                alert('Shift end time must be after start time!');
            }

            const assignedFrom = document.querySelector('input[name="assigned_from"]').value;
            const assignedUntil = document.querySelector('input[name="assigned_until"]').value;

            if (assignedFrom && assignedUntil && assignedUntil <= assignedFrom) {
                e.preventDefault();
                alert('End date must be after start date!');
            }
        });
    </script>
</body>
</html>