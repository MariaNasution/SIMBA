<!DOCTYPE html>
<html>

<head>
    <title>Pemberitahuan Email</title>
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
</head>

<body>
    <div class="email-section">
        <p class="login-heading">SIS</p>
        <p style="text-align: center; color: #ffffff; margin-bottom: 20px; margin-top: -1rem;">
            Student Information System
        </p>

        <div class="email-container">

            <div class="email-notification-card">
                <h2 class="email-heading">Use to Registration</h2>
                <hr>
                <p class="email-content">
                    Cek email anak Anda! Kami telah mengirimkan Token untuk aktivasi akun anda, token ini hanya dikirim
                    1 kali untuk setiap akun. Jika Anda belum masuk
                    ke akun Anda, Anda harus masuk di sini. Anda kemudian dapat segera berhasil mendaftarkan akun SIS.
                    Tidak melihat pop-up? Pastikan pemblokir pop-up browser Anda dimatikan, lalu coba lagi.
                </p>
                <a href="{{ route('login') }}" class="btn">Go back</a>
            </div>
        </div>
    </div>
</body>

</html>
