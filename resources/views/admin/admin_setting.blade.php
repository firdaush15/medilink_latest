<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings — Admin | MediLink</title>
    @vite(['resources/css/sidebar.css'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f4f8;
        }

        /* ── Main layout ── */
        .page-wrapper {
            min-height: 100vh;
        }

        .content-area {
            padding: 36px 40px;
            overflow-y: auto;
            margin-left: 260px; /* matches fixed sidebar width */
        }

        /* ── Page header ── */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        .page-title { font-size: 1.6rem; font-weight: 700; color: #1a2332; }
        .page-sub   { font-size: 0.85rem; color: #64748b; margin-top: 3px; }
        .role-chip {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            background: #fee2e2;
            color: #b91c1c;
        }

        /* ── Flash alerts ── */
        .flash {
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 22px;
            font-size: 0.88rem;
            font-weight: 500;
        }
        .flash-success { background: #dcfce7; color: #166534; border-left: 4px solid #22c55e; }
        .flash-error   { background: #fee2e2; color: #b91c1c; border-left: 4px solid #ef4444; }

        /* ── Settings grid ── */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(520px, 1fr));
            gap: 24px;
        }

        /* ── Card ── */
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 28px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
        }
        .card-danger { border-color: #fecaca; }

        .card-head {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding-bottom: 18px;
            margin-bottom: 22px;
            border-bottom: 1px solid #f1f5f9;
        }
        .card-icon { font-size: 1.5rem; line-height: 1; flex-shrink: 0; margin-top: 2px; }
        .card-title { font-size: 1rem; font-weight: 700; color: #1a2332; }
        .card-desc  { font-size: 0.8rem; color: #94a3b8; margin-top: 3px; }

        /* ── Avatar ── */
        .avatar-row {
            display: flex;
            align-items: center;
            gap: 18px;
            background: #f8fafc;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 22px;
        }
        .avatar-circle {
            width: 70px; height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            color: #fff;
            font-size: 1.35rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .avatar-img {
            width: 70px; height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e2e8f0;
            flex-shrink: 0;
        }
        .upload-label {
            display: inline-block;
            padding: 8px 16px;
            background: #1e3a5f;
            color: #fff;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }
        .upload-label:hover { background: #152d4a; }
        .upload-hint { font-size: 0.74rem; color: #94a3b8; margin-top: 5px; }
        input[type="file"].hidden { display: none; }

        /* ── Form ── */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 10px 13px;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            font-size: 0.86rem;
            font-family: 'Poppins', sans-serif;
            color: #1a2332;
            background: #fff;
            outline: none;
            transition: border-color .2s;
        }
        .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.08); }
        textarea.form-control { resize: vertical; min-height: 72px; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 22px;
            border-radius: 9px;
            font-size: 0.86rem;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            border: none;
            transition: all .2s;
        }
        .btn-primary   { background: #1e3a5f; color: #fff; }
        .btn-primary:hover { background: #152d4a; }
        .btn-secondary { background: #f1f5f9; color: #1e3a5f; border: 1.5px solid #cbd5e1; }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-danger    { background: #fee2e2; color: #b91c1c; border: 1.5px solid #fecaca; }
        .btn-danger:hover { background: #fca5a5; }

        /* ── Info tiles ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        .info-tile {
            background: #f8fafc;
            border-radius: 10px;
            padding: 12px 14px;
        }
        .info-tile-label {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #94a3b8;
            margin-bottom: 4px;
        }
        .info-tile-value {
            font-size: 0.86rem;
            font-weight: 600;
            color: #1a2332;
        }
        .pill {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.76rem;
            font-weight: 600;
        }
        .pill-green  { background: #dcfce7; color: #166534; }
        .pill-red    { background: #fee2e2; color: #b91c1c; }
        .pill-yellow { background: #fef9c3; color: #854d0e; }
        .pill-blue   { background: #dbeafe; color: #1d4ed8; }

        /* ── Permissions grid ── */
        .perms-wrap { margin-top: 6px; }
        .perms-label { font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 10px; }
        .perms-grid  { display: flex; flex-wrap: wrap; gap: 8px; }
        .perm-item {
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.76rem;
            font-weight: 500;
        }
        .perm-on  { background: #dcfce7; color: #166534; }
        .perm-off { background: #f1f5f9; color: #94a3b8; }

        /* ── Tips box ── */
        .tips-box {
            background: #f8fafc;
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 18px;
        }
        .tips-title { font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 8px; }
        .tips-list  { list-style: none; }
        .tips-list li { font-size: 0.76rem; color: #64748b; padding: 2px 0; }
        .tips-list li::before { content: "• "; color: #2563eb; }

        /* ── Danger row ── */
        .danger-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            background: #fff5f5;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 16px;
        }
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

    {{-- Sidebar --}}
    @include('admin.sidebar.admin_sidebar')

    {{-- Main content --}}
    <div class="content-area">

        <div class="page-header">
            <div>
                <h1 class="page-title">⚙️ Settings</h1>
                <p class="page-sub">Manage your account, profile and system preferences</p>
            </div>
            <span class="role-chip">👑 Super Admin</span>
        </div>

        @if(session('success'))
            <div class="flash flash-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-error">❌ {{ session('error') }}</div>
        @endif

        <div class="settings-grid">

            {{-- ── Profile card ── --}}
            <div class="card">
                <div class="card-head">
                    <span class="card-icon">👤</span>
                    <div>
                        <p class="card-title">Profile Information</p>
                        <p class="card-desc">Update your name, email, photo and contact details</p>
                    </div>
                </div>

                <form action="{{ route('admin.settings.profile') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="avatar-row">
                        @if(auth()->user()->profile_photo)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" class="avatar-img" alt="Avatar">
                        @else
                            <div class="avatar-circle">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                        @endif
                        <div>
                            <label class="upload-label" for="admin_photo">📷 Change Photo</label>
                            <input type="file" id="admin_photo" name="profile_photo" class="hidden" accept="image/*">
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
                                value="{{ auth()->user()->admin?->phone_number }}" placeholder="+60 1X-XXX XXXX">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control"
                                value="{{ auth()->user()->admin?->department ?? 'Administration' }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control">{{ auth()->user()->address }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">💾 Save Profile</button>
                </form>
            </div>

            {{-- ── Password card ── --}}
            <div class="card">
                <div class="card-head">
                    <span class="card-icon">🔒</span>
                    <div>
                        <p class="card-title">Change Password</p>
                        <p class="card-desc">Keep your account secure with a strong password</p>
                    </div>
                </div>

                <form action="{{ route('admin.settings.password') }}" method="POST">
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

            {{-- ── Admin account details ── --}}
            <div class="card">
                <div class="card-head">
                    <span class="card-icon">🏥</span>
                    <div>
                        <p class="card-title">Admin Account Details</p>
                        <p class="card-desc">Your role, access level and activity summary</p>
                    </div>
                </div>

                @php $admin = auth()->user()->admin; @endphp

                <div class="info-grid">
                    <div class="info-tile">
                        <p class="info-tile-label">Admin Level</p>
                        <span class="pill pill-red">{{ $admin?->admin_level ?? 'Admin' }}</span>
                    </div>
                    <div class="info-tile">
                        <p class="info-tile-label">Status</p>
                        <span class="pill {{ ($admin?->status === 'Active') ? 'pill-green' : 'pill-yellow' }}">
                            {{ $admin?->status ?? 'Active' }}
                        </span>
                    </div>
                    <div class="info-tile">
                        <p class="info-tile-label">Employee ID</p>
                        <p class="info-tile-value">{{ $admin?->employee_id ?? '—' }}</p>
                    </div>
                    <div class="info-tile">
                        <p class="info-tile-label">Hire Date</p>
                        <p class="info-tile-value">{{ $admin?->hire_date?->format('d M Y') ?? '—' }}</p>
                    </div>
                    <div class="info-tile">
                        <p class="info-tile-label">Last Login</p>
                        <p class="info-tile-value">{{ $admin?->last_login_at?->diffForHumans() ?? 'Never' }}</p>
                    </div>
                    <div class="info-tile">
                        <p class="info-tile-label">Total Logins</p>
                        <p class="info-tile-value">{{ $admin?->total_logins ?? 0 }}</p>
                    </div>
                </div>

                <div class="perms-wrap">
                    <p class="perms-label">Permissions</p>
                    <div class="perms-grid">
                        <span class="perm-item {{ $admin?->can_manage_staff    ? 'perm-on' : 'perm-off' }}">{{ $admin?->can_manage_staff    ? '✅' : '❌' }} Manage Staff</span>
                        <span class="perm-item {{ $admin?->can_manage_inventory ? 'perm-on' : 'perm-off' }}">{{ $admin?->can_manage_inventory ? '✅' : '❌' }} Inventory</span>
                        <span class="perm-item {{ $admin?->can_manage_billing   ? 'perm-on' : 'perm-off' }}">{{ $admin?->can_manage_billing   ? '✅' : '❌' }} Billing</span>
                        <span class="perm-item {{ $admin?->can_view_reports     ? 'perm-on' : 'perm-off' }}">{{ $admin?->can_view_reports     ? '✅' : '❌' }} Reports</span>
                        <span class="perm-item {{ $admin?->can_manage_system_settings ? 'perm-on' : 'perm-off' }}">{{ $admin?->can_manage_system_settings ? '✅' : '❌' }} System Settings</span>
                    </div>
                </div>
            </div>

            {{-- ── Danger zone ── --}}
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
                        <p class="danger-hint">Permanently remove your admin account and all associated data</p>
                    </div>
                    <form method="POST" action="{{ route('profile.destroy') }}"
                          onsubmit="return confirm('This is permanent and cannot be undone. Continue?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">🗑️ Delete</button>
                    </form>
                </div>
            </div>

        </div><!-- /settings-grid -->
    </div><!-- /content-area -->
</div><!-- /page-wrapper -->
@vite(['resources/js/sidebar.js'])
</body>
</html>