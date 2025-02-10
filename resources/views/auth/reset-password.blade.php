<!DOCTYPE html>
<html>

<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
</head>

<body>
    <!-- Login Section -->
    <div class="login-section">
        <p class="login-heading">SIS</p>
        <p style="text-align: center; color: #ffffff; margin-bottom: 20px; margin-top: -1rem;">
            Student Information System
        </p>

        <form action="{{ route('password.reset') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <!-- Input Password -->
            <div class="form-group">
                <label for="password">Password Baru</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" required>
                    <i class="fas fa-lock"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Konfirmasi Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password_confirmation" id="password_confirmation" required>
                    <i class="fas fa-lock"></i>
                </div>
            </div>

            <!-- Tombol Login -->
            <center>
                <button type="submit" class="btn">Reset Password</button>
            </center>
        </form>
    </div>
</body>

</html>
