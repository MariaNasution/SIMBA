<?php

namespace App\Services;

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
            default:
                return [
                    ['name' => '<i class="fas fa-home"></i> Beranda', 'url' => url('/')],
                    ['name' => '<i class="fas fa-question-circle"></i> Unknown Page', 'url' => null],
                ];
        }
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

        // Assuming Notifikasi has a foreign key like 'perwalian_id' linking to Mahasiswa
        $notifications = Notifikasi::where('ID_Notifikasi', $student->ID_Perwalian)->get();
        Log::info('Notifications fetched', ['count' => $notifications->count()]);

        return $notifications;
    }
}