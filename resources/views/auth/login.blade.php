<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Student Information System</title>
  <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>

<body>
  <div class="container">
    <div class="form-box login">
      <div class="logo-container">
        <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" class="logo">
        <div class="logo-text">
          <h2>Sistem Informasi <br>Manajemen Bimbingan<br>Mahasiswa dan Perwalian<br><span>(SIMBA)</span></h2>
        </div>
      </div>
      <button class="btn register-btn">Login</button>
    </div>


    <div class="form-box register">
      <div class="logo-container">
        <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" class="logo">
        <div class="logo-text">
          <h2>Sistem Informasi <br>Manajemen Bimbingan<br>Mahasiswa dan Perwalian<br><span>(SIMBA)</span></h2>
        </div>
      </div>
      <button class="btn login-btn">Register</button>
    </div>

    <div class="toggle-box">
      <div class="toggle-panel toggle-right">
        <form action="#">
          <h1>Login</h1>
          <div class="input-box">
            <input type="text" placeholder="Username" required>
            <i class="bx bxs-user"></i>
          </div>
          <div class="input-box">
            <input type="password" placeholder="Password" required>
            <i class="bx bxs-lock-alt"></i>
          </div>

          <button type="submit" class="btn">Login</button>
          <p>or login with social platforms</p>

        </form>

      </div>
      <div class="toggle-panel toggle-left">
        <form action="#">
          <h1>Registration</h1>
          <div class="input-box">
            <input type="text" placeholder="Username" required>
            <i class="bx bxs-user"></i>
          </div>
          <div class="input-box">
            <input type="password" placeholder="Password" required>
            <i class="bx bxs-lock-alt"></i>
          </div>
          <div class="input-box">
            <input type="email" placeholder="Email" required>
            <i class="bx bxs-envelope"></i>
          </div>
          <button type="submit" class="btn">Register</button>
          <p>or register with social platforms</p>
        </form>

      </div>
    </div>
  </div>

  <script src="{{ asset('js/app.js') }}"></script>

</body>

</html>