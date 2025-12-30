<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MediLink | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            height: 100vh;
            background: url('{{ asset('assets/background.jpg') }}') no-repeat center center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1;
        }

        .login-container {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.15);
            padding: 50px 60px;
            border-radius: 25px;
            backdrop-filter: blur(12px);
            text-align: center;
            color: #fff;
            width: 400px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }

        .login-container img {
            width: 300px;
            margin-bottom: 10px;
            margin-top: -10px;
        }

        h2 {
            font-size: 26px;
            color: #00BFFF;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .status-message {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.5);
            color: #fff;
            padding: 12px;
            border-radius: 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .input-group {
            margin-top: 25px;
            text-align: left;
        }

        label {
            font-size: 14px;
            font-weight: 600;
            color: #f2f2f2;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 13px 15px;
            border: none;
            border-radius: 25px;
            margin-top: 8px;
            outline: none;
            font-size: 14px;
        }

        .error-message {
            color: #ff6b6b;
            font-size: 12px;
            margin-top: 5px;
            text-align: left;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-top: 20px;
            text-align: left;
        }

        .remember-me input[type="checkbox"] {
            width: auto;
            margin-right: 8px;
            cursor: pointer;
        }

        .remember-me label {
            font-size: 13px;
            font-weight: 400;
            cursor: pointer;
        }

        .forgot-password {
            text-align: right;
            margin-top: 15px;
        }

        .forgot-password a {
            color: #00BFFF;
            font-size: 13px;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .btn {
            margin-top: 30px;
            width: 100%;
            padding: 13px;
            background: #00BFFF;
            border: none;
            border-radius: 25px;
            color: #fff;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            background: #0099cc;
        }

        .signup-text {
            margin-top: 18px;
            font-size: 13px;
        }

        .signup-text a {
            color: #00BFFF;
            font-weight: 600;
            text-decoration: none;
        }

        .signup-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="login-container">
        <img src="{{ asset('assets/logo.png') }}" alt="MediLink Logo">
        <h2>HEALTH CARE</h2>

        <!-- Session Status -->
        @if (session('status'))
            <div class="status-message">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       placeholder="Enter your email" 
                       required 
                       autofocus 
                       autocomplete="username">
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Enter your password" 
                       required 
                       autocomplete="current-password">
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="remember-me">
                <input type="checkbox" id="remember_me" name="remember">
                <label for="remember_me">Remember me</label>
            </div>

            <!-- Forgot Password -->
            @if (Route::has('password.request'))
                <div class="forgot-password">
                    <a href="{{ route('password.request') }}">Forgot your password?</a>
                </div>
            @endif

            <button type="submit" class="btn">SUBMIT</button>

            @if (Route::has('register'))
                <div class="signup-text">
                    Don't have an account? <a href="{{ route('register') }}">Sign up now!</a>
                </div>
            @endif
        </form>
    </div>
</body>
</html>