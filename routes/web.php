<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\MahasiswaHomeController;
use App\Http\Controllers\MahasiswaKonselingController;
use App\Http\Controllers\MahasiswaPerwalianController;
use App\Http\Controllers\MahasiswaRequestKonselingController;
use App\Http\Controllers\KemahasiswaanController;
use App\Http\Controllers\KemahasiswaanPerwalianController;
use App\Http\Controllers\KemajuanStudiController;
use App\Http\Controllers\DetailNilaiController;
use App\Http\Controllers\CatatanPerilakuController;
use App\Http\Controllers\KonselorController;
use App\Http\Controllers\KeasramaanController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SetPerwalianController;
use App\Http\Controllers\DaftarPelanggaranController;
use App\Http\Controllers\AjukanKonselingController;
use App\Http\Controllers\RiwayatKonselingController;
use App\Http\Controllers\HasilKonselingController;
use App\Http\Controllers\DaftarRequestKonselingController;
use App\Http\Controllers\CatatanPerilakuDetailController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\NotificationController;

Route::post('/notifications/mark-read', [NotificationController::class, 'markAllRead'])->name('notifications.markRead');
use App\Http\Controllers\KonselingLanjutanController;
use App\Http\Controllers\BeritaAcaraController;

// Login dan Logout
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Register
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register.form');
Route::post('/register', [RegisterController::class, 'submitRegistration'])->name('register.submit');
Route::get('/activate', [RegisterController::class, 'showActivationForm'])->name('activation.prompt');
Route::post('/activate', [RegisterController::class, 'activateAccount'])->name('activation.submit');

// Password Reset
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])->name('password.forgot');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.send-link');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetPasswordForm'])->name('password.reset.form');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');
Route::get('/waiting-email', function () {
    return view('auth.waiting-email');
})->name('password.waiting-email');

// Middleware untuk mahasiswa
Route::middleware(['auth.session', 'ensure.student.data', 'role:mahasiswa'])->group(function () {
    Route::get('/beranda', [MahasiswaHomeController::class, 'index'])->name('beranda');
    Route::get('/pengumuman/{id}', [MahasiswaHomeController::class, 'show'])->name('pengumuman.detail');
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil');
    Route::get('perkuliahan/kemajuan_studi', [KemajuanStudiController::class, 'index'])->name('kemajuan_studi');
    Route::get('/detailnilai/{kode_mk}', [DetailNilaiController::class, 'show'])->name('detailnilai');
    Route::get('/catatan_perilaku', [CatatanPerilakuController::class, 'index'])->name('catatan_perilaku');
    Route::get('/mahasiswa_konseling', [MahasiswaKonselingController::class, 'index'])->name('mahasiswa_konseling');
    Route::get('/mahasiswa_perwalian', [MahasiswaPerwalianController::class, 'index'])->name('mahasiswa_perwalian');
    Route::get('/mahasiswa/konseling/request', [MahasiswaRequestKonselingController::class, 'create'])->name('mhs_konseling_request');
    Route::redirect('/mahasiswa/konseling/request', '/mahasiswa/request-konseling');
    Route::post('/mahasiswa/request-konseling', [MahasiswaRequestKonselingController::class, 'store'])->name('mhs_konseling_request.store');
    Route::get('/mahasiswa/konseling/request', [MahasiswaRequestKonselingController::class, 'create'])->name('mhs_konseling_request');
    Route::post('/mahasiswa/konseling/store', [MahasiswaRequestKonselingController::class, 'store'])->name('mhs_konseling_store');

});

