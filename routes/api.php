<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MahasiswaHomeController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth.session', 'role:mahasiswa'])->group(function () {
    Route::get('/advertisements', [MahasiswaHomeController::class, 'fetchAdvertisements'])->name('api.advertisements');
});