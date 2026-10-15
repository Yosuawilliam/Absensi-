<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Lokasi;
use App\Models\Twibbon;
use App\Services\AbsensiService;
use DomainException;
use Illuminate\Http\Request;

class MahasiswaAbsenController extends Controller
{
    public function __construct(private AbsensiService $service)
    {
    }

    public function index(Request $request)
    {
        $mahasiswa = $request->attributes->get('mahasiswa');
        $hariIni = $this->service->absenMahasiswaHariIni($mahasiswa->nim);

        return view('mahasiswa.absen', [
            'mahasiswa' => $mahasiswa,
            'absen' => $hariIni,
            'twibbon' => Twibbon::berlaku(),
            'libur' => $this->service->keteranganLibur(today()),
            'lokasis' => Lokasi::where('aktif', true)->get(['nama', 'lat', 'lng', 'radius']),
        ]);
    }

    public function masuk(Request $request)
    {
        $data = $request->validate([
            'foto' => 'required|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);

        $mahasiswa = $request->attributes->get('mahasiswa');
        $lat = $request->filled('lat') ? (float) $request->lat : null;
        $lng = $request->filled('lng') ? (float) $request->lng : null;

        try {
            $absen = $this->service->masukMahasiswa($mahasiswa, $data['foto'], $lat, $lng);
        } catch (DomainException $e) {
            return response()->json(['ok' => false, 'pesan' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok' => true,
            'pesan' => 'Absen masuk berhasil pukul '.substr($absen->jam_masuk, 0, 5).' WIB.',
            'status' => Absensi::labelStatus($absen->status),
            'jam' => substr($absen->jam_masuk, 0, 5),
            'foto' => asset($absen->foto_masuk),
        ]);
    }

    public function pulang(Request $request)
    {
        $data = $request->validate([
            'foto' => 'required|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);

        $mahasiswa = $request->attributes->get('mahasiswa');
        $lat = $request->filled('lat') ? (float) $request->lat : null;
        $lng = $request->filled('lng') ? (float) $request->lng : null;

        try {
            $absen = $this->service->pulangMahasiswa($mahasiswa, $data['foto'], $lat, $lng);
        } catch (DomainException $e) {
            return response()->json(['ok' => false, 'pesan' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok' => true,
            'pesan' => 'Absen pulang berhasil pukul '.substr($absen->jam_pulang, 0, 5).' WIB.',
            'status' => Absensi::labelStatus($absen->status),
            'jam' => substr($absen->jam_pulang, 0, 5),
            'foto' => asset($absen->foto_pulang),
        ]);
    }
}