Route::middleware(['auth.session', 'role:kemahasiswaan'])
    ->prefix('kemahasiswaan')
    ->name('kemahasiswaan_')
    ->group(function () {
        Route::get('/pelanggaran', [DaftarPelanggaranController::class, 'daftarPelanggaran'])->name('pelanggaran.daftar');
        Route::get('/beranda', [KemahasiswaanController::class, 'index'])->name('beranda');
        Route::post('/beranda/store', [KemahasiswaanController::class, 'store'])->name('pengumuman.store');
        Route::delete('/beranda/{id}', [KemahasiswaanController::class, 'destroy'])->name('pengumuman.destroy');
        Route::get('/pengumuman/{id}', [KemahasiswaanController::class, 'show'])->name('pengumumankemahasiswaan.detail');
        Route::post('/calendar/upload', [CalendarController::class, 'upload'])->name('calendar.upload');

        // Perwalian
        Route::get('/perwalian/jadwal', [KemahasiswaanPerwalianController::class, 'jadwalPerwalian'])->name('perwalian.jadwal');
        Route::post('/perwalian/store', [KemahasiswaanPerwalianController::class, 'store'])->name('perwalian.store');
        Route::get('/perwalian/kelas', [KemahasiswaanPerwalianController::class, 'kelasPerwalian'])->name('perwalian.kelas');
        Route::get('/perwalian/berita-acara', [KemahasiswaanPerwalianController::class, 'beritaAcaraPerwalian'])->name('perwalian.berita_acara');
        Route::post('/perwalian/berita-acara/search', [KemahasiswaanPerwalianController::class, 'searchBeritaAcara'])->name('perwalian.berita_acara.search');

        // Konseling
        Route::prefix('konseling')->group(function () {
            Route::get('/daftar_pelanggaran', [DaftarPelanggaranController::class, 'daftarPelanggaran'])->name('daftar_pelanggaran');
            Route::get('/hasil_konseling', [KemahasiswaanController::class, 'hasilKonseling'])->name('hasil_konseling');
            Route::get('/riwayat_konseling', [RiwayatKonselingController::class, 'index'])->name('riwayat_konseling');
            Route::get('/konseling_lanjutan', [KemahasiswaanController::class, 'konselingLanjutan'])->name('konseling_lanjutan');
            Route::get('/ajukan_konseling', [KemahasiswaanController::class, 'ajukanKonseling'])->name('ajukan_konseling');

            // Daftar request konseling kemahasiswaan
            Route::get('/daftar-request', [DaftarRequestKonselingController::class, 'daftarRequest'])->name('daftar_request');
            Route::put('/approve-konseling/{id}', [DaftarRequestKonselingController::class, 'approve'])->name('approve_konseling');
            Route::put('/reject-konseling/{id}', [DaftarRequestKonselingController::class, 'reject'])->name('reject_konseling');

            // Riwayat daftar request konseling
            Route::get('/riwayat-daftar-request', [DaftarRequestKonselingController::class, 'riwayatDaftarRequestKonseling'])->name('riwayat_daftar_request');

            Route::get('/hasil', [HasilKonselingController::class, 'index'])->name('hasil.index');
            Route::post('/hasil-konseling', [HasilKonselingController::class, 'store'])->name('hasil_konseling.store');
            Route::get('/hasil/{id}', [HasilKonselingController::class, 'show'])->name('hasil.show');
            Route::delete('/hasil/{id}', [HasilKonselingController::class, 'destroy'])->name('hasil.destroy');
            Route::post('/hasil-konseling/upload', [HasilKonselingController::class, 'upload'])->name('hasil_konseling.upload');

            // Kemahasiswaan request konseling
            Route::get('/ajukan', [AjukanKonselingController::class, 'index'])->name('konseling.ajukan');
            Route::get('/cari', [AjukanKonselingController::class, 'cariMahasiswa'])->name('konseling.cari');
            Route::post('/ajukan', [AjukanKonselingController::class, 'ajukanKonseling'])->name('konseling.ajukan');
            Route::get('/caririwayat', [RiwayatkonselingController::class, 'CariRiwayatMahasiswa'])->name('konseling.caririwayat');
            Route::get('/konseling', [AjukanKonselingController::class, 'index'])->name('konseling.index');
            Route::get('/konseling/pilih', [AjukanKonselingController::class, 'pilihMahasiswa'])->name('konseling.pilih');

            // Riwayat konseling mahasiswa
            Route::get('/riwayat-konseling', [RiwayatKonselingController::class, 'index'])->name('riwayat.konseling');
            Route::get('/riwayat-konseling/cari', [RiwayatKonselingController::class, 'CariRiwayatMahasiswa'])->name('riwayat.konseling.cari');
            Route::get('/riwayat-konseling/{nim}', [RiwayatKonselingController::class, 'detail'])->name('riwayat.konseling.detail');

            Route::get('/konseling-lanjutan/{nim}', [KemahasiswaanController::class, 'detail'])->name('konseling.lanjutan.detail');
            Route::post('/konseling/lanjutan', [KonselingLanjutanController::class, 'store'])->name('konseling.lanjutan.store');
        });
    });
