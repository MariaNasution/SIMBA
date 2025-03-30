<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMBA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/mingcute-icon@latest/dist/mingcute.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        #kemajuanStudiChart {
            width: 100%;
            max-width: 600px;
            height: 300px;
            display: block;
        }

        .card {
            overflow: hidden;
        }
    </style>
    <!-- Yield additional styles from child views -->
    @yield('styles')
</head>

<body>
    <div class="app-wrapper">
        @if (session('user.role') === 'admin')
            @include('components.sidebarAdmin')
        @elseif(session('user.role') === 'mahasiswa')
            @include('components.sidebarMahasiswa')
        @elseif(session('user.role') === 'dosen')
            @include('components.sidebarDosen')
        @elseif(session('user.role') === 'keasramaan')
            @include('components.sidebarKeasramaan')
        @elseif(session('user.role') === 'orang_tua')
            @include('components.sidebarOrangTua')
        @else
            <p class="text-center text-danger">Role tidak dikenali.</p>
        @endif

        <main>
            @yield('content')
        </main>

        <footer class="text-center py-3">
            <p>© {{ date('Y') }} @Kelompok 1 IF 1</p>
        </footer>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartElement = document.getElementById('kemajuanStudiChart');
            if (chartElement) {
                const ctx = chartElement.getContext('2d');
                const labels = @json($labels ?? []);
                const data = @json($values ?? []);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Kemajuan Studi',
                            data: data,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Semester'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'IP Semester'
                                },
                                beginAtZero: true
                            }
                        }
                    }
                });
            } else {
                console.log('kemajuanStudiChart element not found on this page');
            }
        });
    </script>

    <script>
        function toggleSubMenu(submenuId) {
            const submenu = document.getElementById(submenuId);
            const toggleIcon = document.querySelector(`#${submenuId}-toggle`);

            if (submenu.style.display === "none" || submenu.style.display === "") {
                submenu.style.display = "block";
                toggleIcon.classList.add("open");
            } else {
                submenu.style.display = "none";
                toggleIcon.classList.remove("open");
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Apakah anda yakin ingin keluar?',
                text: "Anda akan keluar dari akun ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, keluar!',
                cancelButtonText: 'Tidak',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{{ route('logout') }}';
                }
            });
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pengumumanModal = document.getElementById('pengumumanModal');
            if (pengumumanModal) {
                const modalTitle = document.getElementById('pengumumanModalLabel');
                const modalBody = document.getElementById('pengumumanDeskripsi');

                pengumumanModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const judul = button.getAttribute('data-judul');
                    const deskripsi = button.getAttribute('data-deskripsi');
                    console.log('Judul:', judul);
                    console.log('Deskripsi:', deskripsi);
                    modalTitle.textContent = judul;
                    modalBody.textContent = deskripsi;
                });
            } else {
                console.log('pengumumanModal element not found on this page');
            }
        });
    </script>
</body>

</html>
