<?php

namespace App\Http\Controllers;

use App\Models\Twibbon;

class BerandaController extends Controller
{
    public function index()
    {
        return view('beranda', ['twibbon' => Twibbon::berlaku()]);
    }

    /** Waktu resmi server (WIB), dipakai jam di halaman absen. */
    public function waktu()
    {
        return response()->json([
            'epoch' => now()->getTimestamp() * 1000,
            'tanggal' => now()->translatedFormat('l, d F Y'),
        ])->header('Cache-Control', 'no-store');
    }
}