// Middleware untuk konselor
Route::middleware(['auth.session', 'role:konselor'])
    ->prefix('konselor')
    ->name('konselor_')
    ->group(function () {
        Route::get('/pelanggaran', [DaftarPelanggaranController::class, 'daftarPelanggaran'])->name('pelanggaran.daftar');
        Route::get('/beranda', [KonselorController::class, 'index'])->name('beranda');
        Route::post('/beranda/store', [KonselorController::class, 'store'])->name('pengumuman.store');
        Route::delete('/beranda/{id}', [KonselorController::class, 'destroy'])->name('pengumuman.destroy');
        Route::get('/pengumuman/{id}', [KonselorController::class, 'show'])->name('pengumumankonselor.detail');
        Route::post('/calendar/upload', [CalendarController::class, 'upload'])->name('calendar.upload');
        // Group untuk konseling
        Route::prefix('konseling')->group(function () {
            Route::get('/daftar_pelanggaran', [DaftarPelanggaranController::class, 'daftarPelanggaran'])->name('daftar_pelanggaran');
            Route::get('/hasil_konseling', [KonselorController::class, 'hasilKonseling'])->name('hasil_konseling');
            Route::get('/riwayat_konseling', [RiwayatKonselingController::class, 'index'])->name('riwayat_konseling');
            Route::get('/konseling_lanjutan', [KonselorController::class, 'konselingLanjutan'])->name('konseling_lanjutan');
            Route::get('/ajukan_konseling', [KonselorController::class, 'ajukanKonseling'])->name('ajukan_konseling');

            // Daftar request konseling kemahasiswaan
            Route::get('/daftar-request', [DaftarRequestKonselingController::class, 'daftarRequest'])->name('daftar_request');
            Route::put('/approve-konseling/{id}', [DaftarRequestKonselingController::class, 'approve'])->name('approve_konseling');
            Route::put('/reject-konseling/{id}', [DaftarRequestKonselingController::class, 'reject'])->name('reject_konseling');

            // Riwayat daftar request konseling
            Route::get('/riwayat-daftar-request', [DaftarRequestKonselingController::class, 'riwayatDaftarRequestKonseling'])->name('riwayat_daftar_request');

            Route::get('/hasil', [HasilKonselingController::class, 'index'])->name('hasil.index');
            Route::post('/hasil-konseling', [HasilKonselingController::class, 'store'])->name('hasil_konseling.store');
            Route::get('/hasil/{id}', [HasilKonselingController::class, 'show'])->name('hasil.show');
            Route::delete('/hasil/{id}', [HasilKonselingController::class, 'destroy'])->name('hasil.destroy');
            Route::post('/hasil-konseling/upload', [HasilKonselingController::class, 'upload'])->name('hasil_konseling.upload');

            // Konselor request konseling
            Route::get('/ajukan', [AjukanKonselingController::class, 'index'])->name('konseling.form');
            Route::get('/cari', [AjukanKonselingController::class, 'cariMahasiswa'])->name('konseling.cari');
            Route::post('/ajukan', [AjukanKonselingController::class, 'ajukanKonseling'])->name('konseling.ajukan');

            Route::get('/caririwayat', [RiwayatkonselingController::class, 'CariRiwayatMahasiswa'])->name('konseling.caririwayat');
            Route::get('/konseling', [AjukanKonselingController::class, 'index'])->name('konseling.index');
            Route::get('/konseling/pilih', [AjukanKonselingController::class, 'pilihMahasiswa'])->name('konseling.pilih');

            // Riwayat konseling mahasiswa
            Route::get('/riwayat-konseling', [RiwayatKonselingController::class, 'index'])->name('riwayat.konseling');
            Route::get('/riwayat-konseling/cari', [RiwayatKonselingController::class, 'CariRiwayatMahasiswa'])->name('riwayat.konseling.cari');
            Route::get('/riwayat-konseling/{nim}', [RiwayatKonselingController::class, 'detail'])->name('riwayat.konseling.detail');

            Route::get('/konseling-lanjutan/{nim}', [KonselorController::class, 'detail'])->name('konseling.lanjutan.detail');
            Route::post('/konseling/lanjutan', [KonselingLanjutanController::class, 'store'])->name('konseling.lanjutan.store');
        });
    });

// Middleware untuk mahasiswa
Route::middleware(['auth.session', 'ensure.student.data', 'role:mahasiswa'])->group(function () {
    Route::get('/mahasiswa/beranda', [MahasiswaHomeController::class, 'index'])->name('beranda');
    Route::get('/mahasiswa/pengumuman/{id}', [MahasiswaHomeController::class, 'show'])->name('pengumuman.detail');
    Route::get('/mahasiswa/profil', [ProfilController::class, 'index'])->name('profil');
    Route::get('perkuliahan/kemajuan_studi', [KemajuanStudiController::class, 'index'])->name('kemajuan_studi');
    Route::get('/detailnilai/{kode_mk}', [DetailNilaiController::class, 'show'])->name('detailnilai');
    Route::get('/catatan_perilaku', [CatatanPerilakuController::class, 'index'])->name('catatan_perilaku_mahasiswa');

    Route::get('/mahasiswa_konseling', [MahasiswaKonselingController::class, 'index'])->name('mahasiswa_konseling');
    Route::get('/mahasiswa_perwalian', [MahasiswaPerwalianController::class, 'index'])->name('mahasiswa_perwalian');

    Route::prefix('konseling')->group(function () {
        Route::get('/mahasiswa/konseling/request', [MahasiswaRequestKonselingController::class, 'create'])->name('mhs_konseling_request');


        Route::get('/mahasiswa/request-konseling', [MahasiswaRequestKonselingController::class, 'create'])->name('mhs_konseling_request');
        Route::post('/mahasiswa/request-konseling', [MahasiswaRequestKonselingController::class, 'store'])->name('mhs_konseling_request.store');
        Route::get('/mahasiswa/request-konseling', [MahasiswaRequestKonselingController::class, 'create'])->name('mhs_konseling_request');


        Route::get('/mahasiswa/konseling/request', [MahasiswaRequestKonselingController::class, 'create'])->name('mhs_konseling_request');
        Route::post('/mahasiswa/konseling/store', [MahasiswaRequestKonselingController::class, 'store'])->name('mhs_konseling_store');
    });

});

Route::middleware(['auth.session', 'role:dosen'])->group(function () {
    // DosenController Routes (unchanged)
    Route::get('/dosen/beranda', [DosenController::class, 'beranda'])->name('dosen');
    Route::get('/dosen/perwalian', [DosenController::class, 'index'])->name('dosen.perwalian');
    Route::get('/dosen/presensi', [DosenController::class, 'presensi'])->name('dosen.presensi');
    Route::get('/dosen/detailed-class/{year}/{kelas}', [DosenController::class, 'showDetailedClass'])->name('dosen.detailedClass');

    // SetPerwalianController Routes (unchanged)
    Route::get('/set-perwalian', [SetPerwalianController::class, 'index'])->name('set.perwalian');
    Route::post('/set-perwalian/store', [SetPerwalianController::class, 'store'])->name('set.perwalian.store');
    Route::delete('/set-perwalian/destroy', [SetPerwalianController::class, 'destroy'])->name('set.perwalian.destroy');
    Route::get('/set-perwalian/calendar', [SetPerwalianController::class, 'getCalendar'])->name('set.perwalian.calendar');
    Route::get('/set-perwalian/histori', [SetPerwalianController::class, 'histori'])->name('dosen.histori');
    Route::get('/set-perwalian/histori/detailed/{id}', [SetPerwalianController::class, 'detailedHistori'])->name('dosen.histori.detailed');
    Route::get('/set-perwalian/print-berita-acara/{id}', [SetPerwalianController::class, 'printBeritaAcara'])->name('berita_acara.print');

    // AbsensiController Routes
    Route::get('/absensi-mahasiswa', [AbsensiController::class, 'index'])->name('absensi');
    Route::get('/absensi-mahasiswa/{date}/{class}', [AbsensiController::class, 'show'])->name('absensi.show');
    Route::post('/absensi-mahasiswa/{date}/{class}', [AbsensiController::class, 'store'])->name('absensi.store');
    Route::get('/absensi-mahasiswa/completed', [AbsensiController::class, 'completed'])->name('absensi.completed');

    // BeritaAcaraController Routes - Updated
    Route::get('/berita-acara', [BeritaAcaraController::class, 'index'])->name('berita_acara.index');
    Route::get('/berita-acara/select-class', [BeritaAcaraController::class, 'selectClass'])->name('berita_acara.select_class');
    Route::get('/berita-acara/create/{date}/{class}', [BeritaAcaraController::class, 'create'])->name('berita_acara.create');
    Route::post('/berita-acara/store/{date}/{class}', [BeritaAcaraController::class, 'store'])->name('berita_acara.store');
    Route::get('/berita-acara/success/{kelas}/{tanggal_perwalian}', [BeritaAcaraController::class, 'success'])->name('berita_acara.success');
});

// Middleware untuk keasramaan
Route::middleware(['auth.session', 'ensure.student.data.all.student', 'role:keasramaan'])->group(function () {
    Route::get('/keasramaan/beranda', [KeasramaanController::class, 'index'])->name('keasramaan');
    Route::get('/keasramaan/catatan-perilaku', [KeasramaanController::class, 'pelanggaran'])->name('pelanggaran_keasramaan');
    Route::get('/keasramaan/catatan-perilaku/detail/{studentNim}', [KeasramaanController::class, 'detail'])->name('catatan_perilaku_detail');

    Route::prefix('student-behaviors')->group(function () {
        Route::get('/create/{studentNim}/{ta}/{semester}', [CatatanPerilakuDetailController::class, 'create'])
            ->name('student_behaviors.create');

        Route::post('/store', [CatatanPerilakuDetailController::class, 'store'])
            ->name('student_behaviors.store');

        Route::get('/{id}/edit', [CatatanPerilakuDetailController::class, 'edit'])
            ->name('student_behaviors.edit');

        Route::post('/{id}/update', [CatatanPerilakuDetailController::class, 'update'])->name('student_behaviors.update');

        Route::delete('/{id}/destroy', [CatatanPerilakuDetailController::class, 'destroy'])
            ->name('student_behaviors.destroy');
    });

});

// Middleware untuk orang tua
Route::middleware(['auth.session', 'ensure.student.data.ortu', 'role:orang_tua'])->group(function () {
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil');
    Route::get('/orang_tua/beranda', [OrangTuaController::class, 'index'])->name('orang_tua');
    Route::get('/orang_tua/catatan_perilaku', [OrangTuaController::class, 'catatan_perilaku'])->name('catatan_perilaku_orang_tua');
});

// Middleware untuk Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/beranda', [AdminController::class, 'index'])->name('admin.beranda');

    // CRUD Users
    Route::get('/users', [AdminController::class, 'indexUser'])->name('admin.users.index');
    Route::get('/users/create', [AdminController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [AdminController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [AdminController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('admin.users.destroy');
});

Route::post('/send-sms', [SmsController::class, 'send']);
Route::get('/send-sms', [SmsController::class, 'create']);