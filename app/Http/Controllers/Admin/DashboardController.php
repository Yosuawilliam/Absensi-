<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\Twibbon;
use App\Services\AbsensiService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(private AbsensiService $service)
    {
    }

    public function index()
    {
        $hariIni = today();
        $hariKerja = $this->service->hariKerja($hariIni);

        $totalPegawai = Pegawai::where('aktif', true)->count();
        $pegawaiHariIni = Absensi::where('tipe', 'pegawai')->whereDate('tanggal', $hariIni)->whereNotNull('jam_masuk');
        $tepatWaktu = (clone $pegawaiHariIni)->where('status', 'hadir')->count();
        $terlambat = (clone $pegawaiHariIni)->where('status', 'terlambat')->count();
        $belum = max(0, $totalPegawai - $tepatWaktu - $terlambat);

        $mahasiswaHariIni = Absensi::where('tipe', 'mahasiswa')->whereDate('tanggal', $hariIni)->count();

        // Tren 14 hari terakhir
        $awal = $hariIni->copy()->subDays(13);
        $rows = Absensi::selectRaw('tanggal, tipe, COUNT(*) as total')
            ->whereBetween('tanggal', [$awal->toDateString(), $hariIni->toDateString()])
            ->whereNotNull('jam_masuk')
            ->groupBy('tanggal', 'tipe')
            ->get();

        $tren = [];
        for ($d = $awal->copy(); $d->lte($hariIni); $d->addDay()) {
            $key = $d->toDateString();
            $tren[] = [
                'label' => $d->format('d/m'),
                'libur' => ! $this->service->hariKerja($d),
                'pegawai' => (int) $rows->first(fn ($r) => $r->tanggal->toDateString() === $key && $r->tipe === 'pegawai')?->total,
                'mahasiswa' => (int) $rows->first(fn ($r) => $r->tanggal->toDateString() === $key && $r->tipe === 'mahasiswa')?->total,
            ];
        }

        $terbaru = Absensi::with('pegawai.unit')
            ->whereDate('tanggal', $hariIni)
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'hariKerja' => $hariKerja,
            'libur' => $this->service->keteranganLibur($hariIni),
            'totalPegawai' => $totalPegawai,
            'tepatWaktu' => $tepatWaktu,
            'terlambat' => $terlambat,
            'belum' => $belum,
            'mahasiswaHariIni' => $mahasiswaHariIni,
            'tren' => $tren,
            'terbaru' => $terbaru,
            'twibbon' => Twibbon::berlaku(),
        ]);
    }
}
