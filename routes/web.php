<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IBController;
use App\Http\Controllers\IKController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\KeasramaanController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\AsramaController;
use App\Http\Controllers\BursarController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DetailNilaiController;
use App\Http\Controllers\KemajuanStudiController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\CatatanPerilakuController;

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
    Route::get('/beranda', [HomeController::class, 'index'])->name('beranda');
    Route::get('/pengumuman/{id}', [HomeController::class, 'show'])->name('pengumuman.detail');
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil');
    Route::get('/perizinan/izin_bermalam', [IBController::class, 'index'])->name('izin_bermalam');
    Route::get('/perizinan/izin_keluar', [IKController::class, 'index'])->name('izin_keluar');
    Route::get('bursar/bursar', [BursarController::class, 'index'])->name('bursar');
    Route::get('/bursar/{id}', [BursarController::class, 'showDetail'])->name('bursar.detail');
    Route::get('asrama/asrama', [AsramaController::class, 'index'])->name('asrama');
    Route::get('perkuliahan/prodi', [ProdiController::class, 'index'])->name('prodi');
    Route::get('perkuliahan/jadwal', [JadwalController::class, 'index'])->name('jadwal');
    Route::get('perkuliahan/absensi', [PresensiController::class, 'index'])->name('absensi');
    Route::get('perkuliahan/{kodeMk}/detail', [PresensiController::class, 'showDetail'])->name('absensi.detail');
    Route::get('perkuliahan/kemajuan_studi', [KemajuanStudiController::class, 'index'])->name('kemajuan_studi');
    Route::get('/detailnilai/{kode_mk}', [DetailNilaiController::class, 'show'])->name('detailnilai');
    Route::get('/catatan_perilaku', [CatatanPerilakuController::class, 'index'])->name('catatan_perilaku');
});

// Middleware untuk admin
Route::middleware(['auth.session', 'role:admin'])->group(function () {
    Route::get('/beranda/admin', [AdminController::class, 'index'])->name('admin');
    Route::post('/beranda/admin/store', [AdminController::class, 'store'])->name('pengumuman.store');
    Route::delete('/beranda/admin/{id}', [AdminController::class, 'destroy'])->name('pengumuman.destroy');
    Route::get('/pengumuman/admin/{id}', [AdminController::class, 'show'])->name('pengumumanadmin.detail');
    Route::post('/calendar/upload', [CalendarController::class, 'upload'])->name('calendar.upload');
});

// Middleware untuk dosen
Route::middleware(['auth.session', 'role:dosen'])->group(function () {
    Route::get('/dosen/beranda', [DosenController::class, 'beranda'])->name('dosen');
    Route::get('/dosen/perwalian', [DosenController::class, 'index'])->name('dosen.perwalian');
    Route::get('/dosen/presensi', [DosenController::class, 'presensi'])->name('dosen.presensi');
});

// Middleware untuk keasramaan
Route::middleware(['auth.session', 'role:keasramaan'])->group(function () {
    Route::get('/keasramaan/beranda', [KeasramaanController::class, 'index'])->name('keasramaan');
});

// Middleware untuk orang tua
Route::middleware(['auth.session', 'role:orang_tua'])->group(function () {
    Route::get('/orangtua/beranda', [OrangTuaController::class, 'index'])->name('orang_tua');
});