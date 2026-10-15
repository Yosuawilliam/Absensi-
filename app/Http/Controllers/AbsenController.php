<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Lokasi;
use App\Models\Twibbon;
use App\Services\AbsensiService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\Request;

class AbsenController extends Controller
{
    public function __construct(private AbsensiService $service)
    {
    }

    public function index(Request $request)
    {
        $pegawai = $request->attributes->get('pegawai');
        $hariIni = Absensi::where('pegawai_id', $pegawai->id)->whereDate('tanggal', today())->first();

        return view('pegawai.absen', [
            'pegawai' => $pegawai,
            'absen' => $hariIni,
            'twibbon' => Twibbon::berlaku(),
            'libur' => $this->service->keteranganLibur(today()),
            'lokasis' => Lokasi::where('aktif', true)->get(['nama', 'lat', 'lng', 'radius']),
        ]);
    }

    public function masuk(Request $request)
    {
        return $this->proses($request, 'masuk');
    }

    public function pulang(Request $request)
    {
        return $this->proses($request, 'pulang');
    }

    private function proses(Request $request, string $jenis)
    {
        $request->validate([
            'foto' => 'required|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);

        $pegawai = $request->attributes->get('pegawai');
        $lat = $request->filled('lat') ? (float) $request->lat : null;
        $lng = $request->filled('lng') ? (float) $request->lng : null;

        try {
            $absen = $jenis === 'masuk'
                ? $this->service->masukPegawai($pegawai, $request->foto, $lat, $lng)
                : $this->service->pulangPegawai($pegawai, $request->foto, $lat, $lng);
        } catch (DomainException $e) {
            return response()->json(['ok' => false, 'pesan' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok' => true,
            'pesan' => $jenis === 'masuk'
                ? 'Absen masuk berhasil pukul '.substr($absen->jam_masuk, 0, 5).' WIB.'
                : 'Absen pulang berhasil pukul '.substr($absen->jam_pulang, 0, 5).' WIB.',
            'status' => Absensi::labelStatus($absen->status),
            'jam' => substr($jenis === 'masuk' ? $absen->jam_masuk : $absen->jam_pulang, 0, 5),
            'foto' => asset($jenis === 'masuk' ? $absen->foto_masuk : $absen->foto_pulang),
        ]);
    }

    public function riwayat(Request $request)
    {
        $pegawai = $request->attributes->get('pegawai');

        try {
            $bulan = Carbon::createFromFormat('Y-m', $request->query('bulan', now()->format('Y-m')))->startOfMonth();
        } catch (\Throwable) {
            $bulan = now()->startOfMonth();
        }

        $baris = $this->service->baris($pegawai, $bulan, $bulan->copy()->endOfMonth());

        return view('pegawai.riwayat', [
            'pegawai' => $pegawai,
            'bulan' => $bulan,
            'baris' => $baris,
        ]);
    }
}
