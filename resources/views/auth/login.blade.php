<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMBA</title>
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- Add SweetAlert2 CSS and JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <label for="username">Nama Pengguna</label>
                        <input class="log" type="text" id="username" name="username" required>
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
                <form id = "register-form" action="{{ route('register.submit') }}" method="POST">
                    @csrf
                    <div class="input-box">
                        <label for="username">Nama Pengguna</label>
                        <input class="reg" type="text" id="username" name="username" required>
                        <i class="bx bxs-user reg"></i>
                    </div>
                    <div class="input-box reg">
                        <label for="password">Kata Sandi</label>
                        <input class="reg" type="password" id="password" name="password" required>
                        <i class="bx bxs-lock-alt reg"></i>
                    </div>
                    <div class="input-box">
                        <label for="role">Jabatan</label>
                        <select id="role" name="jabatan" required>
                            <option value="" disabled selected><span class="option-text">Pilih Jabatan</span>
                            </option>
                            <option value="admin"><span class="option-text">Admin (Kemahasiswaan dan DIRDIK
                                    Konselor)</span></option>
                            <option value="keasramaan"><span class="option-text">Keasramaan</span></option>
                            <option value="dosen"><span class="option-text">Dosen Wali</span></option>
                            <option value="mahasiswa"><span class="option-text">Mahasiswa</span></option>
                            <option value="orang_tua"><span class="option-text">Orangtua</span></option>
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
        document.addEventListener("DOMContentLoaded", function() {
            // Retrieve the saved state from sessionStorage
            const currentState = sessionStorage.getItem("activeSection");
            let isActive = currentState === "register"; // True if register is active

            // Select necessary elements
            const container = document.querySelector(".container");

            // Function to update the UI based on the active state
            function updateUI(isActive) {
                // Temporarily disable transitions during initialization
                const formBoxes = document.querySelectorAll(".form-box");
                formBoxes.forEach((box) => {
                    box.style.transition = "none";
                });

                if (isActive) {
                    container.classList.remove("active");
                    sessionStorage.setItem("activeSection", "register");
                } else {
                    container.classList.add("active");
                    sessionStorage.setItem("activeSection", "login");
                }

                // Force reflow to apply the changes immediately
                void container.offsetWidth;

                // Re-enable transitions after initialization
                formBoxes.forEach((box) => {
                    box.style.transition = "";
                });
            }

            // Initialize the UI based on the saved state
            updateUI(isActive);

            // Add event listeners for the toggle buttons
            document.querySelectorAll(".register-btn").forEach((button) => {
                button.addEventListener("click", () => updateUI(true));
            });

            document.querySelectorAll(".login-btn").forEach((button) => {
                button.addEventListener("click", () => updateUI(false));
            });

            // Handle form submission with SweetAlert2
            document.getElementById('register-form').addEventListener('submit', async function(e) {
                e.preventDefault(); // Prevent default form submission

                const form = e.target;
                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Show success notification
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: result.message,
                        }).then(() => {
                            // Switch to the login section by setting isActive to false
                            isActive = false;
                            updateUI(isActive);
                        });
                    } else {
                        // Show error notification
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: result.message,
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat mengirim permintaan.',
                    });
                }
            });
        });
    </script>

</body>

</html>
