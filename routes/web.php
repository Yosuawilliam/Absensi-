<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AbsenController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\MahasiswaAbsenController;
use App\Http\Controllers\MahasiswaAuthController;
use App\Http\Controllers\PegawaiAuthController;
use Illuminate\Support\Facades\Route;

/* ---------------------------- Publik ---------------------------- */
Route::get('/', [BerandaController::class, 'index'])->name('beranda');
Route::get('/waktu', [BerandaController::class, 'waktu'])->name('waktu');

/* ------------------------- Login Pegawai ------------------------ */
Route::prefix('pegawai')->group(function () {
    Route::get('/masuk', [PegawaiAuthController::class, 'form'])->name('pegawai.masuk');
    Route::get('/cari', [PegawaiAuthController::class, 'cari'])->middleware('throttle:60,1')->name('pegawai.cari');
    Route::post('/masuk', [PegawaiAuthController::class, 'masuk'])->middleware('throttle:20,1')->name('pegawai.masuk.kirim');
    Route::get('/pin', [PegawaiAuthController::class, 'pinForm'])->name('pegawai.pin');
    Route::post('/pin', [PegawaiAuthController::class, 'pinCek'])->middleware('throttle:20,1')->name('pegawai.pin.cek');
});
Route::post('/keluar', [PegawaiAuthController::class, 'keluar'])->name('keluar');

/* ------------------------- Mahasiswa ---------------------------- */
Route::prefix('mahasiswa')->group(function () {
    // Autentikasi Mahasiswa
    Route::get('/masuk', [MahasiswaAuthController::class, 'form'])->name('mahasiswa.masuk');
    Route::get('/cek-nim', [MahasiswaAuthController::class, 'cekNim'])->middleware('throttle:60,1')->name('mahasiswa.cek_nim');
    Route::post('/masuk', [MahasiswaAuthController::class, 'masuk'])->middleware('throttle:20,1')->name('mahasiswa.masuk.kirim');
    Route::post('/keluar', [MahasiswaAuthController::class, 'keluar'])->name('mahasiswa.keluar');

    // Verifikasi Absen & Twibbon (Hanya Terakses Setelah Login)
    Route::middleware('mahasiswa')->group(function () {
        Route::get('/', [MahasiswaAbsenController::class, 'index'])->name('mahasiswa');
        Route::post('/absen/masuk', [MahasiswaAbsenController::class, 'masuk'])->middleware('throttle:20,1')->name('mahasiswa.absen.masuk');
        Route::post('/absen/pulang', [MahasiswaAbsenController::class, 'pulang'])->middleware('throttle:20,1')->name('mahasiswa.absen.pulang');
    });
});

