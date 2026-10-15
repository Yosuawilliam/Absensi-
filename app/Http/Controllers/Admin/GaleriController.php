<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Prodi;
use App\Models\Unit;
use Illuminate\Http\Request;

class GaleriController extends Controller
{
    public function index(Request $request)
    {
        $q = Absensi::with(['pegawai.unit', 'prodi'])
            ->where(fn ($w) => $w->whereNotNull('foto_masuk')->orWhereNotNull('foto_pulang'))
            ->orderByDesc('tanggal')->orderByDesc('jam_masuk');

        $tgl = $request->query('tanggal', today()->toDateString());
        if ($tgl) {
            $q->whereDate('tanggal', $tgl);
        }
        if (in_array($request->query('tipe'), ['pegawai', 'mahasiswa'], true)) {
            $q->where('tipe', $request->query('tipe'));
        }
        if ($status = $request->query('status')) {
            $q->where('status', $status);
        }
        if ($cari = trim((string) $request->query('q'))) {
            $q->where(function ($w) use ($cari) {
                $w->where('nama', 'like', "%{$cari}%")->orWhere('nim', 'like', "%{$cari}%")
                    ->orWhereHas('pegawai', fn ($p) => $p->where('nama', 'like', "%{$cari}%"));
            });
        }

        return view('admin.galeri', ['data' => $q->paginate(12)->withQueryString(), 'tanggal' => $tgl]);
    }
}
