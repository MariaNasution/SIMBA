<!DOCTYPE html>
<html>

<head>
    <title>Lupa Password</title>
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
</head>

<body>

    <div class="login-section">
        <p class="login-heading">SIS</p>
        <p style="text-align: center; color: #ffffff; margin-bottom: 20px; margin-top: -1rem;">
            Student Information System
        </p>

        <form action="{{ route('password.send-link') }}" method="POST">
            @csrf
            <!-- Input NIM -->
            <div class="form-group">
                <label for="nim">NIM</label>
                <div class="input-wrapper">
                    <input type="text" id="nim" name="nim" placeholder="Student NIM" required>
                    <i class="fas fa-user"></i>
                </div>
            </div>

            <!-- Tombol Login -->
            <center>
                <button type="submit" class="btn">Kirim Link Reset</button>
            </center>
        </form>
    </div>
</body>

</html>
