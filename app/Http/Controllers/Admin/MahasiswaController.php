<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Prodi;
use App\Models\UtDaerah;
use Illuminate\Http\Request;

/** Data absensi mahasiswa yang masuk (tanpa tabel mahasiswa tetap). */
class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $q = Absensi::with(['prodi', 'utDaerah'])->where('tipe', 'mahasiswa')
            ->orderByDesc('tanggal')->orderByDesc('jam_masuk');

        self::terapkanFilter($q, $request);

        return view('admin.mahasiswa', [
            'data' => $q->paginate(20)->withQueryString(),
            'prodis' => Prodi::orderBy('nama')->get(),
            'daerahs' => UtDaerah::orderBy('nama')->get(),
        ]);
    }

    public static function terapkanFilter($q, Request $request): void
    {
        if ($dari = $request->query('dari')) {
            $q->whereDate('tanggal', '>=', $dari);
        }
        if ($sampai = $request->query('sampai')) {
            $q->whereDate('tanggal', '<=', $sampai);
        }
        if ($prodi = $request->query('prodi')) {
            $q->where('prodi_id', $prodi);
        }
        if ($daerah = $request->query('daerah')) {
            $q->where('ut_daerah_id', $daerah);
        }
        if ($cari = trim((string) $request->query('q'))) {
            $q->where(fn ($w) => $w->where('nama', 'like', "%{$cari}%")->orWhere('nim', 'like', "%{$cari}%"));
        }
    }
}
