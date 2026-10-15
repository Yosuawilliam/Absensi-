<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\Prodi;
use App\Models\Unit;
use App\Models\UtDaerah;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function __construct(private AbsensiService $service)
    {
    }

    public function index(Request $request)
    {
        return view('admin.laporan', [
            'lap' => $this->kumpulkan($request),
            'units' => Unit::orderBy('nama')->get(),
            'prodis' => Prodi::orderBy('nama')->get(),
            'daerahs' => UtDaerah::orderBy('nama')->get(),
            'f' => $this->filter($request),
        ]);
    }

    /** Ekspor CSV (dapat dibuka langsung di Excel). */
    public function csv(Request $request)
    {
        $lap = $this->kumpulkan($request);

        $out = "\xEF\xBB\xBF";
        $out .= $this->barisCsv([$lap['judul']]);
        $out .= $this->barisCsv(array_merge(['No'], $lap['kolom']));
        foreach ($lap['baris'] as $i => $b) {
            $out .= $this->barisCsv(array_merge([$i + 1], $b));
        }

        $nama = 'laporan-'.$this->filter($request)['tipe'].'-'.$this->filter($request)['periode'].'-'.$this->filter($request)['tanggal'].'.csv';

        return response($out, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.str_replace('/', '-', $nama).'"',
        ]);
    }

    /** Halaman siap cetak; di browser pilih "Simpan sebagai PDF". */
    public function cetak(Request $request)
    {
        return view('admin.laporan-cetak', ['lap' => $this->kumpulkan($request)]);
    }

    /* ------------------------------------------------------------------ */

    private function filter(Request $r): array
    {
        $periode = $r->query('periode') === 'bulanan' ? 'bulanan' : 'harian';
        $tipe = $r->query('tipe') === 'mahasiswa' ? 'mahasiswa' : 'pegawai';

        try {
            $tanggal = Carbon::parse($r->query('tanggal', today()->toDateString()))->startOfDay();
        } catch (\Throwable) {
            $tanggal = today();
        }
        if ($tanggal->isFuture()) {
            $tanggal = today();
        }

        return [
            'periode' => $periode,
            'tipe' => $tipe,
            'tanggal' => $tanggal->toDateString(),
            'bulan' => $tanggal->format('Y-m'),
            'unit' => $r->query('unit'),
            'prodi' => $r->query('prodi'),
            'daerah' => $r->query('daerah'),
            'status' => $r->query('status'),
            'tgl' => $tanggal,
        ];
    }

    private function kumpulkan(Request $r): array
    {
        $f = $this->filter($r);
        $bulanan = $f['periode'] === 'bulanan';

        if ($f['tipe'] === 'pegawai') {
            return $bulanan ? $this->bulananPegawai($f) : $this->harianPegawai($f);
        }

        return $bulanan ? $this->bulananMahasiswa($f) : $this->harianMahasiswa($f);
    }

    private function harianPegawai(array $f): array
    {
        $tgl = $f['tgl'];
        $pegawais = Pegawai::with('unit')->where('aktif', true)
            ->when($f['unit'], fn ($q, $u) => $q->where('unit_id', $u))
            ->orderBy('nama')->get();

        $absen = Absensi::where('tipe', 'pegawai')->whereDate('tanggal', $tgl)->get()->keyBy('pegawai_id');
        $hitung = ['hadir' => 0, 'terlambat' => 0, 'tidak_hadir' => 0, 'belum' => 0, 'libur' => 0];
        $baris = [];

        foreach ($pegawais as $p) {
            $a = $absen->get($p->id);
            $status = $this->service->statusHari($p, $tgl, $a);
            if ($status === null) {
                continue;
            }
            $hitung[$status]++;

            // Filter Status
            if (! empty($f['status'])) {
                $cocok = match ($f['status']) {
                    'semua_hadir' => in_array($status, ['hadir', 'terlambat'], true),
                    'hadir' => $status === 'hadir',
                    'terlambat' => $status === 'terlambat',
                    'tidak_hadir' => in_array($status, ['tidak_hadir', 'belum'], true),
                    default => true,
                };
                if (! $cocok) {
                    continue;
                }
            }

            $baris[] = [
                $p->nip, $p->nama, $p->jabatan ?: '-', $p->unit->nama ?? '-',
                $a && $a->jam_masuk ? substr($a->jam_masuk, 0, 5) : '-',
                $a && $a->jam_pulang ? substr($a->jam_pulang, 0, 5) : '-',
                $this->label($status).($a && $a->dikoreksi ? ' (dikoreksi)' : ''),
            ];
        }

        $libur = $this->service->keteranganLibur($tgl);
        $statusSuffix = $this->statusSuffix($f['status'] ?? null);

        return [
            'judul' => 'Laporan Harian Pegawai — '.$tgl->translatedFormat('l, d F Y').($libur ? ' ('.$libur.')' : '').$statusSuffix,
            'kolom' => ['NIP', 'Nama', 'Jabatan', 'Unit', 'Jam Masuk', 'Jam Pulang', 'Status'],
            'baris' => $baris,
            'ringkasan' => [
                'Hadir' => $hitung['hadir'], 'Terlambat' => $hitung['terlambat'],
                'Tidak Hadir' => $hitung['tidak_hadir'], 'Belum Absen' => $hitung['belum'], 'Libur' => $hitung['libur'],
            ],
        ];
    }

    private function harianMahasiswa(array $f): array
    {
        $q = Absensi::with(['prodi', 'utDaerah'])->where('tipe', 'mahasiswa')
            ->whereDate('tanggal', $f['tgl'])
            ->when($f['prodi'], fn ($q, $v) => $q->where('prodi_id', $v))
            ->when($f['daerah'], fn ($q, $v) => $q->where('ut_daerah_id', $v))
            ->when($f['status'], function ($q, $st) {
                if ($st === 'hadir') {
                    $q->where('status', 'hadir');
                } elseif ($st === 'terlambat') {
                    $q->where('status', 'terlambat');
                } elseif ($st === 'tidak_hadir') {
                    $q->whereRaw('1 = 0');
                }
            })
            ->orderBy('jam_masuk');

        $data = $q->get();
        $baris = $data->map(fn ($a) => [
            $a->nim, $a->nama, $a->no_hp, $a->email, $a->prodi->nama ?? '-', $a->utDaerah->nama ?? '-',
            $a->jam_masuk ? substr($a->jam_masuk, 0, 5) : '-',
            $a->jam_pulang ? substr($a->jam_pulang, 0, 5) : '-',
            $this->label($a->status).($a->dikoreksi ? ' (dikoreksi)' : ''),
        ])->all();

        $statusSuffix = $this->statusSuffix($f['status'] ?? null);

        return [
            'judul' => 'Laporan Harian Mahasiswa — '.$f['tgl']->translatedFormat('l, d F Y').$statusSuffix,
            'kolom' => ['NIM', 'Nama', 'No. HP', 'Email', 'Prodi', 'UT Daerah', 'Jam Masuk', 'Jam Pulang', 'Status'],
            'baris' => $baris,
            'ringkasan' => [
                'Total Ditampilkan' => count($baris),
                'Tepat Waktu' => $data->where('status', 'hadir')->count(),
                'Terlambat' => $data->where('status', 'terlambat')->count(),
            ],
        ];
    }

    private function bulananPegawai(array $f): array
    {
        $awal = $f['tgl']->copy()->startOfMonth();
        $akhir = $awal->copy()->endOfMonth()->min(today());

        $pegawais = Pegawai::with('unit')->where('aktif', true)
            ->when($f['unit'], fn ($q, $u) => $q->where('unit_id', $u))
            ->orderBy('nama')->get();

        $absen = Absensi::where('tipe', 'pegawai')
            ->whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->get()->groupBy('pegawai_id');

        $totHadir = $totTelat = $totAlpa = 0;
        $baris = [];

        foreach ($pegawais as $p) {
            $perTgl = ($absen->get($p->id) ?? collect())->keyBy(fn ($a) => $a->tanggal->toDateString());
            $hadir = $telat = $alpa = 0;

            for ($d = $awal->copy(); $d->lte($akhir); $d->addDay()) {
                $status = $this->service->statusHari($p, $d, $perTgl->get($d->toDateString()));
                if ($status === 'hadir') {
                    $hadir++;
                } elseif ($status === 'terlambat') {
                    $telat++;
                } elseif ($status === 'tidak_hadir') {
                    $alpa++;
                }
            }

            $totHadir += $hadir;
            $totTelat += $telat;
            $totAlpa += $alpa;

            // Filter Status
            if (! empty($f['status'])) {
                $cocok = match ($f['status']) {
                    'semua_hadir' => ($hadir + $telat) > 0,
                    'hadir' => $hadir > 0,
                    'terlambat' => $telat > 0,
                    'tidak_hadir' => $alpa > 0,
                    default => true,
                };
                if (! $cocok) {
                    continue;
                }
            }

            $baris[] = [$p->nip, $p->nama, $p->unit->nama ?? '-', $hadir, $telat, $alpa];
        }

        $statusSuffix = $this->statusSuffix($f['status'] ?? null);

        return [
            'judul' => 'Rekap Bulanan Pegawai — '.$awal->translatedFormat('F Y').$statusSuffix,
            'kolom' => ['NIP', 'Nama', 'Unit', 'Hadir', 'Terlambat', 'Tidak Hadir'],
            'baris' => $baris,
            'ringkasan' => ['Total Hadir' => $totHadir, 'Total Terlambat' => $totTelat, 'Total Tidak Hadir' => $totAlpa],
        ];
    }

    private function bulananMahasiswa(array $f): array
    {
        $awal = $f['tgl']->copy()->startOfMonth();
        $akhir = $awal->copy()->endOfMonth();

        $data = Absensi::with(['prodi', 'utDaerah'])->where('tipe', 'mahasiswa')
            ->whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->when($f['prodi'], fn ($q, $v) => $q->where('prodi_id', $v))
            ->when($f['daerah'], fn ($q, $v) => $q->where('ut_daerah_id', $v))
            ->orderBy('tanggal')->get();

        if ($f['status'] === 'terlambat') {
            $data = $data->where('status', 'terlambat');
        } elseif ($f['status'] === 'hadir') {
            $data = $data->where('status', 'hadir');
        }

        $baris = [];
        foreach ($data->groupBy('nim') as $nim => $rows) {
            $akhirData = $rows->last();
            $baris[] = [
                $nim, $akhirData->nama, $akhirData->prodi->nama ?? '-', $akhirData->utDaerah->nama ?? '-',
                $rows->count(), $rows->where('status', 'terlambat')->count(),
            ];
        }
        usort($baris, fn ($a, $b) => strcmp($a[1], $b[1]));

        $statusSuffix = $this->statusSuffix($f['status'] ?? null);

        return [
            'judul' => 'Rekap Bulanan Mahasiswa — '.$awal->translatedFormat('F Y').$statusSuffix,
            'kolom' => ['NIM', 'Nama', 'Prodi', 'UT Daerah', 'Hari Hadir', 'Terlambat'],
            'baris' => $baris,
            'ringkasan' => ['Mahasiswa' => count($baris), 'Total Absensi' => $data->count(), 'Terlambat' => $data->where('status', 'terlambat')->count()],
        ];
    }

    private function statusSuffix(?string $status): string
    {
        return match ($status) {
            'semua_hadir' => ' [Filter: Semua Hadir]',
            'hadir' => ' [Filter: Hadir Tepat Waktu]',
            'terlambat' => ' [Filter: Terlambat]',
            'tidak_hadir' => ' [Filter: Tidak Hadir / Belum]',
            default => '',
        };
    }

    private function label(string $status): string
    {
        return $status === 'belum' ? 'Belum Absen' : Absensi::labelStatus($status);
    }

    private function barisCsv(array $kolom): string
    {
        $kolom = array_map(function ($v) {
            $v = (string) $v;
            // Cegah formula injection di Excel
            if ($v !== '' && in_array($v[0], ['=', '+', '-', '@'], true)) {
                $v = "'".$v;
            }

            return '"'.str_replace('"', '""', $v).'"';
        }, $kolom);

        return implode(';', $kolom)."\r\n";
    }
}
