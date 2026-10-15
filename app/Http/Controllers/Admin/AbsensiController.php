<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\LogAktivitas;
use App\Models\Lokasi;
use App\Models\Pegawai;
use App\Models\Prodi;
use App\Models\UtDaerah;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

/** Koreksi absensi manual oleh admin. Data asli selalu disimpan di log. */
class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $q = Absensi::with(['pegawai', 'prodi'])->orderByDesc('tanggal')->orderByDesc('jam_masuk');

        if ($tipe = $request->query('tipe')) {
            $q->where('tipe', $tipe);
        }
        if ($tgl = $request->query('tanggal')) {
            $q->whereDate('tanggal', $tgl);
        }
        if ($cari = trim((string) $request->query('q'))) {
            $q->where(function ($w) use ($cari) {
                $w->where('nama', 'like', "%{$cari}%")
                    ->orWhere('nim', 'like', "%{$cari}%")
                    ->orWhereHas('pegawai', fn ($p) => $p->where('nama', 'like', "%{$cari}%")->orWhere('nip', 'like', "%{$cari}%"));
            });
        }

        return view('admin.koreksi.index', ['data' => $q->paginate(20)->withQueryString()]);
    }

    public function create()
    {
        return view('admin.koreksi.form', $this->opsi(['absen' => new Absensi(['tipe' => 'pegawai', 'tanggal' => today(), 'status' => 'hadir'])]));
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['dikoreksi'] = true;

        try {
            $a = Absensi::create($data);
        } catch (QueryException $e) {
            return back()->withInput()->with('galat', 'Data absensi untuk orang dan tanggal tersebut sudah ada. Cari datanya dan ubah dari daftar.');
        }

        LogAktivitas::catat('Tambah absensi manual', $this->ringkas($a).' | Alasan: '.$data['catatan_koreksi']);

        return redirect()->route('admin.koreksi.index')->with('sukses', 'Absensi manual ditambahkan.');
    }

    public function edit(Absensi $absensi)
    {
        return view('admin.koreksi.form', $this->opsi(['absen' => $absensi]));
    }

    public function update(Request $request, Absensi $absensi)
    {
        $data = $this->validasi($request, $absensi);
        $asli = $absensi->only(['tipe', 'pegawai_id', 'tanggal', 'nama', 'nim', 'jam_masuk', 'jam_pulang', 'status', 'catatan_koreksi']);

        try {
            $absensi->update($data + ['dikoreksi' => true]);
        } catch (QueryException $e) {
            return back()->withInput()->with('galat', 'Perubahan bentrok dengan data absensi lain pada tanggal yang sama.');
        }

        LogAktivitas::catat('Koreksi absensi', $this->ringkas($absensi).' | Alasan: '.$data['catatan_koreksi'], $asli);

        return redirect()->route('admin.koreksi.index')->with('sukses', 'Koreksi absensi disimpan. Data asli tercatat di log aktivitas.');
    }

    public function destroy(Request $request, Absensi $absensi)
    {
        $request->validate(['alasan' => 'required|string|max:255'], ['alasan.required' => 'Alasan penghapusan wajib diisi.']);
        LogAktivitas::catat('Hapus absensi', $this->ringkas($absensi).' | Alasan: '.$request->alasan, $absensi->only(['tipe', 'pegawai_id', 'tanggal', 'nama', 'nim', 'jam_masuk', 'jam_pulang', 'status']));
        $absensi->delete();

        return back()->with('sukses', 'Data absensi dihapus.');
    }

    private function validasi(Request $request, ?Absensi $absensi = null): array
    {
        $tipe = $request->input('tipe', $absensi?->tipe);

        $aturan = [
            'tipe' => 'required|in:pegawai,mahasiswa',
            'tanggal' => 'required|date|before_or_equal:today',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i|after_or_equal:jam_masuk',
            'status' => 'required|in:hadir,terlambat,tidak_hadir',
            'catatan_koreksi' => 'required|string|max:255',
        ];

        if ($tipe === 'pegawai') {
            $aturan['pegawai_id'] = 'required|exists:pegawais,id';
        } else {
            $aturan += [
                'nama' => 'required|string|max:120',
                'nim' => ['required', 'regex:/^[0-9]{6,20}$/'],
                'no_hp' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:120',
                'prodi_id' => 'nullable|exists:prodis,id',
                'ut_daerah_id' => 'nullable|exists:ut_daerahs,id',
            ];
        }

        $data = $request->validate($aturan, [
            'catatan_koreksi.required' => 'Alasan koreksi wajib diisi.',
            'jam_pulang.after_or_equal' => 'Jam pulang tidak boleh lebih awal dari jam masuk.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
        ]);

        if ($tipe === 'pegawai') {
            $data += ['nama' => null, 'nim' => null, 'no_hp' => null, 'email' => null, 'prodi_id' => null, 'ut_daerah_id' => null];
        } else {
            $data['pegawai_id'] = null;
        }

        foreach (['jam_masuk', 'jam_pulang'] as $k) {
            $data[$k] = ! empty($data[$k]) ? $data[$k].':00' : null;
        }
        if ($data['status'] === 'tidak_hadir') {
            $data['jam_masuk'] = $data['jam_pulang'] = null;
        }

        return $data;
    }

    private function opsi(array $tambah): array
    {
        return $tambah + [
            'pegawais' => Pegawai::where('aktif', true)->orderBy('nama')->get(['id', 'nama', 'nip']),
            'prodis' => Prodi::orderBy('nama')->get(),
            'daerahs' => UtDaerah::orderBy('nama')->get(),
        ];
    }

    private function ringkas(Absensi $a): string
    {
        $a->loadMissing('pegawai');

        return $a->namaTampil().' · '.$a->tanggal->format('d/m/Y').' · masuk '.($a->jam_masuk ? substr($a->jam_masuk, 0, 5) : '-')
            .' · pulang '.($a->jam_pulang ? substr($a->jam_pulang, 0, 5) : '-').' · '.Absensi::labelStatus($a->status);
    }
}
