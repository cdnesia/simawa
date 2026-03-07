<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BiodataController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JadwalPerkuliahanController;
use App\Http\Controllers\KalenderAkademikController;
use App\Http\Controllers\KalenderController;
use App\Http\Controllers\KhsController;
use App\Http\Controllers\KknController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PklController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\KrsController;
use App\Http\Controllers\PendaftaranKKNController;
use App\Http\Controllers\PendaftaranPKLController;
use App\Http\Controllers\PendaftaranSeminarController;
use App\Http\Controllers\PendaftaranSidangController;
use App\Http\Controllers\RiwayatPembayaranController;
use App\Http\Controllers\TaController;
use App\Http\Controllers\TranskripController;
use App\Http\Controllers\TranskripNilaiController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\WisudaController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::get('/beranda', [HomeController::class, 'index'])->name('home');
    Route::get('/generate-va', [HomeController::class, 'generateVA'])->name('generate-va');
    Route::resource('jadwal-perkuliahan', JadwalPerkuliahanController::class)->only('index');
    Route::resource('biodata', BiodataController::class);
    Route::get('khs.print', [KhsController::class, 'print'])->name('khs.print');
    Route::resource('khs', KhsController::class)->only('index');
    Route::get('krs.print', [KrsController::class, 'print'])->name('krs.print');
    Route::resource('krs', KrsController::class)->only('index', 'create', 'store', 'destroy');
    Route::resource('transkrip-nilai', TranskripNilaiController::class);
    Route::resource('pendaftaran-kkn', PendaftaranKKNController::class);
    Route::resource('pendaftaran-pkl', PendaftaranPKLController::class);
    Route::resource('pendaftaran-sidang-tugas-akhir', PendaftaranSeminarController::class);
    Route::resource('pendaftaran-seminar-proposal', PendaftaranSidangController::class);
    Route::resource('riwayat-pembayaran', RiwayatPembayaranController::class)->only('index');
    Route::resource('wisuda', WisudaController::class);

    Route::get('/bantuan', function () {
        return redirect()->away('mailto:ict@umjambi.ac.id?subject=Permintaan Bantuan');
    })->name('bantuan');

    Route::get('/reset-password', [AuthController::class, 'showResetForm'])->name('password.request');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
