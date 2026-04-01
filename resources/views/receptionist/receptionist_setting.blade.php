<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings — Receptionist | MediLink</title>
    @vite(['resources/css/sidebar.css'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f4f8;
        }

        .page-wrapper { min-height: 100vh; }

        .content-area { padding: 36px 40px; overflow-y: auto; margin-left: 220px; }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .page-title { font-size: 1.6rem; font-weight: 700; color: #1a2332; }
        .page-sub   { font-size: 0.85rem; color: #64748b; margin-top: 3px; }
        .role-chip  {
            padding: 6px 16px; border-radius: 20px;
            font-size: 0.78rem; font-weight: 600;
            background: #fef9c3; color: #854d0e;
        }

        .flash { padding: 12px 18px; border-radius: 10px; margin-bottom: 22px; font-size: 0.88rem; font-weight: 500; }
        .flash-success { background: #dcfce7; color: #166534; border-left: 4px solid #22c55e; }
        .flash-error   { background: #fee2e2; color: #b91c1c; border-left: 4px solid #ef4444; }

        .settings-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(520px, 1fr)); gap: 24px; }

        .card { background: #fff; border-radius: 16px; padding: 28px; border: 1px solid #e2e8f0; box-shadow: 0 1px 6px rgba(0,0,0,0.06); }
        .card-danger { border-color: #fecaca; }

        .card-head { display: flex; align-items: flex-start; gap: 14px; padding-bottom: 18px; margin-bottom: 22px; border-bottom: 1px solid #f1f5f9; }
        .card-icon  { font-size: 1.5rem; line-height: 1; flex-shrink: 0; margin-top: 2px; }
        .card-title { font-size: 1rem; font-weight: 700; color: #1a2332; }
        .card-desc  { font-size: 0.8rem; color: #94a3b8; margin-top: 3px; }

        .avatar-row { display: flex; align-items: center; gap: 18px; background: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 22px; }
        .avatar-circle {
            width: 70px; height: 70px; border-radius: 50%;
            background: linear-gradient(135deg, #d97706, #f59e0b);
            color: #fff; font-size: 1.35rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .avatar-img { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid #e2e8f0; flex-shrink: 0; }
        .upload-label { display: inline-block; padding: 8px 16px; background: #d97706; color: #fff; border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer; }
        .upload-label:hover { background: #b45309; }
        .upload-hint { font-size: 0.74rem; color: #94a3b8; margin-top: 5px; }
        input[type="file"].hidden { display: none; }

        .form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .form-control {
            width: 100%; padding: 10px 13px; border: 1.5px solid #e2e8f0; border-radius: 9px;
            font-size: 0.86rem; font-family: 'Poppins', sans-serif;
            color: #1a2332; background: #fff; outline: none; transition: border-color .2s;
        }
        .form-control:focus { border-color: #d97706; box-shadow: 0 0 0 3px rgba(217,119,6,.08); }
        textarea.form-control { resize: vertical; min-height: 72px; }

        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 10px 22px; border-radius: 9px; font-size: 0.86rem; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer; border: none; transition: all .2s; }
        .btn-primary   { background: #d97706; color: #fff; }
        .btn-primary:hover { background: #b45309; }
        .btn-secondary { background: #f1f5f9; color: #d97706; border: 1.5px solid #fde68a; }
        .btn-secondary:hover { background: #fef9c3; }
        .btn-danger    { background: #fee2e2; color: #b91c1c; border: 1.5px solid #fecaca; }
        .btn-danger:hover { background: #fca5a5; }

        .info-grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
        .info-tile  { background: #f8fafc; border-radius: 10px; padding: 12px 14px; }
        .info-tile-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; margin-bottom: 4px; }
        .info-tile-value { font-size: 0.86rem; font-weight: 600; color: #1a2332; }
        .pill { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 0.76rem; font-weight: 600; }
        .pill-green  { background: #dcfce7; color: #166534; }
        .pill-yellow { background: #fef9c3; color: #854d0e; }

        /* Performance bar */
        .perf-section { margin-top: 4px; }
        .perf-section-label { font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 10px; }
        .perf-bar-wrap { height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; margin-bottom: 6px; }
        .perf-bar { height: 100%; background: linear-gradient(90deg, #d97706, #f59e0b); border-radius: 4px; }
        .perf-labels { display: flex; justify-content: space-between; font-size: 0.74rem; color: #64748b; }

        .tips-box   { background: #f8fafc; border-radius: 10px; padding: 14px; margin-bottom: 18px; }
        .tips-title { font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 8px; }
        .tips-list  { list-style: none; }
        .tips-list li { font-size: 0.76rem; color: #64748b; padding: 2px 0; }
        .tips-list li::before { content: "• "; color: #d97706; }

        .danger-row { display: flex; justify-content: space-between; align-items: center; gap: 18px; background: #fff5f5; border: 1px solid #fecaca; border-radius: 10px; padding: 16px; }
        .danger-title { font-size: 0.88rem; font-weight: 600; color: #b91c1c; }
        .danger-hint  { font-size: 0.76rem; color: #94a3b8; margin-top: 3px; }

        @media (max-width: 900px) {
            .settings-grid { grid-template-columns: 1fr; }
            .form-row      { grid-template-columns: 1fr; }
            .content-area  { padding: 22px 18px; }
        }
    </style>
</head>
<body>

<div class="page-wrapper">

    @include('receptionist.sidebar.receptionist_sidebar')

    <div class="content-area">

        <div class="page-header">
            <div>
                <h1 class="page-title">⚙️ Settings</h1>
                <p class="page-sub">Manage your profile and account preferences</p>
            </div>
            <span class="role-chip">📋 Receptionist</span>
        </div>

        @if(session('success'))
            <div class="flash flash-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-error">❌ {{ session('error') }}</div>
        @endif

        <div class="settings-grid">

            {{-- Profile --}}
            <div class="card">
                <div class="card-head">
                    <span class="card-icon">👤</span>
                    <div>
                        <p class="card-title">Profile Information</p>
                        <p class="card-desc">Update your personal and work details</p>
                    </div>
                </div>

                <form action="{{ route('receptionist.setting.profile') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @php $receptionist = auth()->user()->receptionist; @endphp

                    <div class="avatar-row">
                        @if(auth()->user()->profile_photo)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" class="avatar-img" alt="Avatar">
                        @else
                            <div class="avatar-circle">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                        @endif
                        <div>
                            <label class="upload-label" for="rec_photo">📷 Change Photo</label>
                            <input type="file" id="rec_photo" name="profile_photo" class="hidden" accept="image/*">
                            <p class="upload-hint">JPG or PNG · Max 2 MB</p>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone_number" class="form-control"
                                value="{{ $receptionist?->phone_number }}" placeholder="+60 1X-XXX XXXX">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control"
                                value="{{ $receptionist?->department ?? 'Front Desk' }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Shift</label>
                        <select name="shift" class="form-control">
                            @foreach(['Morning', 'Afternoon', 'Evening', 'Night', 'Rotating'] as $s)
                                <option value="{{ $s }}" {{ $receptionist?->shift === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control">{{ auth()->user()->address }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 Save Profile</button>
                </form>
            </div>

            {{-- Password --}}
            <div class="card">
                <div class="card-head">
                    <span class="card-icon">🔒</span>
                    <div>
                        <p class="card-title">Change Password</p>
                        <p class="card-desc">Update your login credentials</p>
                    </div>
                </div>

                <form action="{{ route('receptionist.setting.password') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" placeholder="Enter current password">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Min. 8 characters">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password">
                        </div>
                    </div>
                    <div class="tips-box">
                        <p class="tips-title">Password requirements</p>
                        <ul class="tips-list">
                            <li>At least 8 characters</li>
                            <li>One uppercase letter</li>
                            <li>One number or special character</li>
                        </ul>
                    </div>
                    <button type="submit" class="btn btn-secondary">🔑 Update Password</button>
                </form>
            </div>

            {{-- Work statistics --}}
            <div class="card">
                <div class="card-head">
                    <span class="card-icon">📊</span>
                    <div>
                        <p class="card-title">Work Statistics</p>
                        <p class="card-desc">Check-in performance and work details</p>
                    </div>
                </div>

                @php
                    $receptionist = auth()->user()->receptionist;
                    $avail        = $receptionist?->availability_status ?? 'Available';
                    $perf         = $receptionist?->getTodayPerformance() ?? ['total_checked_in' => 0, 'on_time' => 0, 'late' => 0, 'on_time_percentage' => 0];
                @endphp

                <div class="info-grid">
                    <div class="info-tile">
                        <p class="info-tile-label">Employee ID</p>
                        <p class="info-tile-value">{{ $receptionist?->employee_id ?? '—' }}</p>
                    </div>
                    <div class="info-tile">
                        <p class="info-tile-label">Hire Date</p>
                        <p class="info-tile-value">{{ $receptionist?->hire_date?->format('d M Y') ?? '—' }}</p>
                    </div>
                    <div class="info-tile">
                        <p class="info-tile-label">Availability</p>
                        <span class="pill {{ $avail === 'Available' ? 'pill-green' : 'pill-yellow' }}">{{ $avail }}</span>
                    </div>
                    <div class="info-tile">
                        <p class="info-tile-label">Current Shift</p>
                        <p class="info-tile-value">{{ $receptionist?->shift ?? '—' }}</p>
                    </div>
                    <div class="info-tile">
                        <p class="info-tile-label">Today's Check-ins</p>
                        <p class="info-tile-value">{{ $receptionist?->patients_checked_in_today ?? 0 }}</p>
                    </div>
                    <div class="info-tile">
                        <p class="info-tile-label">Total Check-ins</p>
                        <p class="info-tile-value">{{ $receptionist?->total_patients_checked_in ?? 0 }}</p>
                    </div>
                </div>

                <div class="perf-section">
                    <p class="perf-section-label">Today's On-time Performance</p>
                    <div class="perf-bar-wrap">
                        <div class="perf-bar" style="width: {{ $perf['on_time_percentage'] }}%"></div>
                    </div>
                    <div class="perf-labels">
                        <span>On-time: {{ $perf['on_time'] }}</span>
                        <span>Late: {{ $perf['late'] }}</span>
                        <span>{{ number_format($perf['on_time_percentage'], 0) }}% efficiency</span>
                    </div>
                </div>
            </div>

            {{-- Danger zone --}}
            <div class="card card-danger">
                <div class="card-head">
                    <span class="card-icon">⚠️</span>
                    <div>
                        <p class="card-title">Danger Zone</p>
                        <p class="card-desc">Irreversible actions — proceed with caution</p>
                    </div>
                </div>
                <div class="danger-row">
                    <div>
                        <p class="danger-title">Delete Account</p>
                        <p class="danger-hint">Permanently remove your account and all data</p>
                    </div>
                    <form method="POST" action="{{ route('profile.destroy') }}"
                          onsubmit="return confirm('This cannot be undone. Continue?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">🗑️ Delete</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@vite(['resources/js/sidebar.js'])
</body>
</html>