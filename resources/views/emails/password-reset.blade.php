<!DOCTYPE html>
<html>

<head>
    <title>Reset Password</title>
</head>

<body>
    <h1>Reset Password</h1>
    <p>Silakan klik tautan di bawah ini untuk mereset password Anda:</p>
    <p>Token: {{ $token ?? 'Token tidak tersedia' }}</p>
    <a href="{{ url('/reset-password/' . $token) }}">Reset Password</a>
    <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
</body>

</html>
