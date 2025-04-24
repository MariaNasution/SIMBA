<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

class BreadCrumbService
{
    public function generateBreadcrumbs($params = [])
    {
        $currentRoute = Route::currentRouteName();
        $user = session('user');
        $role = $user ? $user['role'] : null;

        if (!$role) {
            return [
                ['name' => '<i class="fas fa-home"></i> Home', 'url' => url('/')],
            ];
        }

        switch ($role) {
            case 'dosen':
                return $this->generateDosenBreadcrumbs($currentRoute, $params);
            case 'mahasiswa':
                return $this->generateMahasiswaBreadcrumbs($currentRoute, $params);
            case 'konselor':
                return $this->generateKonselorBreadcrumbs($currentRoute, $params);
            case 'kemahasiswaan':
                return $this->generateKemahasiswaanBreadcrumbs($currentRoute, $params);
            case 'admin':
                return $this->generateAdminBreadcrumbs($currentRoute, $params);
            case 'orang_tua':
                return $this->generateOrangTuaBreadcrumbs($currentRoute, $params);
            case 'keasramaan':
                return $this->generateKeasramaanBreadcrumbs($currentRoute, $params);
            default:
                return [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => url('/')],
                    ['name' => 'Unknown Page', 'url' => null],
                ];
        }
    }

    protected function generateKeasramaanBreadcrumbs($currentRoute, $params = [])
    {
        $breadcrumbs = [];

        switch ($currentRoute) {
            case 'keasramaan':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => null],
                ];
                break;

            case 'pelanggaran_keasramaan':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('keasramaan')],
                    ['name' => 'Catatan Perilaku', 'url' => null],
                ];
                break;

            case 'catatan_perilaku_detail':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('keasramaan')],
                    ['name' => 'Catatan Perilaku', 'url' => route('pelanggaran_keasramaan')],
                    ['name' => 'Detail Catatan Perilaku', 'url' => null],
                ];
                break;

            default:
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('keasramaan')],
                    ['name' => 'Halaman Tidak Dikenal', 'url' => null],
                ];
        }

        return $breadcrumbs;
    }

    protected function generateOrangTuaBreadcrumbs($currentRoute, $params = [])
    {
        $breadcrumbs = [];

        switch ($currentRoute) {
            case 'orang_tua':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => null],
                ];
                break;

            case 'catatan_perilaku_orang_tua':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-edit"></i> Catatan Perilaku', 'url' => null],
                ];
                break;

            default:
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('orang_tua')],
                    ['name' => 'Halaman Tidak Dikenal', 'url' => null],
                ];
        }

        return $breadcrumbs;
    }

    protected function generateAdminBreadcrumbs($currentRoute, $params = [])
    {
        $breadcrumbs = [];

        switch ($currentRoute) {
            case 'admin.beranda':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => null],
                ];
                break;

            case 'admin.users.index':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-users-cog"></i> Kelola Pengguna', 'url' => null],
                ];
                break;

            case 'admin.users.create':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-plus"></i> Tambah Pengguna', 'url' => null],
                ];
                break;

            case 'admin.users.edit':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-users-cog"></i> Kelola Pengguna', 'url' => route('admin.users.index')],
                    ['name' => 'Edit Pengguna', 'url' => null],
                ];
                break;

            default:
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('admin.beranda')],
                    ['name' => 'Halaman Tidak Dikenal', 'url' => null],
                ];
                break;
        }

        return $breadcrumbs;
    }

    protected function generateDosenBreadcrumbs($currentRoute, $params = [])
    {
        $breadcrumbs = [];

        switch ($currentRoute) {
            case 'dosen':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => null],
                ];
                break;

            case 'dosen.perwalian':
                $breadcrumbs = [
                    ['name' => 'Perwalian Kelas', 'url' => null],
                ];
                break;

            case 'dosen.presensi':
                $breadcrumbs = [
                    ['name' => 'Presensi', 'url' => null],
                ];
                break;

            case 'dosen.detailedClass':
                $year = $params['year'] ?? 'N/A';
                $kelas = $params['kelas'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('dosen')],
                    ['name' => "Detail Kelas $kelas ($year)", 'url' => null],
                ];
                break;

            case 'set.perwalian':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-users"></i> Set Perwalian', 'url' => null],
                ];
                break;

            case 'set.perwalian.store':
                $breadcrumbs = [
                    ['name' => 'Set Perwalian', 'url' => route('set.perwalian')],
                    ['name' => 'Simpan Perwalian', 'url' => null],
                ];
                break;

            case 'set.perwalian.destroy':
                $breadcrumbs = [
                    ['name' => 'Set Perwalian', 'url' => route('set.perwalian')],
                    ['name' => 'Hapus Perwalian', 'url' => null],
                ];
                break;

            case 'set.perwalian.calendar':
                $breadcrumbs = [
                    ['name' => 'Set Perwalian', 'url' => route('set.perwalian')],
                    ['name' => 'Kalender Perwalian', 'url' => null],
                ];
                break;

            case 'dosen.histori':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-history"></i> Histori', 'url' => null],
                ];
                break;

            case 'dosen.histori.detailed':
                $id = $params['id'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-history"></i> Histori', 'url' => route('dosen.histori')],
                ];
                break;

            case 'berita_acara.print':
                $id = $params['id'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-history"></i> Histori', 'url' => route('dosen.histori')],
                ];
                break;

            case 'absensi':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-check-square"></i> Absensi Mahasiswa', 'url' => null],
                ];
                break;

            case 'absensi.show':
                $date = $params['date'] ?? 'N/A';
                $class = $params['class'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-check"></i> Absensi Mahasiswa', 'url' => route('absensi')],
                    ['name' => "Absensi $class - $date", 'url' => null],
                ];
                break;

            case 'absensi.store':
                $date = $params['date'] ?? 'N/A';
                $class = $params['class'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-check"></i> Absensi Mahasiswa', 'url' => route('absensi')],
                    ['name' => "Simpan Absensi $class - $date", 'url' => null],
                ];
                break;

            case 'absensi.completed':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-check"></i> Absensi Mahasiswa', 'url' => route('absensi')],
                    ['name' => 'Absensi Selesai', 'url' => null],
                ];
                break;

            case 'berita_acara.index':
            case 'perwalian.berita_acara':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-book"></i> Berita Acara', 'url' => null],
                ];
                break;

            case 'berita_acara.select_class':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-book"></i> Berita Acara', 'url' => route('berita_acara.index')],
                    ['name' => 'Pilih Kelas', 'url' => null],
                ];
                break;

            case 'berita_acara.create':
                $date = $params['date'] ?? 'N/A';
                $class = $params['class'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-book"></i> Berita Acara', 'url' => route('berita_acara.index')],
                    ['name' => "Buat Berita Acara $class - $date", 'url' => null],
                ];
                break;

            case 'berita_acara.store':
                $date = $params['date'] ?? 'N/A';
                $class = $params['class'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-book"></i> Berita Acara', 'url' => route('berita_acara.index')],
                    ['name' => "Simpan Berita Acara $class - $date", 'url' => null],
                ];
                break;

            case 'berita_acara.success':
                $kelas = $params['kelas'] ?? 'N/A';
                $tanggal_perwalian = $params['tanggal_perwalian'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-book"></i> Berita Acara', 'url' => route('berita_acara.index')],
                ];
                break;

            default:
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('dosen')],
                    ['name' => 'Unknown Page', 'url' => null],
                ];
                break;
        }

        return $breadcrumbs;
    }

    protected function generateKonselorBreadcrumbs($currentRoute, $params = [])
    {
        $breadcrumbs = [];

        switch ($currentRoute) {
            case 'konselor_beranda':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => null],
                ];
                break;

            case 'konselor_pelanggaran.daftar':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-list"></i> Konseling', 'url' => route('konselor_beranda')],
                    ['name' => 'Daftar Pelanggaran', 'url' => null],
                ];
                break;

            case 'konselor_pengumumankonselor.detail':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-bullhorn"></i> Beranda', 'url' => route('konselor_beranda')],
                    ['name' => 'Detail Pengumuman', 'url' => null],
                ];
                break;

            case 'konselor_daftar_pelanggaran':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-list"></i> Konseling', 'url' => route('konselor_beranda')],
                    ['name' => 'Daftar Pelanggaran', 'url' => null],
                ];
                break;

            case 'konselor_hasil_konseling':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-book"></i> Konseling', 'url' => route('konselor_beranda')],
                    ['name' => 'Hasil Konseling', 'url' => null],
                ];
                break;

            case 'konselor_riwayat_konseling':
            case 'konselor_riwayat.konseling':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-history"></i> Konseling', 'url' => route('konselor_beranda')],
                    ['name' => 'Riwayat Konseling', 'url' => null],
                ];
                break;

            case 'konselor_riwayat.konseling.cari':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-history"></i> Konseling', 'url' => route('konselor_beranda')],
                    ['name' => 'Riwayat Konseling', 'url' => null],
                ];
                break;

            case 'konselor_konseling_lanjutan':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-forward"></i> Konseling', 'url' => route('konselor_beranda')],
                    ['name' => 'Konseling Lanjutan', 'url' => null],
                ];
                break;

            case 'konselor_ajukan_konseling':
            case 'konselor_konseling.ajukan.form':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-friends"></i> Konseling', 'url' => route('konselor_beranda')],
                    ['name' => 'Ajukan Konseling', 'url' => null],
                ];
                break;

            case 'konselor_konseling.form':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-friends"></i> Konseling', 'url' => route('konselor_beranda')],
                    ['name' => 'Ajukan Konseling', 'url' => null],
                ];
                break;

            case 'konselor_konseling.cari':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-friends"></i> Konseling', 'url' => route('konselor_beranda')],
                    ['name' => 'Ajukan Konseling', 'url' => null],
                ];
                break;

            case 'konselor_konseling.pilih':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-friends"></i> Konseling', 'url' => route('konselor_beranda')],
                    ['name' => 'Ajukan Konseling', 'url' => null],
                ];
                break;

            case 'konselor_konseling.ajukan':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-friends"></i> Konseling', 'url' => route('konselor_beranda')],
                    ['name' => 'Ajukan Konseling', 'url' => null],
                ];
                break;

            case 'konselor_daftar_request':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-book-open"></i> Konseling', 'url' => route('konselor_beranda')],
                    ['name' => 'Daftar Request', 'url' => null],
                ];
                break;

            case 'konselor_riwayat_daftar_request':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-clock"></i> Konseling', 'url' => route('konselor_beranda')],
                    ['name' => 'Riwayat Daftar Request', 'url' => null],
                ];
                break;

            case 'konselor_hasil.index':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-folder-open"></i> Data Hasil Konseling', 'url' => null],
                ];
                break;

            case 'konselor_hasil.show':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-folder-open"></i> Data Hasil Konseling', 'url' => route('konselor_hasil.index')],
                    ['name' => 'Detail Hasil', 'url' => null],
                ];
                break;

            case 'konselor_konseling.lanjutan.detail':
                $nim = $params['nim'] ?? 'NIM Tidak Diketahui';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-forward"></i> Konseling', 'url' => route('konselor_beranda')],
                    ['name' => 'Konseling Lanjutan', 'url' => route('konselor_konseling_lanjutan')],
                    ['name' => "Konseling Lanjutan Detail - $nim", 'url' => null],
                ];
                break;

            case 'konselor_riwayat.konseling.detail':
                $nim = $params['nim'] ?? 'NIM Tidak Diketahui';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-history"></i> Konseling', 'url' => route('konselor_beranda')],
                    ['name' => 'Riwayat Konseling', 'url' => route('konselor_riwayat.konseling')],
                    ['name' => "Riwayat Konseling Detail - $nim", 'url' => null],
                ];
                break;

            default:
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('konselor_beranda')],
                    ['name' => 'Halaman Tidak Dikenal', 'url' => null],
                ];
        }

        return $breadcrumbs;
    }

    protected function generateKemahasiswaanBreadcrumbs($currentRoute, $params = [])
    {
        $breadcrumbs = [];

        switch ($currentRoute) {
            case 'kemahasiswaan_beranda':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_pelanggaran.daftar':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-list"></i> Konseling', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Daftar Pelanggaran', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_pengumumankemahasiswaan.detail':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-bullhorn"></i> Beranda', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Detail Pengumuman', 'url' => null],
                ];
                break;


            //Group Perwalian
            case 'kemahasiswaan_perwalian.jadwal':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-clock"></i> Perwalian', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Jadwalkan Perwalian', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_perwalian.store':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-clock"></i> Perwalian', 'url' => route('kemahasiswaan_perwalian.jadwal')],
                    ['name' => 'Simpan Jadwal', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_perwalian.kelas':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-chalkboard-teacher"></i> Perwalian', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Perwalian Kelas', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_perwalian.berita_acara':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-book-open"></i> Perwalian', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Berita Acara', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_perwalian.berita_acara.search':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-book-open"></i> Perwalian', 'url' => route('kemahasiswaan_perwalian.jadwal')],
                    ['name' => 'Berita Acara', 'url' => route('kemahasiswaan_perwalian.berita_acara')],
                    ['name' => 'Cari Berita Acara', 'url' => null],
                ];
                break;

            // Grup Konseling
            case 'kemahasiswaan_daftar_pelanggaran':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-list"></i> Konseling', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Daftar Pelanggaran', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_hasil_konseling':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-book"></i> Konseling', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Hasil Konseling', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_riwayat_konseling':
            case 'kemahasiswaan_riwayat.konseling':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-history"></i> Konseling', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Riwayat Konseling', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_riwayat.konseling.cari':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-history"></i> Konseling', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Riwayat Konseling', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_konseling_lanjutan':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-forward"></i> Konseling', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Konseling Lanjutan', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_ajukan_konseling':
            case 'kemahasiswaan_konseling.ajukan.form':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-friends"></i> Konseling', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Ajukan Konseling', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_konseling.form':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-friends"></i> Konseling', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Ajukan Konseling', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_konseling.cari':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-friends"></i> Konseling', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Ajukan Konseling', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_konseling.pilih':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-friends"></i> Konseling', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Ajukan Konseling', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_konseling.ajukan':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-friends"></i> Konseling', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Ajukan Konseling', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_daftar_request':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-book-open"></i> Konseling', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Daftar Request', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_riwayat_daftar_request':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-clock"></i> Konseling', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Riwayat Daftar Request', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_hasil.index':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-folder-open"></i> Data Hasil Konseling', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_hasil.show':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-folder-open"></i> Data Hasil Konseling', 'url' => route('kemahasiswaan_hasil.index')],
                    ['name' => '<i class="fas fa-eye"></i> Detail Hasil', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_konseling.lanjutan.detail':
                $nim = $params['nim'] ?? 'NIM Tidak Diketahui';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-forward"></i> Konseling', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Konseling Lanjutan', 'url' => route('kemahasiswaan_konseling_lanjutan')],
                    ['name' => "Konseling Lanjutan Detail - $nim", 'url' => null],
                ];
                break;

            case 'kemahasiswaan_riwayat.konseling.detail':
                $nim = $params['nim'] ?? 'NIM Tidak Diketahui';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-history"></i> Konseling', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Riwayat Konseling', 'url' => route('kemahasiswaan_riwayat.konseling')],
                    ['name' => "Riwayat Konseling Detail - $nim", 'url' => null],
                ];
                break;

            default:
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => '<i class="fas fa-question-circle"></i> Halaman Tidak Dikenal', 'url' => null],
                ];
        }

        return $breadcrumbs;
    }

    protected function generateMahasiswaBreadcrumbs($currentRoute, $params = [])
    {
        $breadcrumbs = [];

        switch ($currentRoute) {
            case 'beranda':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => null],
                ];
                break;

            case 'profil':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                    ['name' => 'Profil', 'url' => null],
                ];
                break;

            case 'kemajuan_studi':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                    ['name' => 'Kemajuan Studi', 'url' => null],
                ];
                break;

            case 'detailnilai':
                $kode_mk = $params['kode_mk'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                    ['name' => 'Kemajuan Studi', 'url' => route('kemajuan_studi')],
                    ['name' => "Detail Nilai $kode_mk", 'url' => null],
                ];
                break;

            case 'catatan_perilaku_mahasiswa':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                    ['name' => 'Catatan Perilaku', 'url' => null],
                ];
                break;

            case 'mahasiswa_konseling':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                    ['name' => 'Konseling', 'url' => null],
                ];
                break;

            case 'mhs_konseling_request':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                    ['name' => 'Konseling', 'url' => null],
                ];
                break;

            case 'mahasiswa_perwalian':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                    ['name' => 'Perwalian', 'url' => null],
                ];
                break;

            default:
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                    ['name' => 'Halaman Tidak Dikenal', 'url' => null],
                ];
                break;
        }

        return $breadcrumbs;
    }

    public function generateNotifications()
    {
        $user = session('user');
        $notifications = collect([]);

        if (!$user || !isset($user['role'])) {
            Log::info('No user or role found in generateNotifications', ['user' => $user]);
            return $notifications;
        }

        switch ($user['role']) {
            case 'mahasiswa':
                $notifications = $this->generateStudentNotifications($user);
                break;
            case 'dosen':
                $notifications = $this->generateDosenNotifications($user);
                break;
            default:
                Log::info('Unhandled role in notification generation', ['role' => $user['role']]);
                break;
        }

        return $notifications;
    }

    private function generateStudentNotifications($user)
    {
        $student = Mahasiswa::where('nim', $user['nim'])->first();

        if (!$student) {
            Log::info('No student found', ['nim' => $user['nim']]);
            return collect([]);
        }

        // Fetch notifications from Laravel's notifications table
        $notifications = $student->unreadNotifications()
            ->where('notifiable_type', 'App\Models\Mahasiswa')
            ->get();
        Log::info('Notifications fetched for student', ['count' => $notifications->count()]);

        return $notifications;
    }

    private function generateDosenNotifications($user)
    {
        $dosen = Dosen::where('username', $user['username'])->first();

        if (!$dosen) {
            Log::info('No dosen found', ['username' => $user['username']]);
            return collect([]);
        }

        // Fetch notifications from Laravel's notifications table
        $notifications = $dosen->unreadNotifications()
            ->where('notifiable_type', 'App\Models\Dosen')
            ->get();
        Log::info('Notifications fetched for dosen', ['count' => $notifications->count()]);

        return $notifications;
    }
}