/* ------------------------- Area Pegawai ------------------------- */
Route::middleware('pegawai')->group(function () {
    Route::get('/absen', [AbsenController::class, 'index'])->name('absen');
    Route::post('/absen/masuk', [AbsenController::class, 'masuk'])->name('absen.masuk');
    Route::post('/absen/pulang', [AbsenController::class, 'pulang'])->name('absen.pulang');
    Route::get('/riwayat', [AbsenController::class, 'riwayat'])->name('riwayat');

    /* --------------------- Panel Admin --------------------------- */
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Twibbon
        Route::get('/twibbon', [Admin\TwibbonController::class, 'index'])->name('twibbon');
        Route::post('/twibbon', [Admin\TwibbonController::class, 'store'])->name('twibbon.simpan');
        Route::post('/twibbon/{twibbon}/aktifkan', [Admin\TwibbonController::class, 'aktifkan'])->name('twibbon.aktifkan');
        Route::post('/twibbon/{twibbon}/nonaktifkan', [Admin\TwibbonController::class, 'nonaktifkan'])->name('twibbon.nonaktifkan');
        Route::put('/twibbon/{twibbon}/jadwal', [Admin\TwibbonController::class, 'jadwal'])->name('twibbon.jadwal');
        Route::delete('/twibbon/{twibbon}', [Admin\TwibbonController::class, 'destroy'])->name('twibbon.hapus');

        // Pegawai
        Route::get('/pegawai', [Admin\PegawaiController::class, 'index'])->name('pegawai');
        Route::post('/pegawai', [Admin\PegawaiController::class, 'store'])->name('pegawai.simpan');
        Route::get('/pegawai/template', [Admin\PegawaiController::class, 'templateCsv'])->name('pegawai.template');
        Route::post('/pegawai/impor', [Admin\PegawaiController::class, 'impor'])->name('pegawai.impor');
        Route::put('/pegawai/{pegawai}', [Admin\PegawaiController::class, 'update'])->name('pegawai.ubah');
        Route::delete('/pegawai/{pegawai}', [Admin\PegawaiController::class, 'destroy'])->name('pegawai.hapus');

        // Master: Unit, Prodi, UT Daerah
        Route::get('/master', [Admin\MasterController::class, 'index'])->name('master');
        Route::post('/master/{jenis}', [Admin\MasterController::class, 'store'])->name('master.simpan');
        Route::put('/master/{jenis}/{id}', [Admin\MasterController::class, 'update'])->name('master.ubah');
        Route::delete('/master/{jenis}/{id}', [Admin\MasterController::class, 'destroy'])->name('master.hapus');

        // Data absensi mahasiswa
        Route::get('/mahasiswa', [Admin\MahasiswaController::class, 'index'])->name('mahasiswa');

        // Koreksi absensi manual
        Route::get('/koreksi', [Admin\AbsensiController::class, 'index'])->name('koreksi.index');
        Route::get('/koreksi/baru', [Admin\AbsensiController::class, 'create'])->name('koreksi.baru');
        Route::post('/koreksi', [Admin\AbsensiController::class, 'store'])->name('koreksi.simpan');
        Route::get('/koreksi/{absensi}', [Admin\AbsensiController::class, 'edit'])->name('koreksi.ubah');
        Route::put('/koreksi/{absensi}', [Admin\AbsensiController::class, 'update'])->name('koreksi.perbarui');
        Route::delete('/koreksi/{absensi}', [Admin\AbsensiController::class, 'destroy'])->name('koreksi.hapus');

        // Lokasi & radius
        Route::get('/lokasi', [Admin\LokasiController::class, 'index'])->name('lokasi');
        Route::post('/lokasi', [Admin\LokasiController::class, 'store'])->name('lokasi.simpan');
        Route::put('/lokasi/{lokasi}', [Admin\LokasiController::class, 'update'])->name('lokasi.ubah');
        Route::delete('/lokasi/{lokasi}', [Admin\LokasiController::class, 'destroy'])->name('lokasi.hapus');

        // Hari libur
        Route::get('/libur', [Admin\HariLiburController::class, 'index'])->name('libur');
        Route::post('/libur', [Admin\HariLiburController::class, 'store'])->name('libur.simpan');
        Route::delete('/libur/{libur}', [Admin\HariLiburController::class, 'destroy'])->name('libur.hapus');

        // Laporan & galeri
        Route::get('/laporan', [Admin\LaporanController::class, 'index'])->name('laporan');
        Route::get('/laporan/csv', [Admin\LaporanController::class, 'csv'])->name('laporan.csv');
        Route::get('/laporan/cetak', [Admin\LaporanController::class, 'cetak'])->name('laporan.cetak');
        Route::get('/galeri', [Admin\GaleriController::class, 'index'])->name('galeri');

        // Pengaturan & log
        Route::get('/pengaturan', [Admin\PengaturanController::class, 'index'])->name('pengaturan');
        Route::put('/pengaturan', [Admin\PengaturanController::class, 'update'])->name('pengaturan.simpan');
        Route::delete('/pengaturan/logo', [Admin\PengaturanController::class, 'hapusLogo'])->name('pengaturan.logo.hapus');
        Route::put('/pengaturan/pin', [Admin\PengaturanController::class, 'ubahPin'])->name('pengaturan.pin');
        Route::get('/log', [Admin\LogController::class, 'index'])->name('log');
    });
});
