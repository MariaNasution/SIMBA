<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - SIMBA</title>
  <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <!-- Add SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Load reCAPTCHA v2 -->
  @if (config('services.recaptcha.enabled') && config('services.recaptcha.site_key'))
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  @else
  @if (!config('services.recaptcha.site_key'))
  <script>
    console.error('reCAPTCHA site key is missing. Check .env RECAPTCHA_SITE_KEY and config/services.php.');
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'error',
        title: 'reCAPTCHA Error',
        text: 'reCAPTCHA configuration is missing. Please contact support or try again later.',
      });
    });
  </script>
  @endif
  @endif
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

    <div class="form-box register">
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
        <form id="login-form" action="{{ route('login.submit') }}" method="POST">
          @csrf
          <div class="input-box">
            <i class="bx bxs-user log"></i>
            <label for="login-username">Nama Pengguna</label>
            <input class="log" type="text" id="login-username" name="username" required>
          </div>
          @if ($errors->has('login'))
          <div style="font-size: 12px; color: red; margin: 0 !important; padding: 0 !important; line-height: 1;">
            {{ $errors->first('login') }}
          </div>
          @endif

          <div class="input-box">
            <i class="bx bxs-lock-alt log"></i>
            <label for="login-password">Kata Sandi</label>
            <input class="log" type="password" id="login-password" name="password" required>
          </div>
          @if ($errors->has('login2'))
          <div style="font-size: 12px; color: red; margin: 0 !important; padding: 0 !important; line-height: 1;">
            {{ $errors->first('login2') }}
          </div>
          @endif

          <!-- reCAPTCHA v2 Widget -->
          @if (config('services.recaptcha.enabled') && config('services.recaptcha.site_key'))
          <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
          @if ($errors->has('g-recaptcha-response'))
          <div style="font-size: 12px; color: red; margin: 0 !important; padding: 0 !important; line-height: 1;">
            {{ $errors->first('g-recaptcha-response') }}
          </div>
          @endif
          @endif

          <p>Belum Punya Akun? <a href="#" class="register-link"
              onclick="document.querySelector('.register-btn').click(); return false;">Daftar Disini</a></p>
          <button type="submit" class="btn">Masuk</button>
        </form>
      </div>

      <div class="toggle-panel toggle-left">
        <div class="btn-container">
          <button class="btn register-btn">Daftar</button>
          <button class="btn login-btn">Masuk</button>
        </div>
        <form id="register-form">
          @csrf
          <div class="input-box">
            <label for="register-username">Nama Pengguna</label>
            <input class="reg" type="text" id="register-username" name="username" required>
            <i class="bx bxs-user reg"></i>
          </div>
          <div class="input-box reg">
            <label for="register-password">Kata Sandi</label>
            <input class="reg" type="password" id="register-password" name="password" required>
            <i class="bx bxs-lock-alt reg"></i>
          </div>
          <div class="input-box">
            <label for="role">Jabatan</label>
            <select id="role" name="jabatan" required>
              <option value="" disabled selected>Pilih Jabatan</option>
              <option value="kemahasiswaan">Kemahasiswaan</option>
              <option value="konselor">Konselor</option>
              <option value="keasramaan">Keasramaan</option>
              <option value="dosen">Dosen Wali</option>
              <option value="mahasiswa">Mahasiswa</option>
              <option value="orangtua">Orangtua</option>
            </select>
            <i class="bx bx-chevron-down jabatan"></i>
          </div>
          <p>Sudah Daftar? <a href="#" class="login-link"
              onclick="document.querySelector('.login-btn').click(); return false;">Masuk Disini</a></p>
          <button type="submit" class="btn">Buat Akun</button>
        </form>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener("DOMContentLoaded", function() {
    const container = document.querySelector(".container");
    const loginForm = document.getElementById("login-form");

    // Tambahkan class no-transition untuk mencegah semua transisi saat halaman pertama kali dimuat
    container.classList.add("no-transition");

    // Ambil state aktif dari sessionStorage
    const currentState = sessionStorage.getItem("activeSection");
    const isActive = currentState === "register"; // True jika di state "register"

    // Langsung perbarui tampilan sesuai state tanpa transisi
    if (isActive) {
      container.classList.remove("active");
    } else {
      container.classList.add("active");
    }

    // Tunggu sebentar sebelum menghapus no-transition untuk memastikan tidak ada transisi awal
    setTimeout(() => container.classList.remove("no-transition"), 100);

    // Tambahkan event listener untuk tombol toggle
    document.querySelectorAll(".register-btn").forEach(button => {
      button.addEventListener("click", () => {
        container.classList.remove("active");
        sessionStorage.setItem("activeSection", "register");
      });
    });

    document.querySelectorAll(".login-btn").forEach(button => {
      button.addEventListener("click", () => {
        container.classList.add("active");
        sessionStorage.setItem("activeSection", "login");
      });
    });

    // Handle reCAPTCHA v2 form submission
    @if (config('services.recaptcha.enabled') && config('services.recaptcha.site_key'))
    loginForm.addEventListener("submit", function(e) {
      const recaptchaResponse = document.getElementById('g-recaptcha-response')?.value;
      if (!recaptchaResponse) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: 'reCAPTCHA Required',
          text: 'Please complete the reCAPTCHA checkbox.',
        });
      }
    });
    @endif
  });
  </script>

  <!-- Script alert registrasi -->
  <script>
  document.addEventListener("DOMContentLoaded", function() {
    const registerForm = document.getElementById("register-form");

    registerForm.addEventListener("submit", function(e) {
      e.preventDefault();

      const formData = new FormData(registerForm);

      fetch("{{ route('register.submit') }}", {
          method: "POST",
          headers: {
            "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
            "Accept": "application/json"
          },
          body: formData
        })
        .then(response => {
          if (!response.ok) return response.json().then(err => {
            throw err;
          });
          return response.json();
        })
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Sukses',
              text: data.message
            }).then(() => {
              // Arahkan ke form PMID
              document.querySelector('.login-btn').click();
              registerForm.reset();
            });
          }
        })
        .catch(error => {
          let errorMsg = error?.errors?.username?.[0] || error.message || 'Registrasi gagal. Coba lagi.';

          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMsg
          });
        });
    });
  });
  </script>

</body>

</html>