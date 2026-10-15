<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Models\LogAktivitas;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HariLiburController extends Controller
{
    public function index(Request $request)
    {
        $tahun = (int) $request->query('tahun', now()->year);

        $libur = HariLibur::whereYear('tanggal', $tahun)->orderBy('tanggal')->get();

        return view('admin.libur', [
            'tahun' => $tahun,
            'libur' => $libur,
            'daftarTahun' => range(now()->year - 1, now()->year + 2),
        ]);
    }

    /** Menyimpan satu tanggal, atau rentang tanggal (cuti bersama). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'sampai' => 'nullable|date|after_or_equal:tanggal',
            'keterangan' => 'required|string|max:150',
        ], ['sampai.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.']);

        $mulai = Carbon::parse($data['tanggal']);
        $akhir = Carbon::parse($data['sampai'] ?? $data['tanggal']);

        if ($mulai->diffInDays($akhir) > 31) {
            return back()->withInput()->with('galat', 'Rentang libur maksimal 31 hari sekali input.');
        }

        $dibuat = 0;
        for ($d = $mulai->copy(); $d->lte($akhir); $d->addDay()) {
            $r = HariLibur::updateOrCreate(['tanggal' => $d->toDateString()], ['keterangan' => $data['keterangan']]);
            $dibuat += $r->wasRecentlyCreated ? 1 : 0;
        }

        LogAktivitas::catat('Tambah hari libur', $data['keterangan'].' ('.$mulai->format('d/m/Y').($mulai->ne($akhir) ? ' s.d. '.$akhir->format('d/m/Y') : '').')');

        return back()->with('sukses', 'Hari libur disimpan.');
    }

    public function destroy(HariLibur $libur)
    {
        LogAktivitas::catat('Hapus hari libur', $libur->keterangan.' ('.$libur->tanggal->format('d/m/Y').')');
        $libur->delete();

        return back()->with('sukses', 'Hari libur dihapus.');
    }
}
