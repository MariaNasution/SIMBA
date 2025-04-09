<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
            case 'kemahasiswaan':
                return $this->generateKemahasiswaanBreadcrumbs($currentRoute, $params);
            default:
                return [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => url('/')],
                    ['name' => '<i class="fas fa-question-circle"></i> Unknown Page', 'url' => null],
                ];
        }
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
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('dosen')],
                    ['name' => '<i class="fas fa-users"></i> Perwalian Kelas', 'url' => null],
                ];
                break;

            case 'dosen.presensi':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-calendar-check"></i> Presensi', 'url' => null],
                ];
                break;

            case 'absensi':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-check"></i> Absensi Mahasiswa', 'url' => null],
                ];
                break;

            case 'absensi.show':
                $date = $params['date'] ?? 'N/A';
                $class = $params['class'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-user-check"></i> Absensi Mahasiswa', 'url' => route('absensi')],
                    ['name' => "<i class='fas fa-calendar'></i> Absensi $class - $date", 'url' => null],
                ];
                break;

            case 'set.perwalian':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-cog"></i> Set Perwalian', 'url' => null],
                ];
                break;

            case 'dosen.histori':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-history"></i> Histori', 'url' => null],
                ];
                break;

            case 'berita_acara.index':
            case 'perwalian.berita_acara':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('dosen')],
                    ['name' => '<i class="fas fa-book"></i> Berita Acara', 'url' => null],
                ];
                break;

            case 'berita_acara.select_class':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-book"></i> Berita Acara', 'url' => route('berita_acara.index')],
                    ['name' => '<i class="fas fa-chalkboard-teacher"></i> Pilih Kelas', 'url' => null],
                ];
                break;

            case 'berita_acara.create':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-book"></i> Berita Acara', 'url' => route('berita_acara.index')],
                    ['name' => '<i class="fas fa-edit"></i> Buat Berita Acara', 'url' => null],
                ];
                break;

            case 'berita-acara.success':
                $kelas = $params['kelas'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-book"></i> Berita Acara', 'url' => route('berita_acara.index')],
                    ['name' => "<i class='fas fa-check-circle'></i> Sukses - $kelas", 'url' => null],
                ];
                break;

            case 'dosen.detailedClass':
                $year = $params['year'] ?? 'N/A';
                $kelas = $params['class'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('dosen')],
                    ['name' => "<i class='fas fa-chalkboard'></i> Detail Kelas $kelas ($year)", 'url' => null],
                ];
                break;

            default:
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('dosen')],
                    ['name' => '<i class="fas fa-question-circle"></i> Unknown Page', 'url' => null],
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

            case 'pengumuman.detail':
                $id = $params['id'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                    ['name' => "<i class='fas fa-bullhorn'></i> Pengumuman #$id", 'url' => null],
                ];
                break;

            case 'profil':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                    ['name' => '<i class="fas fa-user"></i> Profil', 'url' => null],
                ];
                break;

            case 'kemajuan_studi':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                    ['name' => '<i class="fas fa-graduation-cap"></i> Kemajuan Studi', 'url' => null],
                ];
                break;

            case 'detailnilai':
                $kode_mk = $params['kode_mk'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                    ['name' => '<i class="fas fa-book-open"></i> Kemajuan Studi', 'url' => route('kemajuan_studi')],
                    ['name' => "<i class='fas fa-list-ol'></i> Detail Nilai $kode_mk", 'url' => null],
                ];
                break;

            case 'catatan_perilaku':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-sticky-note"></i> Catatan Perilaku', 'url' => null],
                ];
                break;

            case 'mahasiswa_konseling':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                ];
                break;

            case 'mahasiswa_perwalian':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-users"></i> Perwalian', 'url' => null],
                ];
                break;

            case 'mhs_konseling_request':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => route('mahasiswa_konseling')],
                    ['name' => '<i class="fas fa-plus-circle"></i> Request Konseling', 'url' => null],
                ];
                break;

            case 'mhs_konseling_request.store':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => route('mahasiswa_konseling')],
                    ['name' => '<i class="fas fa-plus-circle"></i> Request Konseling', 'url' => null],
                ];
                break;

            case 'mhs_konseling_store':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => route('mahasiswa_konseling')],
                    ['name' => '<i class="fas fa-check-circle"></i> Konseling Disimpan', 'url' => null],
                ];
                break;

            default:
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-question-circle"></i> Unknown Page', 'url' => null],
                ];
                break;
        }

        return $breadcrumbs;
    }

    protected function generateKemahasiswaanBreadcrumbs($currentRoute, $params = [])
    {
        $breadcrumbs = [];

        switch ($currentRoute) {
            // Beranda and Pengumuman Routes
            case 'kemahasiswaan_beranda':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_pengumuman.store':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => '<i class="fas fa-bullhorn"></i> Tambah Pengumuman', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_pengumuman.destroy':
                $id = $params['id'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => "<i class='fas fa-trash'></i> Hapus Pengumuman #$id", 'url' => null],
                ];
                break;

            case 'kemahasiswaan_pengumunankonselor.detail':
                $id = $params['id'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => "<i class='fas fa-bullhorn'></i> Detail Pengumuman #$id", 'url' => null],
                ];
                break;

            case 'kemahasiswaan_calendar.upload':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => '<i class="fas fa-calendar"></i> Upload Kalender', 'url' => null],
                ];
                break;

            // Perwalian Routes
            case 'kemahasiswaan_perwalian.jadwal':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-users"></i> Perwalian', 'url' => null],
                    ['name' => '<i class="fas fa-calendar"></i> Jadwal', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_perwalian.kelas':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-users"></i> Perwalian', 'url' => null],
                    ['name' => '<i class="fas fa-chalkboard"></i> Kelas', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_perwalian.berita_acara':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-users"></i> Perwalian', 'url' => null],
                    ['name' => '<i class="fas fa-book"></i> Berita Acara', 'url' => null],
                ];
                break;

            // Konseling Routes
            case 'kemahasiswaan_daftar_pelanggaran':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-exclamation-circle"></i> Daftar Pelanggaran', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_hasil_konseling':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-file-alt"></i> Hasil Konseling', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_riwayat_konseling':
            case 'kemahasiswaan_riwayat.konseling':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-history"></i> Riwayat Konseling', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_riwayat.konseling.cari':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-history"></i> Riwayat Konseling', 'url' => route('kemahasiswaan_riwayat.konseling')],
                    ['name' => '<i class="fas fa-search"></i> Cari Riwayat', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_riwayat.konseling.detail':
                $nim = $params['nim'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-history"></i> Riwayat Konseling', 'url' => route('kemahasiswaan_riwayat.konseling')],
                    ['name' => "<i class='fas fa-user'></i> Detail NIM: $nim", 'url' => null],
                ];
                break;

            case 'kemahasiswaan_konseling_lanjutan':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-forward"></i> Konseling Lanjutan', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_konseling.lanjutan.detail':
                $nim = $params['nim'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-forward"></i> Konseling Lanjutan', 'url' => route('kemahasiswaan_konseling_lanjutan')],
                    ['name' => "<i class='fas fa-user'></i> Detail NIM: $nim", 'url' => null],
                ];
                break;

            case 'kemahasiswaan_konseling.lanjutan.store':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-forward"></i> Konseling Lanjutan', 'url' => route('kemahasiswaan_konseling_lanjutan')],
                    ['name' => '<i class="fas fa-save"></i> Simpan Konseling Lanjutan', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_ajukan_konseling':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-plus-circle"></i> Ajukan Konseling', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_konseling.ajukan':
            case 'kemahasiswaan_konseling.index':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-plus-circle"></i> Ajukan Konseling', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_konseling.cari':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-plus-circle"></i> Ajukan Konseling', 'url' => route('kemahasiswaan_konseling.ajukan')],
                    ['name' => '<i class="fas fa-search"></i> Cari Mahasiswa', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_konseling.pilih':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-plus-circle"></i> Ajukan Konseling', 'url' => route('kemahasiswaan_konseling.ajukan')],
                    ['name' => '<i class="fas fa-user-check"></i> Pilih Mahasiswa', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_konseling.caririwayat':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-plus-circle"></i> Ajukan Konseling', 'url' => route('kemahasiswaan_konseling.ajukan')],
                    ['name' => '<i class="fas fa-search"></i> Cari Riwayat', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_daftar_request':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-list"></i> Daftar Request', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_approve_konseling':
                $id = $params['id'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-list"></i> Daftar Request', 'url' => route('kemahasiswaan_daftar_request')],
                    ['name' => "<i class='fas fa-check'></i> Approve Request #$id", 'url' => null],
                ];
                break;

            case 'kemahasiswaan_reject_konseling':
                $id = $params['id'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-list"></i> Daftar Request', 'url' => route('kemahasiswaan_daftar_request')],
                    ['name' => "<i class='fas fa-times'></i> Reject Request #$id", 'url' => null],
                ];
                break;

            case 'kemahasiswaan_riwayat_daftar_request':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-history"></i> Riwayat Daftar Request', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_hasil.index':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-file-alt"></i> Hasil Konseling', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_hasil.show':
                $id = $params['id'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-file-alt"></i> Hasil Konseling', 'url' => route('kemahasiswaan_hasil.index')],
                    ['name' => "<i class='fas fa-eye'></i> Detail #$id", 'url' => null],
                ];
                break;

            case 'kemahasiswaan_hasil_konseling.store':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-file-alt"></i> Hasil Konseling', 'url' => route('kemahasiswaan_hasil.index')],
                    ['name' => '<i class="fas fa-save"></i> Simpan Hasil Konseling', 'url' => null],
                ];
                break;

            case 'kemahasiswaan_hasil.destroy':
                $id = $params['id'] ?? 'N/A';
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-file-alt"></i> Hasil Konseling', 'url' => route('kemahasiswaan_hasil.index')],
                    ['name' => "<i class='fas fa-trash'></i> Hapus #$id", 'url' => null],
                ];
                break;

            case 'kemahasiswaan_hasil_konseling.upload':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-comments"></i> Konseling', 'url' => null],
                    ['name' => '<i class="fas fa-file-alt"></i> Hasil Konseling', 'url' => route('kemahasiswaan_hasil.index')],
                    ['name' => '<i class="fas fa-upload"></i> Upload Hasil Konseling', 'url' => null],
                ];
                break;

            default:
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => '<i class="fas fa-question-circle"></i> Unknown Page', 'url' => null],
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
            return $notifications;
        }

        switch ($user['role']) {
            case 'mahasiswa':
                $notifications = $this->generateStudentNotifications($user);
                break;
            case 'kemahasiswaan':
                $notifications = $this->generateKemahasiswaanNotifications($user);
                break;
            default:
                Log::info('Unhandled role', ['role' => $user['role']]);
                break;
        }

        return $notifications;
    }

    private function generateStudentNotifications($user)
    {
        $student = Mahasiswa::where('nim', $user['nim'])->first();

        if (!$student || !isset($student->ID_Perwalian)) {
            Log::info('No student or ID_Perwalian found', ['nim' => $user['nim']]);
            return collect([]);
        }

        $notifications = Notifikasi::where('ID_Notifikasi', $student->ID_Perwalian)->get()->where('role', 'mahasiswa');
        Log::info('Notifications fetched', ['count' => $notifications->count()]);

        return $notifications;
    }

    private function generateDosenNotifications($user)
    {
        $dosen = Dosen::where('nip', $user['nip'])->first();

        if (!$dosen || !isset($student->ID_Perwalian)) {
            Log::info('No student or ID_Perwalian found', ['nim' => $user['nim']]);
            return collect([]);
        }

        $notifications = Notifikasi::where('ID_Notifikasi', $student->ID_Perwalian)->get()->where('role', 'mahasiswa');
        Log::info('Notifications fetched', ['count' => $notifications->count()]);

        return $notifications;
    }

    private function generateKemahasiswaanNotifications($user)
    {
        // $notifications = Notifikasi::where('role', 'kemahasiswaan')
        //                            ->where('is_read', false)
        //                            ->get();

        // Log::info('Notifications fetched for kemahasiswaan', ['count' => $notifications->count()]);

        // return $notifications;
    }
}