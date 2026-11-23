<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\VerificationController;
use App\Http\Controllers\Rt;
use App\Http\Controllers\Rw;
use App\Http\Controllers\Shared\AccountController;
use App\Http\Controllers\Shared\DocumentController;
use App\Http\Controllers\Shared\LetterDownloadController;
use App\Http\Controllers\Shared\NotificationController;
use App\Http\Controllers\Warga;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik
|--------------------------------------------------------------------------
*/
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/tentang', [PageController::class, 'about'])->name('about');
Route::get('/layanan', [PageController::class, 'services'])->name('services');
Route::get('/jenis-surat', [PageController::class, 'letterTypes'])->name('letter-types');
Route::get('/jenis-surat/{letterType}', [PageController::class, 'letterType'])->name('letter-types.show');

Route::get('/verifikasi', [VerificationController::class, 'index'])->name('verify.index');
Route::get('/verifikasi/{code}', [VerificationController::class, 'show'])
    ->middleware('throttle:30,1')->name('verify.show');

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', fn () => redirect()->route(auth()->user()->role->homeRoute()))->name('dashboard');

    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifikasi/{id}', [NotificationController::class, 'open'])->name('notifications.open');

    Route::get('/akun', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/akun', [AccountController::class, 'update'])->name('account.update');
    Route::put('/akun/kata-sandi', [AccountController::class, 'password'])->name('account.password');

    Route::get('/dokumen/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::get('/surat/{letterRequest}/unduh', [LetterDownloadController::class, 'download'])->name('letters.download');
});

/*
|--------------------------------------------------------------------------
| Warga
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:warga'])->prefix('warga')->name('warga.')->group(function () {
    Route::get('/', [Warga\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profil', [Warga\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [Warga\ProfileController::class, 'update'])->name('profile.update');

    Route::get('/pengajuan', [Warga\LetterRequestController::class, 'index'])->name('requests.index');
    Route::get('/pengajuan/baru', [Warga\LetterRequestController::class, 'create'])->name('requests.create');
    Route::get('/pengajuan/baru/{letterType}', [Warga\LetterRequestController::class, 'form'])->name('requests.form');
    Route::post('/pengajuan/baru/{letterType}', [Warga\LetterRequestController::class, 'store'])->name('requests.store');
    Route::get('/pengajuan/{letterRequest}', [Warga\LetterRequestController::class, 'show'])->name('requests.show');
    Route::get('/pengajuan/{letterRequest}/ubah', [Warga\LetterRequestController::class, 'edit'])->name('requests.edit');
    Route::put('/pengajuan/{letterRequest}', [Warga\LetterRequestController::class, 'update'])->name('requests.update');
    Route::post('/pengajuan/{letterRequest}/kirim', [Warga\LetterRequestController::class, 'submit'])->name('requests.submit');
    Route::delete('/pengajuan/{letterRequest}', [Warga\LetterRequestController::class, 'destroy'])->name('requests.destroy');
});

/*
|--------------------------------------------------------------------------
| Ketua RT
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:ketua_rt'])->prefix('rt')->name('rt.')->group(function () {
    Route::get('/', [Rt\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/warga', [Rt\ResidentController::class, 'index'])->name('residents.index');
    Route::get('/warga/{resident}', [Rt\ResidentController::class, 'show'])->name('residents.show');
    Route::post('/warga/{resident}/verifikasi', [Rt\ResidentController::class, 'verify'])->name('residents.verify');

    Route::get('/pengajuan', [Rt\LetterRequestController::class, 'index'])->name('requests.index');
    Route::get('/pengajuan/{letterRequest}', [Rt\LetterRequestController::class, 'show'])->name('requests.show');
    Route::post('/pengajuan/{letterRequest}/keputusan', [Rt\LetterRequestController::class, 'decide'])->name('requests.decide');

    Route::get('/riwayat', [Rt\HistoryController::class, 'index'])->name('history');
});

/*
|--------------------------------------------------------------------------
| Ketua RW
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:ketua_rw'])->prefix('rw')->name('rw.')->group(function () {
    Route::get('/', [Rw\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/pengajuan', [Rw\LetterRequestController::class, 'index'])->name('requests.index');
    Route::get('/pengajuan/{letterRequest}', [Rw\LetterRequestController::class, 'show'])->name('requests.show');
    Route::post('/pengajuan/{letterRequest}/keputusan', [Rw\LetterRequestController::class, 'decide'])->name('requests.decide');
    Route::post('/pengajuan/{letterRequest}/terbitkan', [Rw\LetterRequestController::class, 'issue'])->name('requests.issue');

    Route::get('/penerbitan', [Rw\IssuanceController::class, 'index'])->name('issuance.index');

    Route::get('/rt', [Rw\RtController::class, 'index'])->name('rts.index');
    Route::get('/rt/baru', [Rw\RtController::class, 'create'])->name('rts.create');
    Route::post('/rt', [Rw\RtController::class, 'store'])->name('rts.store');
    Route::get('/rt/{rt}/ubah', [Rw\RtController::class, 'edit'])->name('rts.edit');
    Route::put('/rt/{rt}', [Rw\RtController::class, 'update'])->name('rts.update');

    Route::get('/ketua-rt', [Rw\RtHeadController::class, 'index'])->name('heads.index');
    Route::get('/ketua-rt/{rt}', [Rw\RtHeadController::class, 'edit'])->name('heads.edit');
    Route::put('/ketua-rt/{rt}', [Rw\RtHeadController::class, 'update'])->name('heads.update');
    Route::post('/ketua-rt/{rt}/status', [Rw\RtHeadController::class, 'toggle'])->name('heads.toggle');

    Route::get('/warga', [Rw\ResidentController::class, 'index'])->name('residents.index');
    Route::get('/warga/{resident}', [Rw\ResidentController::class, 'show'])->name('residents.show');

    Route::get('/jenis-surat', [Rw\LetterTypeController::class, 'index'])->name('letter-types.index');
    Route::get('/jenis-surat/baru', [Rw\LetterTypeController::class, 'create'])->name('letter-types.create');
    Route::post('/jenis-surat', [Rw\LetterTypeController::class, 'store'])->name('letter-types.store');
    Route::get('/jenis-surat/{letterType}/ubah', [Rw\LetterTypeController::class, 'edit'])->name('letter-types.edit');
    Route::put('/jenis-surat/{letterType}', [Rw\LetterTypeController::class, 'update'])->name('letter-types.update');
    Route::post('/jenis-surat/{letterType}/status', [Rw\LetterTypeController::class, 'toggle'])->name('letter-types.toggle');

    Route::get('/template', [Rw\LetterTemplateController::class, 'index'])->name('templates.index');
    Route::get('/template/{letterType}', [Rw\LetterTemplateController::class, 'edit'])->name('templates.edit');
    Route::post('/template/{letterType}', [Rw\LetterTemplateController::class, 'store'])->name('templates.store');
    Route::post('/template/{letterType}/pratinjau', [Rw\LetterTemplateController::class, 'preview'])->name('templates.preview');
    Route::post('/template/versi/{template}/aktifkan', [Rw\LetterTemplateController::class, 'activate'])->name('templates.activate');

    Route::get('/laporan', [Rw\ReportController::class, 'index'])->name('reports.index');
    Route::get('/laporan/unduh', [Rw\ReportController::class, 'export'])->name('reports.export');

    Route::get('/audit-log', [Rw\ActivityLogController::class, 'index'])->name('activity.index');

    Route::get('/pengaturan', [Rw\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/pengaturan', [Rw\SettingController::class, 'update'])->name('settings.update');
});
