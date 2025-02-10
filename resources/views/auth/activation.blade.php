<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <title>Aktivasi Akun</title>
</head>

<body>
    <div class="login-section">
        <p class="login-heading">SIS</p>
        <p style="text-align: center; color: #ffffff; margin-bottom: 20px; margin-top: -1rem;">
            Student Information System
        </p>
        <form action="{{ route('activation.submit') }}" method="POST">
            @csrf
            <!-- Input Password -->
            <div class="form-group">
                <label for="token">Token Aktivasi</label>
                <input type="text" id="token" name="token" placeholder="Masukkan Token" required>
            </div>

            <!-- Tombol Login -->
            <center>
                <button type="submit" class="btn">Aktivasi</button>
            </center>
        </form>
    </div>

</body>

</html>
