<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - SIMBA</title>
  <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
  <div class="container active">
    <div class="form-box login">
        <div class="logo-container">
            <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" class="logo">
            <div class="logo-text">
                <h2>Sistem Informasi <br>Manajemen Bimbingan<br>Mahasiswa dan Perwalian<br><span>(SIMBA)</span></h2>
            </div>
        </div>
    </div>

    <div class="form-box">
        <div class="logo-container">
            <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" class="logo">
            <div class="logo-text">
                <h2>Sistem Informasi <br>Manajemen Bimbingan<br>Mahasiswa dan Perwalian<br><span>(SIMBA)</span></h2>
            </div>
        </div>
    </div>

    <div class="toggle-box">
        <div class="toggle-panel toggle-right">
            <div class="btn-container">
                <button class="btn register-btn">Daftar</button>
                <button class="btn login-btn">Masuk</button>
            </div>
            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                <div class="input-box">
                    <i class="bx bxs-user log"></i>
                    <label for="nim">Nama Pengguna</label>
                    <input class="log" type="text" id="nim" name="nim" required>
                </div>
                <div class="input-box">
                    <i class="bx bxs-lock-alt log"></i>
                    <label for="password">Kata Sandi</label>
                    <input class="log" type="password" id="password" name="password" required>
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
            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                <div class="input-box">
                    <label for="nim">Nama Pengguna</label>
                    <input class="reg" type="text" id="nim" name="nim" required>
                    <i class="bx bxs-user reg"></i>
                </div>
                <div class="input-box reg">
                    <label for="password">Kata Sandi</label>
                    <input class="reg" type="password" id="password" name="password" required>
                    <i class="bx bxs-lock-alt reg"></i>
                </div>
                <div class="input-box">
                    <label for="role">Jabatan</label>
                    <select id="role" required>
                        <option value="" disabled selected><span class="option-text">Pilih Jabatan</span></option>
                        <option value="admin"><span class="option-text">Admin (Kemahasiswaan dan DIRDIK Konselor)</span></option>
                        <option value="keasramaan"><span class="option-text">Keasramaan</span></option>
                        <option value="dosen"><span class="option-text">Dosen Wali</span></option>
                        <option value="mahasiswa"><span class="option-text">Mahasiswa</span></option>
                        <option value="orangtua"><span class="option-text">Orangtua</span></option>
                    </select>
                    <i class="bx bx-chevron-down jabatan"></i>
                </div>
                <p>Sudah Daftar? <a href="#" class="login-link">Masuk Disini</a></p>
                <button type="submit" class="btn">Buat Akun</button>
            </form>
        </div>
    </div>
</div>

  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
    // Retrieve the saved state from localStorage
    const currentState = sessionStorage.getItem("activeSection");

    // Select necessary elements
    const container = document.querySelector(".container");
    const toggleRight = document.querySelector(".toggle-panel.toggle-right");
    const toggleLeft = document.querySelector(".toggle-panel.toggle-left");

    // Function to switch to the login section
    function showLoginSection() {
        container.classList.remove("active");
        sessionStorage.setItem("activeSection", "login");
    }

    // Function to switch to the register section
    function showRegisterSection() {
        container.classList.add("active");
        sessionStorage.setItem("activeSection", "register");
    } 

    // Check the saved state and apply it
    if (currentState === "register") {
        showRegisterSection();
    } else if (currentState === "login"){
        showLoginSection();
    }

    // Add event listeners for the toggle buttons
    document.querySelectorAll(".register-btn").forEach(button => {
        button.addEventListener("click", showRegisterSection);
    });

    document.querySelectorAll(".login-btn").forEach(button => {
        button.addEventListener("click", showLoginSection);
    });
});
  </script>
  
</body>

</html>