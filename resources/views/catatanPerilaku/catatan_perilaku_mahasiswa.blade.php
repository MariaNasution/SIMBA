@extends('layouts.app')

<link rel="stylesheet" href="{{ url('assets/css/catatan_perilaku.css') }}">

<!-- Tambahkan CSS untuk animasi dropdown -->

@section('content')
<!-- Header -->

<div class="container mt-4">
  <p class="text-center mb-4">Daftar Nilai Perilaku Mahasiswa</p>
  <hr class="mb-4">

  <!-- Tabel -->
  <table class="table">
    <thead>
      <tr>
        <th style="width: 5%">No</th>
        <th>TA</th>
        <th>Semester</th>
        <th>Skor Awal</th>
        <th>Akumulasi Skor</th>
        <th>Nilai Huruf</th>
      </tr>
    </thead>
    <tbody>
      @php $index = 1; @endphp
      @forelse ($nilaiPerilaku as $key => $perilaku)
      <tr>
        <td style="width: 5%">{{ $index++ }}</td>
        <td>{{ $perilaku['ta'] ?? '-' }}</td>
        <td>{{ $perilaku['semester'] ?? '-' }}</td>
        <td>{{ $perilaku['akumulasi_skor_awal'] ?? 0 }}</td>
        <td>{{ $perilaku['akumulasi_skor'] ?? 0 }}</td>
        <td class="nilai-huruf-cell">
          {{ $perilaku['nilai_huruf'] ?? '-' }}
          <!-- Tombol toggle collapse -->
          <a href="#" class="dropdown-icon" data-bs-toggle="collapse" data-bs-target="#details{{ $key }}">
            <i class="fas fa-chevron-left"></i>
          </a>
        </td>
      </tr>

      <!-- Row detail dengan animasi collapse -->
      <tr class="collapse" id="details{{ $key }}">
        <td colspan="7">
          <!-- Bungkus isi detail di dalam .collapse-content untuk efek fade+slide -->
          <div class="collapse-content p-3">
            <h5 style="font-size: 1.1rem; text-align: left;">Pembinaan: </h5>

            <!-- Container untuk Kotak Pelanggaran dan Perbuatan Baik -->
            <div class="custom-box-container">
              <!-- Kotak Pelanggaran -->
              <div id="pelanggaranBox{{ $key }}" class="custom-box active"
                onclick="showTable('pelanggaranTable{{ $key }}', 'pelanggaranBox{{ $key }}', 'perbuatanBaikBox{{ $key }}')">
                Pelanggaran ({{ count($perilaku['pelanggaran'] ?? []) }})
              </div>

              <!-- Kotak Perbuatan Baik -->
              <div id="perbuatanBaikBox{{ $key }}" class="custom-box"
                onclick="showTable('perbuatanBaikTable{{ $key }}', 'perbuatanBaikBox{{ $key }}', 'pelanggaranBox{{ $key }}')">
                Perbuatan Baik Baru ({{ count($perilaku['perbuatan_baik'] ?? []) }})
              </div>
            </div>

            <!-- Tabel Pelanggaran -->
            <div id="pelanggaranTable{{ $key }}">
              <table class="table table-bordered table-striped" style="margin-top: 0; border-top: none;">
                <thead>
                  <tr>
                    <th style="width: 0.7%">#</th>
                    <th>Pelanggaran</th>
                    <th>Unit</th>
                    <th>Tanggal</th>
                    <th>Poin</th>
                    <th>Tindakan</th>
                  </tr>
                </thead>
                <tbody>
                  @php $pelanggaranIndex = 1; @endphp
                  @forelse ($perilaku['pelanggaran'] ?? [] as $pelanggaran)
                  <tr>
                    <td style="width: 5%">{{ $pelanggaranIndex++ }}</td>
                    <td>{{ $pelanggaran['pelanggaran'] ?? '-' }}</td>
                    <td>{{ $pelanggaran['unit'] ?? '-' }}</td>
                    <td>{{ $pelanggaran['tanggal'] ?? '-' }}</td>
                    <td>{{ $pelanggaran['poin'] ?? 0 }}</td>
                    <td>{{ $pelanggaran['tindakan'] ?? '-' }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="6" class="no-results">No results found.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <!-- Tabel Perbuatan Baik -->
            <div id="perbuatanBaikTable{{ $key }}" style="display: none;">
              <table class="table table-bordered table-striped" style="margin-top: 0; border-top: none;">
                <thead>
                  <tr>
                    <th style="width: 0.7%">#</th>
                    <th>Perbuatan Baik</th>
                    <th>Keterangan</th>
                    <th>Kredit Kebaikan Poin</th>
                    <th>Unit</th>
                    <th>Tanggal</th>
                  </tr>
                </thead>
                <tbody>
                  @php $perbuatanBaikIndex = 1; @endphp
                  @forelse ($perilaku['perbuatan_baik'] ?? [] as $perbuatan)
                  <tr>
                    <td style="width: 5%">{{ $perbuatanBaikIndex++ }}</td>
                    <td>{{ $perbuatan['perbuatan_baik'] ?? '-' }}</td>
                    <td>{{ $perbuatan['keterangan'] ?? '-' }}</td>
                    <td style="width: 15%">{{ $perbuatan['kredit_poin'] ?? 0 }}</td>
                    <td>{{ $perbuatan['unit'] ?? '-' }}</td>
                    <td>{{ $perbuatan['tanggal'] ?? '-' }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="6" class="no-results">No results found.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
              // Loop melalui semua tombol dropdown
              document.querySelectorAll('.dropdown-icon').forEach(function(dropdown) {
                const target = dropdown.getAttribute('data-bs-target');
                const collapseElement = document.querySelector(target);

                // Event listener saat dropdown dibuka
                collapseElement.addEventListener('show.bs.collapse', function() {
                  toggleDropdownIcon(dropdown, true); // Ikon berubah saat dropdown dibuka

                  // Dapatkan key dari data-bs-target
                  const key = target.replace('#details', '');

                  // Tampilkan tabel pelanggaran secara otomatis saat dropdown dibuka
                  showTable(`pelanggaranTable${key}`, `pelanggaranBox${key}`, `perbuatanBaikBox${key}`);

                  // Tambahkan animasi fade-in saat tabel muncul
                  const table = document.getElementById(`pelanggaranTable${key}`);
                  if (table) {
                    table.style.opacity = '0';
                    table.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                      table.style.opacity = '1';
                      table.style.transform = 'translateY(0)';
                    }, 150);
                  }
                });

                // Event listener saat dropdown ditutup
                collapseElement.addEventListener('hide.bs.collapse', function() {
                  toggleDropdownIcon(dropdown, false); // Ikon kembali saat dropdown ditutup

                  // Tambahkan animasi fade-out saat tabel menghilang
                  const key = target.replace('#details', '');
                  const table = document.getElementById(`pelanggaranTable${key}`);
                  if (table) {
                    table.style.opacity = '1';
                    table.style.transform = 'translateY(0)';
                    setTimeout(() => {
                      table.style.opacity = '0';
                      table.style.transform = 'translateY(-10px)';
                    }, 100);
                  }
                });
              });

              // Pastikan ikon berubah jika dropdown dalam keadaan terbuka saat pertama dimuat
              document.querySelectorAll('.collapse.show').forEach(function(collapse) {
                const key = collapse.id.replace('details', '');
                const dropdown = document.querySelector(`[data-bs-target="#details${key}"]`);
                if (dropdown) {
                  toggleDropdownIcon(dropdown, true);
                }
                showTable(`pelanggaranTable${key}`, `pelanggaranBox${key}`, `perbuatanBaikBox${key}`);
              });

              // Tambahkan event listener untuk kotak Pelanggaran / Perbuatan Baik
              document.querySelectorAll('.custom-box').forEach(function(box) {
                box.addEventListener('click', function() {
                  const key = this.id.replace('pelanggaranBox', '').replace('perbuatanBaikBox', '');
                  if (this.id.startsWith('pelanggaranBox')) {
                    showTable(`pelanggaranTable${key}`, `pelanggaranBox${key}`, `perbuatanBaikBox${key}`);
                  } else if (this.id.startsWith('perbuatanBaikBox')) {
                    showTable(`perbuatanBaikTable${key}`, `perbuatanBaikBox${key}`, `pelanggaranBox${key}`);
                  }

                  // Tambahkan animasi fade-in saat tabel muncul
                  const activeTable = document.getElementById(this.id.startsWith('pelanggaranBox') ?
                    `pelanggaranTable${key}` : `perbuatanBaikTable${key}`);
                  if (activeTable) {
                    activeTable.style.opacity = '0';
                    activeTable.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                      activeTable.style.opacity = '1';
                      activeTable.style.transform = 'translateY(0)';
                    }, 150);
                  }
                });
              });
            });

            // Fungsi untuk menampilkan tabel dan mengatur kotak aktif
            function showTable(tableId, activeBoxId, inactiveBoxId) {
              const currentDropdown = document.getElementById(activeBoxId).closest('.collapse');

              // Sembunyikan semua tabel di dalam dropdown yang sedang aktif
              currentDropdown.querySelectorAll('[id^="pelanggaranTable"], [id^="perbuatanBaikTable"]').forEach(function(
                table) {
                table.style.display = 'none';
              });

              // Tampilkan tabel yang dipilih di dalam dropdown yang sedang aktif
              if (tableId) {
                document.getElementById(tableId).style.display = 'block';
              }

              // Tambahkan kelas 'active' ke kotak yang dipilih dan hapus dari kotak lain
              if (activeBoxId) {
                document.getElementById(activeBoxId).classList.add('active');
              }
              if (inactiveBoxId) {
                document.getElementById(inactiveBoxId).classList.remove('active');
              }
            }

            // Fungsi untuk mengubah ikon dropdown
            function toggleDropdownIcon(element, isOpen) {
              const icon = element.querySelector('i');
              if (isOpen) {
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-down');
              } else {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-left');
              }
            }
            </script>


          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="text-center">Tidak ada data perilaku.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection