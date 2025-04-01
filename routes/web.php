<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\MahasiswaHomeController;
use App\Http\Controllers\MahasiswaKonselingController;
use App\Http\Controllers\MahasiswaPerwalianController;
use App\Http\Controllers\MahasiswaRequestKonselingController;
use App\Http\Controllers\KemahasiswaanController;
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
use App\Http\Controllers\NotifikasiController;

Route::post('/notifications/mark-read', [NotifikasiController::class, 'markAllRead'])->name('notifications.markRead');
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
    Route::get('/mahasiswa/request-konseling', [MahasiswaRequestKonselingController::class, 'create'])->name('mhs_konseling_request');
    Route::post('/mahasiswa/request-konseling', [MahasiswaRequestKonselingController::class, 'store'])->name('mhs_konseling_request.store');
    Route::get('/mahasiswa/konseling/request', [MahasiswaRequestKonselingController::class, 'create'])->name('mhs_konseling_request');
    Route::post('/mahasiswa/konseling/store', [MahasiswaRequestKonselingController::class, 'store'])->name('mhs_konseling_store');

});

// Middleware untuk kemahasiswaan
Route::middleware(['auth.session', 'role:kemahasiswaan'])
    ->prefix('kemahasiswaan')
    ->name('kemahasiswaan_')
    ->group(function () {
        Route::get('/beranda', [KemahasiswaanController::class, 'index'])->name('beranda');
        Route::post('/beranda/store', [KemahasiswaanController::class, 'store'])->name('pengumuman.store');
        Route::delete('/beranda/{id}', [KemahasiswaanController::class, 'destroy'])->name('pengumuman.destroy');
        Route::get('/pengumuman/{id}', [KemahasiswaanController::class, 'show'])->name('pengumunankonselor.detail');
        Route::post('/calendar/upload', [CalendarController::class, 'upload'])->name('calendar.upload');

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
            Route::post('/konseling/ajukan', [AjukanKonselingController::class, 'ajukanKonseling'])->name('konseling.ajukan');
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
        Route::get('/beranda', [KonselorController::class, 'index'])->name('beranda');
        Route::post('/beranda/store', [KonselorController::class, 'store'])->name('pengumuman.store');
        Route::delete('/beranda/{id}', [KonselorController::class, 'destroy'])->name('pengumuman.destroy');
        Route::get('/pengumuman/{id}', [KonselorController::class, 'show'])->name('pengumunankonselor.detail');
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
            Route::get('/ajukan', [AjukanKonselingController::class, 'index'])->name('konseling.ajukan');
            Route::get('/cari', [AjukanKonselingController::class, 'cariMahasiswa'])->name('konseling.cari');
            Route::post('/konseling/ajukan', [AjukanKonselingController::class, 'ajukanKonseling'])->name('konseling.ajukan');
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
    Route::get('/catatan_perilaku', [CatatanPerilakuController::class, 'index'])->name('catatan_perilaku');

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

// Middleware untuk dosen
Route::middleware(['auth.session', 'role:dosen'])->group(function () {


    Route::get('/dosen/beranda', [DosenController::class, 'beranda'])->name('dosen');
    Route::get('/dosen/perwalian', [DosenController::class, 'index'])->name('dosen.perwalian');
    Route::get('/dosen/presensi', [DosenController::class, 'presensi'])->name('dosen.presensi');
    Route::get('/dosen/absensi-mahasiswa', [AbsensiController::class, 'index'])->name('absensi');

    Route::get('/absensi-mahasiswa/{date}/{class}', [AbsensiController::class, 'show'])->name('absensi.show');
    Route::post('/absensi-mahasiswa/{date}/{class}', [AbsensiController::class, 'store'])->name('absensi.store');
    Route::get('/set-perwalian', [SetPerwalianController::class, 'index'])->name('set.perwalian');
    Route::post('/set-perwalian/store', [SetPerwalianController::class, 'store'])->name('set.perwalian.store');
    Route::delete('/set-perwalian/destroy', [SetPerwalianController::class, 'destroy'])->name('set.perwalian.destroy');
    Route::get('/set-perwalian/calendar', [SetPerwalianController::class, 'getCalendar'])->name('set.perwalian.calendar');
    Route::get('/dosen/detailed-class/{year}/{kelas}', [DosenController::class, 'showDetailedClass'])->name('dosen.detailedClass');
    Route::get('/set-perwalian/histori', [SetPerwalianController::class, 'histori'])->name('dosen.histori');

    Route::get('/perwalian/berita-acara', [BeritaAcaraController::class, 'index'])->name('perwalian.berita_acara');
    Route::post('/perwalian/berita-acara', [BeritaAcaraController::class, 'store'])->name('perwalian.berita_acara.store');

    Route::get('/berita-acara/select-class', [BeritaAcaraController::class, 'selectClass'])->name('berita_acara.select_class');
    Route::get('/berita-acara', [BeritaAcaraController::class, 'index'])->name('berita_acara.index');
    Route::get('/berita-acara/create', [BeritaAcaraController::class, 'create'])->name('berita_acara.create');
    Route::post('/berita-acara/store', [BeritaAcaraController::class, 'store'])->name('berita_acara.store');
    Route::get('/berita-acara/{id}', [BeritaAcaraController::class, 'show'])->name('berita_acara.show');
    Route::get('/berita-acara/success', [BeritaAcaraController::class, 'successPage'])->name('berita-acara.success');
    Route::get('/berita-acara/success/{kelas}/{tanggal_perwalian}', [BeritaAcaraController::class, 'success'])
        ->name('berita-acara.success');

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

Route::post('/send-sms', [SmsController::class, 'send']);
Route::get('/send-sms', [SmsController::class, 'create']);