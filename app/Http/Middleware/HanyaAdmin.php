<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Hanya pegawai bertanda admin (dan sudah memasukkan PIN) yang boleh lewat. */
class HanyaAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $pegawai = $request->attributes->get('pegawai');

        if (! $pegawai || ! $pegawai->is_admin || ! session('admin_ok')) {
            // Tidak membocorkan keberadaan panel admin kepada pegawai biasa
            abort(404);
        }

        return $next($request);
    }
}
