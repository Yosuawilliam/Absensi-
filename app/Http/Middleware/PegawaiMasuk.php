<?php

namespace App\Http\Middleware;

use App\Models\Pegawai;
use Closure;
use Illuminate\Http\Request;

/** Memastikan pegawai sudah "masuk" (memilih namanya di halaman login pegawai). */
class PegawaiMasuk
{
    public function handle(Request $request, Closure $next)
    {
        $pegawai = session('pegawai_id') ? Pegawai::with('unit')->find(session('pegawai_id')) : null;

        if (! $pegawai || ! $pegawai->aktif) {
            session()->forget(['pegawai_id', 'admin_ok']);

            return redirect()->route('pegawai.masuk')->with('galat', 'Silakan masuk terlebih dahulu.');
        }

        // Admin yang belum memasukkan PIN diarahkan ke halaman PIN
        if ($pegawai->is_admin && ! session('admin_ok')) {
            return redirect()->route('pegawai.pin');
        }

        $request->attributes->set('pegawai', $pegawai);
        view()->share('pegawaiMasuk', $pegawai);

        return $next($request);
    }
}
