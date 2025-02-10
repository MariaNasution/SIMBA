<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Aktivasi</title>
</head>

<body>
    <p>Terima kasih telah mendaftar. Gunakan token berikut untuk mengaktivasi akun Anda:</p>
    <p><strong>{{ $token }}</strong></p>
    <p>Masukkan token di halaman <a href="{{ route('activation.prompt') }}">aktivasi</a>.</p>
</body>

</html>
