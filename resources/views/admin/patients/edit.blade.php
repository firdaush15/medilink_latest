<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediLink | Edit Patient</title>
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

        .required {
            color: #ef4444;
        }

        .form-input, .form-textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-help {
            font-size: 12px;
            color: #64748b;
            margin-top: 6px;
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
    </style>
</head>
<body>
    @include('admin.sidebar.admin_sidebar')

    <div class="main">
        <div class="page-header">
            <h2>✏️ Edit Patient Contact Information</h2>
            <a href="{{ route('admin.patients') }}" class="btn-back">← Back to List</a>
        </div>

        <div class="form-container">
            <div class="form-header">
                <h3>Update Patient: {{ $patient->user->name }}</h3>
                <p>Edit contact and emergency information</p>
            </div>

            <div class="info-box">
                <p>ℹ️ <strong>Note:</strong> You can only update contact information. Medical records and history cannot be modified from this form.</p>
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

            <form method="POST" action="{{ route('admin.patients.update', $patient->patient_id) }}">
                @csrf
                @method('PUT')

                <!-- Phone Number -->
                <div class="form-group">
                    <label class="form-label">
                        Phone Number <span class="required">*</span>
                    </label>
                    <input type="text" name="phone_number" class="form-input" 
                           value="{{ old('phone_number', $patient->phone_number) }}" 
                           required placeholder="e.g., 0123456789">
                    <div class="form-help">Primary contact number for appointments and reminders</div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label">
                        Email Address
                    </label>
                    <input type="email" name="email" class="form-input" 
                           value="{{ old('email', $patient->user->email) }}" 
                           placeholder="patient@example.com">
                    <div class="form-help">Used for appointment confirmations and medical reports</div>
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label class="form-label">
                        Address
                    </label>
                    <textarea name="address" class="form-textarea" 
                              placeholder="Full residential address">{{ old('address', $patient->user->address ?? '') }}</textarea>
                    <div class="form-help">Complete address including city and postal code</div>
                </div>

                <!-- Emergency Contact -->
                <div class="form-group">
                    <label class="form-label">
                        Emergency Contact
                    </label>
                    <input type="text" name="emergency_contact" class="form-input" 
                           value="{{ old('emergency_contact', $patient->emergency_contact) }}" 
                           placeholder="Name: John Doe, Relation: Spouse, Phone: 0198765432">
                    <div class="form-help">Format: Name, Relationship, Phone Number</div>
                </div>

                <!-- Read-only fields (for reference) -->
                <div class="form-group">
                    <label class="form-label">Patient ID (Read-only)</label>
                    <input type="text" class="form-input" value="#{{ $patient->patient_id }}" disabled style="background: #f1f5f9; cursor: not-allowed;">
                </div>

                <div class="form-group">
                    <label class="form-label">Date of Birth (Read-only)</label>
                    <input type="text" class="form-input" value="{{ $patient->date_of_birth->format('M d, Y') }} ({{ $patient->age }} years old)" disabled style="background: #f1f5f9; cursor: not-allowed;">
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="{{ route('admin.patients') }}">
                        <button type="button" class="btn btn-secondary">Cancel</button>
                    </a>
                    <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>