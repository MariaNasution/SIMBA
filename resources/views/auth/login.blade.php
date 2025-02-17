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
      <!-- <button class="btn register-btn">Login</button> -->
    </div>


    <div class="form-box register">
      <div class="logo-container">
        <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" class="logo">
        <div class="logo-text">
          <h2>Sistem Informasi <br>Manajemen Bimbingan<br>Mahasiswa dan Perwalian<br><span>(SIMBA)</span></h2>
        </div>
      </div>
      <!-- <button class="btn login-btn">Register</button> -->
    </div>

    <div class="toggle-box">
      <div class="toggle-panel toggle-right">
        <div class="btn-container">
          <button class="btn register-btn">Daftar</button>
          <button class="btn login-btn">Masuk</button>
        </div>

        <form action="#">
          <div class="input-box">
            <label for="username">Nama Pengguna</label>
            <input type="text" id="username" required>
            <i class="bx bxs-user"></i>
          </div>
          <div class="input-box">
            <label for="password">Kata Sandi</label>
            <input type="password" id="password" required>
            <i class="bx bxs-lock-alt"></i>
          </div>

          <p>Belum Punya Akun? <a href="#" class="register-link">Daftar Disini</a></p>
          <button type="submit" class="btn">Masuk</button>

        </form>
      </div>

      <div class="toggle-panel toggle-left">
        <div class="btn-container">
          <button class="btn register-btn">Daftar</button>
          <button class="btn login-btn">Masuk</button>
        </div>
        <form action="#">
          <!-- <button class="btn register-btn">Login</button> -->
          <!-- <h1>Registration</h1> -->
          <div class="input-box">
            <label for="username">Nama Pengguna</label>
            <input type="text" id="username" required>
            <i class="bx bxs-user"></i>
          </div>
          <div class="input-box">
            <label for="password">Kata Sandi</label>
            <input type="password" id="password" required>
            <i class="bx bxs-lock-alt"></i>
          </div>

          <div class="input-box">
            <label for="role">Jabatan</label>
            <select id="role" required>
              <option value="" disabled selected>Pilih Jabatan</option>
              <option value="admin">Admin (Kemahasiswaan dan DIRDIK Konselor)</option>
              <option value="keasramaan">Keasramaan</option>
              <option value="dosen">Dosen Wali</option>
              <option value="mahasiswa">Mahasiswa</option>
              <option value="orangtua">Orangtua</option>
            </select>
            <i class="bx bx-chevron-down"></i> <!-- Ikon dropdown -->
          </div>
          <p>Sudah Daftar? <a href="#" class="login-link">Masuk Disini</a></p>

          <button type="submit" class="btn">Buat Akun</button>

        </form>
      </div>
    </div>

  </div>

  <script src="{{ asset('js/app.js') }}"></script>

</body>

</html>