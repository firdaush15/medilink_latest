<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediLink | Edit Doctor</title>
    @vite(['resources/css/admin/admin_sidebar.css'])
    <style>
        body {
            margin: 0;
            font-family: "Poppins", sans-serif;
            background-color: #e4f4ff;
        }

        .main {
            margin-left: 230px;
            padding: 20px;
            min-height: 100vh;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-header h2 {
            font-size: 28px;
            color: #1e293b;
            margin: 0;
        }

        .btn-back {
            background: #64748b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: #475569;
        }

        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .form-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f1f5f9;
        }

        .form-header h3 {
            font-size: 22px;
            color: #1e293b;
            margin: 0 0 10px 0;
        }

        .form-header p {
            color: #64748b;
            margin: 0;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #1e293b;
            font-weight: 500;
            font-size: 14px;
        }

        .form-label .required {
            color: #ef4444;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-help {
            font-size: 12px;
            color: #64748b;
            margin-top: 6px;
        }

        .error-message {
            color: #ef4444;
            font-size: 12px;
            margin-top: 6px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f1f5f9;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .info-box p {
            margin: 0;
            color: #1e40af;
            font-size: 14px;
        }
    </style>
</head>
<body>
    @include('admin.sidebar.admin_sidebar')

    <div class="main">
        <div class="page-header">
            <h2>✏️ Edit Doctor Information</h2>
            <a href="{{ route('admin.doctors') }}" class="btn-back">← Back to List</a>
        </div>

        <div class="form-container">
            <div class="form-header">
                <h3>Update Doctor: Dr. {{ $doctor->user->name }}</h3>
                <p>Edit contact information and availability status</p>
            </div>

            <div class="info-box">
                <p>ℹ️ <strong>Note:</strong> You can only update contact information and availability status. Medical credentials and specialization changes require higher authorization.</p>
            </div>

            @if ($errors->any())
                <div style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <ul style="margin: 0; padding-left: 20px; color: #991b1b;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.doctors.update', $doctor->doctor_id) }}">
                @csrf
                @method('PUT')

                <!-- Phone Number -->
                <div class="form-group">
                    <label class="form-label">
                        Phone Number <span class="required">*</span>
                    </label>
                    <input type="text" name="phone_number" class="form-input" 
                           value="{{ old('phone_number', $doctor->phone_number) }}" 
                           required placeholder="e.g., 0123456789">
                    @error('phone_number')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Specialization -->
                <div class="form-group">
                    <label class="form-label">
                        Specialization <span class="required">*</span>
                    </label>
                    <select name="specialization" class="form-select" required>
                        <option value="">Select Specialization</option>
                        <option value="Cardiology" {{ $doctor->specialization == 'Cardiology' ? 'selected' : '' }}>Cardiology</option>
                        <option value="Neurology" {{ $doctor->specialization == 'Neurology' ? 'selected' : '' }}>Neurology</option>
                        <option value="Orthopedics" {{ $doctor->specialization == 'Orthopedics' ? 'selected' : '' }}>Orthopedics</option>
                        <option value="Pediatrics" {{ $doctor->specialization == 'Pediatrics' ? 'selected' : '' }}>Pediatrics</option>
                        <option value="Dermatology" {{ $doctor->specialization == 'Dermatology' ? 'selected' : '' }}>Dermatology</option>
                        <option value="General Practice" {{ $doctor->specialization == 'General Practice' ? 'selected' : '' }}>General Practice</option>
                        <option value="Internal Medicine" {{ $doctor->specialization == 'Internal Medicine' ? 'selected' : '' }}>Internal Medicine</option>
                        <option value="Surgery" {{ $doctor->specialization == 'Surgery' ? 'selected' : '' }}>Surgery</option>
                    </select>
                    @error('specialization')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Availability Status -->
                <div class="form-group">
                    <label class="form-label">
                        Availability Status <span class="required">*</span>
                    </label>
                    <select name="availability_status" class="form-select" required>
                        <option value="Available" {{ $doctor->availability_status == 'Available' ? 'selected' : '' }}>✅ Available</option>
                        <option value="On Leave" {{ $doctor->availability_status == 'On Leave' ? 'selected' : '' }}>🏖️ On Leave</option>
                        <option value="Unavailable" {{ $doctor->availability_status == 'Unavailable' ? 'selected' : '' }}>🚫 Unavailable</option>
                    </select>
                    <div class="form-help">This status will be visible to all staff and affects appointment scheduling.</div>
                    @error('availability_status')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email (Read-only, shown for reference) -->
                <div class="form-group">
                    <label class="form-label">Email (Read-only)</label>
                    <input type="email" class="form-input" value="{{ $doctor->user->email }}" disabled style="background: #f1f5f9; cursor: not-allowed;">
                    <div class="form-help">Email address cannot be changed from this form. Contact IT support for email changes.</div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="{{ route('admin.doctors') }}">
                        <button type="button" class="btn btn-secondary">Cancel</button>
                    </a>
                    <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>