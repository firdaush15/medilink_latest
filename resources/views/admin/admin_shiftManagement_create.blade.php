<!--admin_shiftManagement_create.blade.php-->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Shift - MediLink Admin</title>
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
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
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
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
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
            outline:
                outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
            gap: 10px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .checkbox-group label {
            margin: 0;
            cursor: pointer;
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

        .shift-preview {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-top: 25px;
        }

        .shift-preview h3 {
            margin-bottom: 15px;
            font-size: 18px;
        }

        .shift-preview-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .shift-preview-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 12px;
            border-radius: 8px;
        }

        .shift-preview-item strong {
            display: block;
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    @include('admin.sidebar.admin_sidebar')

    <div class="main-content">
        <div class="page-header">
            <h1>➕ Create New Shift</h1>
            <div class="breadcrumb">
                <a href="{{ route('admin.shifts.index') }}">Staff Shifts</a> / Create New Shift
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
            <form action="{{ route('admin.shifts.store') }}" method="POST" id="shiftForm">
                @csrf

                <div class="form-group">
                    <label>Staff Member <span class="required">*</span></label>
                    <select name="user_id" id="user_id" required onchange="updatePreview()">
                        <option value="">-- Select Staff Member --</option>
                        @foreach($staff->groupBy('role') as $role => $members)
                        <optgroup label="{{ ucfirst($role) }}s">
                            @foreach($members as $member)
                            <option value="{{ $member->id }}" data-role="{{ $role }}">
                                {{ $member->name }}
                            </option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                    <div class="help-text">Select the staff member to assign this shift to</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Shift Date <span class="required">*</span></label>
                        <input type="date" name="shift_date" id="shift_date" required
                            value="{{ old('shift_date', now()->format('Y-m-d')) }}"
                            min="{{ now()->format('Y-m-d') }}"
                            onchange="updatePreview()">
                        <div class="help-text">Date when this shift will occur</div>
                    </div>

                    <div class="form-group">
                        <label>Shift Template</label>
                        <select name="template_id" id="template_id" onchange="applyTemplate()">
                            <option value="">-- Custom Times --</option>
                            @foreach($templates as $template)
                            <option value="{{ $template->template_id }}"
                                data-start="{{ $template->start_time->format('H:i') }}"
                                data-end="{{ $template->end_time->format('H:i') }}">
                                {{ $template->template_name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="help-text">Select a preset or create custom times</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Start Time <span class="required">*</span></label>
                        <input type="time" name="start_time" id="start_time" required
                            value="{{ old('start_time', '08:00') }}"
                            onchange="updatePreview()">
                    </div>

                    <div class="form-group">
                        <label>End Time <span class="required">*</span></label>
                        <input type="time" name="end_time" id="end_time" required
                            value="{{ old('end_time', '17:00') }}"
                            onchange="updatePreview()">
                    </div>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_recurring" id="is_recurring" value="1">
                        <label for="is_recurring">
                            <strong>Recurring Shift</strong> - Repeat this shift weekly on the same day
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea name="notes" placeholder="Add any special instructions or notes for this shift...">{{ old('notes') }}</textarea>
                </div>

                <!-- Shift Preview -->
                <div class="shift-preview" id="shiftPreview" style="display: none;">
                    <h3>📋 Shift Preview</h3>
                    <div class="shift-preview-details">
                        <div class="shift-preview-item">
                            <strong>Staff Member</strong>
                            <div id="preview_staff">-</div>
                        </div>
                        <div class="shift-preview-item">
                            <strong>Date</strong>
                            <div id="preview_date">-</div>
                        </div>
                        <div class="shift-preview-item">
                            <strong>Time</strong>
                            <div id="preview_time">-</div>
                        </div>
                        <div class="shift-preview-item">
                            <strong>Duration</strong>
                            <div id="preview_duration">-</div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">
                        ← Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        ✓ Create Shift
                    </button>
                </div>
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
                updatePreview();
            }
        }

        function updatePreview() {
            const userId = document.getElementById('user_id').value;
            const date = document.getElementById('shift_date').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;

            if (!userId || !date || !startTime || !endTime) {
                document.getElementById('shiftPreview').style.display = 'none';
                return;
            }

            const userSelect = document.getElementById('user_id');
            const staffName = userSelect.options[userSelect.selectedIndex].text;

            const dateObj = new Date(date);
            const dateFormatted = dateObj.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            const duration = calculateDuration(startTime, endTime);

            document.getElementById('preview_staff').textContent = staffName;
            document.getElementById('preview_date').textContent = dateFormatted;
            document.getElementById('preview_time').textContent = `${startTime} - ${endTime}`;
            document.getElementById('preview_duration').textContent = duration;

            document.getElementById('shiftPreview').style.display = 'block';
        }

        function calculateDuration(start, end) {
            const [startHour, startMin] = start.split(':').map(Number);
            const [endHour, endMin] = end.split(':').map(Number);

            const startMinutes = startHour * 60 + startMin;
            const endMinutes = endHour * 60 + endMin;
            const duration = endMinutes - startMinutes;

            const hours = Math.floor(duration / 60);
            const minutes = duration % 60;

            return minutes > 0 ? `${hours}h ${minutes}m` : `${hours} hours`;
        }

        // Validate end time is after start time
        document.getElementById('shiftForm').addEventListener('submit', function(e) {
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