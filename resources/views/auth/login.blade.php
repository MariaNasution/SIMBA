<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Information System</title>
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
</head>

<body>
    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Del Logo">
            <div class="welcome-content">
                <p class="welcome-text">Selamat Datang,</p>
                <div class="container-sis">
                    <p class="welcome-text">Di</p>
                    <p class="font-sis">SIS</p>
                </div>
                <p class="student-text">Student Information System</p>
            </div>
        </div>

        <!-- Login Section -->
        <div class="login-section">
            <p class="login-heading">SIS</p>
            <p style="text-align: center; color: #ffffff; margin-bottom: 20px; margin-top: -1rem;">
                Student Information System
            </p>

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                <!-- Input NIM -->
                <div class="form-group">
                    <label for="nim">NIM</label>
                    <div class="input-wrapper">
                        <input type="text" id="nim" name="nim" placeholder="Student NIM" required>
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <!-- Input Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="*******" required>
                        <i class="fas fa-lock"></i>
                    </div>

                    <!-- Pesan Kesalahan -->
                    @if ($errors->has('login'))
                        <p style="color: red; font-size: 14px; margin-top: 8px;">
                            {{ $errors->first('login') }}
                        </p>
                    @endif
                </div>

                <div class="forgot-password">
                    <a href="{{ route('password.forgot') }}"
                        style="color: #ffffff; font-size: 14px; text-align: right; display: block; margin-top: 10px;">
                        Lupa Password?
                    </a>
                </div>

                <div>
                    <a href="{{ route('register.form') }}"
                        style="color: #ffffff; font-size: 14px; text-align: right; display: block; margin-top: 10px;">
                        Belum Punya Akun?
                    </a>
                </div>

                <!-- Tombol Login -->
                <center>
                    <button type="submit" class="btn">Login</button>
                </center>
            </form>
        </div>
    </div>
</body>

</html>
