<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MediLink | Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    @vite(['resources/css/register.css'])
</head>


<body>
  <div class="overlay"></div>

  <div class="scroll-wrapper">
    <div class="signup-container">
      <img src="{{ asset('assets/logo.png') }}" alt="MediLink Logo">
      <h2>Create Account</h2>

      <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="input-group">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Enter your full name">
          @error('name')
            <p class="error">{{ $message }}</p>
          @enderror
        </div>

        <!-- Email -->
        <div class="input-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="Enter your email">
          @error('email')
            <p class="error">{{ $message }}</p>
          @enderror
        </div>

        <!-- Password -->
        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="Enter your password">
          @error('password')
            <p class="error">{{ $message }}</p>
          @enderror
        </div>

        <!-- Confirm Password -->
        <div class="input-group">
          <label for="password_confirmation">Confirm Password</label>
          <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" placeholder="Re-enter your password">
          @error('password_confirmation')
            <p class="error">{{ $message }}</p>
          @enderror
        </div>

        <button type="submit" class="btn">SIGN UP</button>

        <div class="login-text">
          Already have an account? <a href="{{ route('login') }}">Login here</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
