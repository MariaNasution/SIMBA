<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Notifikasi;
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
            case 'orang_tua':
                return $this->generateOrangTuaBreadcrumbs($currentRoute, $params);
            case 'keasramaan':
                    return $this->generateKeasramaanBreadcrumbs($currentRoute, $params);
            default:
                return [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => url('/')],
                    ['name' => '<i class="fas fa-question-circle"></i> Unknown Page', 'url' => null],
                ];
        }
    }

    protected function generateKeasramaanBreadcrumbs($currentRoute, $params = [])
    {
        $breadcrumbs = [];
    
        Log::info('Current Route', ['route' => $breadcrumbs]);
        switch ($currentRoute) {
            case 'keasramaan':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => null],
                ];
                break;
    
            case 'pelanggaran_keasramaan':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('keasramaan')],
                    ['name' => '<i class="fas fa-user-edit"></i> Catatan Perilaku', 'url' => null],
                ];
                break;
                
                case 'catatan_perilaku_detail':
                    $breadcrumbs = [
                        ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('keasramaan')],
                        ['name' => '<i class="fas fa-user-edit"></i> Catatan Perilaku', 'url' => route('pelanggaran_keasramaan')],
                        ['name' => '<i class="fas fa-eye"></i> Detail Catatan Perilaku', 'url' => null],
                    ];
                    break;
    
            default:
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('keasramaan')],
                    ['name' => '<i class="fas fa-question-circle"></i> Halaman Tidak Dikenal', 'url' => null],
                ];
        }
    
        return $breadcrumbs;
    }
    protected function generateOrangTuaBreadcrumbs($currentRoute, $params = [])
    {
        $breadcrumbs = [];
    
        Log::info('Current Route', ['route' => $breadcrumbs]);
        switch ($currentRoute) {
            case 'orang_tua':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => null],
                ];
                break;
    
            case 'catatan_perilaku_orang_tua':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('orang_tua')],
                    ['name' => '<i class="fas fa-user-edit"></i> Catatan Perilaku', 'url' => null],
                ];
                break;
    
            default:
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('orang_tua')],
                    ['name' => '<i class="fas fa-question-circle"></i> Halaman Tidak Dikenal', 'url' => null],
                ];
        }
    
        return $breadcrumbs;
    }
    

    protected function generateDosenBreadcrumbs($currentRoute, $params = [])
    {
        // ... (your existing dosen breadcrumbs remain unchanged)
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

    //konseling
    protected function generateKonselorBreadcrumbs($currentRoute, $params = [])
    {
        $breadcrumbs = [];

        switch ($currentRoute) {
            case 'konselor_beranda':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => null],
                ];
                break;

            case 'konselor_pengumumankonselor.detail':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-bullhorn"></i> Beranda', 'url' => route('konselor_beranda')],
                    ['name' => 'Detail Pengumuman', 'url' => null],
                ];
                break;

            // Grup Konseling
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
                    ['name' => '<i class="fas fa-eye"></i> Detail Hasil', 'url' => null],
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
                    ['name' => '<i class="fas fa-question-circle"></i> Halaman Tidak Dikenal', 'url' => null],
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

            case 'kemahasiswaan_pengumumankemahasiswaan.detail':
                $breadcrumbs = [
                    ['name' => '<i class="fas fa-bullhorn"></i> Beranda', 'url' => route('kemahasiswaan_beranda')],
                    ['name' => 'Detail Pengumuman', 'url' => null],
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

        case 'catatan_perilaku_mahasiswa':
            $breadcrumbs = [
                ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                ['name' => '<i class="fas fa-user-edit"></i> Catatan Perilaku', 'url' => null],
            ];
            break;

        case 'mahasiswa_konseling':
            $breadcrumbs = [
                ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                ['name' => '<i class="fas fa-user-friends"></i> Konseling', 'url' => null],
            ];
            break;

        case 'mahasiswa_perwalian':
            $breadcrumbs = [
                ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                ['name' => '<i class="fas fa-list-alt"></i> Perwalian', 'url' => null],
            ];
            break;

   

        default:
            $breadcrumbs = [
                ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => route('beranda')],
                ['name' => '<i class="fas fa-question-circle"></i> Halaman Tidak Dikenal', 'url' => null],
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
            Log::info('No student found in generateStudentNotifications', ['nim' => $user['nim']]);
            return collect([]);
        }

        // Retrieve notifications using Laravel’s built-in notifications relationship
        $notifications = $student->notifications()->orderBy('created_at', 'desc')->get();

        Log::info('Notifications fetched for student', [
            'nim'   => $student->nim,
            'count' => $notifications->count(),
        ]);

        return $notifications;
    }
    
}