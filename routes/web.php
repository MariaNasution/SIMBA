<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MahasiswaHomeController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\KeasramaanController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DetailNilaiController;
use App\Http\Controllers\KemajuanStudiController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\CatatanPerilakuController;
use App\Http\Controllers\SetPerwalianController;
use App\Http\Controllers\DaftarPelanggaranController;
use App\Http\Controllers\AjukanKonselingController;
use App\Http\Controllers\MahasiswaKonselingController;
use App\Http\Controllers\MahasiswaPerwalianController;
use App\Http\Controllers\MahasiswaRequestKonselingController;
use App\Http\Controllers\RiwayatKonselingController;
use App\Http\Controllers\HasilKonselingController;
use App\Http\Controllers\DaftarRequestKonselingController;
use App\Http\Controllers\StudentBehaviorController;

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
});

// Middleware untuk admin
Route::middleware(['auth.session', 'role:admin'])->group(function () {
    Route::get('/beranda/admin', [AdminController::class, 'index'])->name('admin');
    Route::post('/beranda/admin/store', [AdminController::class, 'store'])->name('pengumuman.store');
    Route::delete('/beranda/admin/{id}', [AdminController::class, 'destroy'])->name('pengumuman.destroy');
    Route::get('/pengumuman/admin/{id}', [AdminController::class, 'show'])->name('pengumumanadmin.detail');
    Route::post('/calendar/upload', [CalendarController::class, 'upload'])->name('calendar.upload');

    // Konseling
    Route::prefix('konseling')->group(function () {
        Route::get('/daftar_pelanggaran', [DaftarPelanggaranController::class, 'daftarPelanggaran'])->name('daftar_pelanggaran');
        Route::get('/hasil_konseling', [AdminController::class, 'hasilKonseling'])->name('hasil_konseling');
        Route::get('/riwayat_konseling', [RiwayatKonselingController::class, 'index'])->name('riwayat_konseling');
        Route::get('/konseling_lanjutan', [AdminController::class, 'konselingLanjutan'])->name('konseling_lanjutan');
        Route::get('/ajukan_konseling', [AdminController::class, 'ajukanKonseling'])->name('ajukan_konseling');
        Route::get('/daftar_request', [AdminController::class, 'daftarRequest'])->name('daftar_request');

        Route::get('/hasil', [HasilKonselingController::class, 'index'])->name('hasil.index');
        Route::post('/hasil', [HasilKonselingController::class, 'store'])->name('hasil.store');
        Route::get('/hasil/{id}', [HasilKonselingController::class, 'show'])->name('hasil.show');
        Route::delete('/hasil/{id}', [HasilKonselingController::class, 'destroy'])->name('hasil.destroy');

        //daftar request
        Route::get('/admin/daftar-request', [DaftarRequestKonselingController::class, 'daftarRequest'])->name('daftar_request');
        Route::put('/admin/approve-konseling/{id}', [DaftarRequestKonselingController::class, 'approve'])->name('approve_konseling');
        Route::put('/admin/reject-konseling/{id}', [DaftarRequestKonselingController::class, 'reject'])->name('reject_konseling');

        Route::prefix('konseling')->group(function () {
            Route::get('/ajukan', [AjukanKonselingController::class, 'index'])->name('konseling.ajukan');
            Route::get('/cari', [AjukanKonselingController::class, 'cariMahasiswa'])->name('konseling.cari');
            Route::post('/submit', [AjukanKonselingController::class, 'submit'])->name('konseling.ajukan');
            Route::get('/caririwayat', [RiwayatkonselingController::class, 'CariRiwayatMahasiswa'])->name('konseling.caririwayat');
            // Menampilkan semua riwayat konseling mahasiswa
            Route::get('/riwayat-konseling', [RiwayatKonselingController::class, 'index'])->name('riwayat.konseling');
            Route::post('/hasil-konseling/upload', [HasilKonselingController::class, 'upload'])->name('hasil_konseling.upload');

            // Mencari riwayat konseling mahasiswa berdasarkan NIM
            Route::get('/riwayat-konseling/cari', [RiwayatKonselingController::class, 'CariRiwayatMahasiswa'])->name('riwayat.konseling.cari');
        });

        Route::get('/konseling-lanjutan', [AdminController::class, 'konselingLanjutan'])->name('konseling.lanjutan');
    });
    
});

// Middleware untuk dosen
Route::middleware(['auth.session', 'role:dosen'])->group(function () {
    Route::get('/dosen/beranda', [DosenController::class, 'beranda'])->name('dosen');
    Route::get('/dosen/perwalian', [DosenController::class, 'index'])->name('dosen.perwalian');
    Route::get('/dosen/presensi', [DosenController::class, 'presensi'])->name('dosen.presensi');
    Route::get('/dosen/absensi-mahasiswa', [AbsensiController::class, 'index'])->name('absensi');
    Route::get('/absensi-mahasiswa/{date}/{class}', [AbsensiController::class, 'show'])->name('absensi.show');
    Route::get('/set-perwalian', [SetPerwalianController::class, 'index'])->name('set.perwalian');
    Route::post('/set-perwalian', [SetPerwalianController::class, 'store'])->name('set.perwalian.store');
});

// Middleware untuk keasramaan
Route::middleware(['auth.session', 'ensure.student.data.all.student', 'role:keasramaan'])->group(function () {
    Route::get('/keasramaan/beranda', [KeasramaanController::class, 'index'])->name('keasramaan');
    Route::get('/keasramaan/catatan-perilaku', [KeasramaanController::class, 'pelanggaran'])->name('pelanggaran_keasramaan');
    Route::get('/keasramaan/catatan-perilaku/detail/{studentNim}', [KeasramaanController::class, 'detail'])->name('catatan_perilaku_detail');

Route::prefix('student-behaviors')->group(function () {
    Route::get('/create/{studentNim}/{ta}/{semester}', [StudentBehaviorController::class, 'create'])
        ->name('student_behaviors.create');

    Route::post('/store', [StudentBehaviorController::class, 'store'])
        ->name('student_behaviors.store');
        
    Route::get('/{id}/edit', [StudentBehaviorController::class, 'edit'])
        ->name('student_behaviors.edit');

    Route::post('/{id}/update', [StudentBehaviorController::class, 'update'])
        ->name('student_behaviors.update');
        
    Route::delete('/{id}/destroy', [StudentBehaviorController::class, 'destroy'])
        ->name('student_behaviors.destroy');
});

});

// Middleware untuk orang tua
Route::middleware(['auth.session', 'ensure.student.data.ortu', 'role:orang_tua'])->group(function () {
    Route::get('/orang_tua/beranda', [OrangTuaController::class, 'index'])->name('orang_tua');
    Route::get('/orang_tua/catatan_perilaku', [OrangTuaController::class, 'catatan_perilaku']) ->name('catatan_perilaku_orang_tua');
});

Route::get('/mahasiswa/request-konseling', [MahasiswaRequestKonselingController::class, 'create'])->name('mhs_konseling_request');
Route::post('/mahasiswa/request-konseling', [MahasiswaRequestKonselingController::class, 'store'])->name('mhs_konseling_request.store');
Route::get('/mahasiswa/request-konseling', [MahasiswaRequestKonselingController::class, 'create'])->name('mhs_konseling_request');
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
});

Route::get('/mahasiswa/konseling/request', [MahasiswaRequestKonselingController::class, 'create'])->name('mhs_konseling_request');
Route::post('/mahasiswa/konseling/store', [MahasiswaRequestKonselingController::class, 'store'])->name('mhs_konseling_store');
