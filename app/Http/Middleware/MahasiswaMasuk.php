<?php

namespace App\Http\Middleware;

use App\Models\Mahasiswa;
use Closure;
use Illuminate\Http\Request;

/** Memastikan mahasiswa sudah login dengan NIM sebelum mengakses absensi & twibbon. */
class MahasiswaMasuk
{
    public function handle(Request $request, Closure $next)
    {
        $mahasiswa = session('mahasiswa_id') ? Mahasiswa::with(['prodi', 'utDaerah'])->find(session('mahasiswa_id')) : null;

        if (! $mahasiswa || ! $mahasiswa->aktif) {
            session()->forget('mahasiswa_id');

            return redirect()->route('mahasiswa.masuk')->with('galat', 'Silakan masuk dengan NIM Anda terlebih dahulu untuk mengakses verifikasi absen dan twibbon.');
        }

        $request->attributes->set('mahasiswa', $mahasiswa);
        view()->share('mahasiswaMasuk', $mahasiswa);

        return $next($request);
    }
}
