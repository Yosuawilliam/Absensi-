<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\HariLibur;
use App\Models\Lokasi;
use App\Models\Mahasiswa;
use App\Models\Pegawai;
use App\Models\Pengaturan;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Str;

/**
 * Seluruh aturan bisnis absensi dikumpulkan di sini supaya
 * konsisten antara pegawai, mahasiswa, admin, dan laporan.
 */
class AbsensiService
{
    /** @var array<string,string|null> cache keterangan libur per tanggal */
    private array $cacheLibur = [];

    /* ------------------------------------------------------------------ */
    /*  Hari libur                                                         */
    /* ------------------------------------------------------------------ */

    /** Mengembalikan keterangan libur untuk tanggal tsb, atau null jika hari kerja. */
    public function keteranganLibur(Carbon $tgl): ?string
    {
        $key = $tgl->toDateString();
        if (array_key_exists($key, $this->cacheLibur)) {
            return $this->cacheLibur[$key];
        }

        $libur = HariLibur::whereDate('tanggal', $key)->first();
        $hasil = null;

        if ($libur) {
            $hasil = $libur->keterangan;
        } elseif ($tgl->isSaturday() && Pengaturan::ambil('libur_sabtu') === '1') {
            $hasil = 'Akhir pekan (Sabtu)';
        } elseif ($tgl->isSunday() && Pengaturan::ambil('libur_minggu') === '1') {
            $hasil = 'Akhir pekan (Minggu)';
        }

        return $this->cacheLibur[$key] = $hasil;
    }

    public function hariKerja(Carbon $tgl): bool
    {
        return $this->keteranganLibur($tgl) === null;
    }

    /* ------------------------------------------------------------------ */
    /*  Lokasi / GPS                                                       */
    /* ------------------------------------------------------------------ */

    public static function jarakMeter(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Validasi lokasi pengguna terhadap lokasi absen aktif.
     * Mengembalikan Lokasi yang cocok, atau null bila validasi GPS tidak diwajibkan.
     *
     * @throws DomainException jika berada di luar radius / GPS tidak tersedia
     */
    public function periksaLokasi(?float $lat, ?float $lng): ?Lokasi
    {
        $daftar = Lokasi::where('aktif', true)->get();

        if (Pengaturan::ambil('gps_wajib') !== '1' || $daftar->isEmpty()) {
            return null;
        }

        if ($lat === null || $lng === null) {
            throw new DomainException('Lokasi GPS tidak terbaca. Aktifkan GPS dan izinkan akses lokasi di browser.');
        }

        $terdekat = null;
        $jarakTerdekat = PHP_FLOAT_MAX;

        foreach ($daftar as $lok) {
            $jarak = self::jarakMeter($lat, $lng, (float) $lok->lat, (float) $lok->lng);
            if ($jarak <= $lok->radius) {
                return $lok;
            }
            if ($jarak < $jarakTerdekat) {
                $jarakTerdekat = $jarak;
                $terdekat = $lok;
            }
        }

        throw new DomainException(sprintf(
            'Anda berada di luar area absen. Jarak Anda ke "%s" sekitar %s m (batas %d m).',
            $terdekat->nama,
            number_format($jarakTerdekat, 0, ',', '.'),
            $terdekat->radius
        ));
    }

    /* ------------------------------------------------------------------ */
    /*  Status                                                             */
    /* ------------------------------------------------------------------ */

    public function hitungStatusMasuk(Carbon $waktu): string
    {
        if (! $this->hariKerja($waktu)) {
            return 'hadir';
        }

        [$j, $m] = array_map('intval', explode(':', Pengaturan::ambil('jam_masuk')));
        $batas = $waktu->copy()->setTime($j, $m, 0)
            ->addMinutes((int) Pengaturan::ambil('toleransi_menit'))
            ->addSeconds(59);

        return $waktu->gt($batas) ? 'terlambat' : 'hadir';
    }

    /* ------------------------------------------------------------------ */
    /*  Foto                                                               */
    /* ------------------------------------------------------------------ */

    /**
     * Menyimpan foto dari data URL (hasil gabungan selfie + twibbon).
     * Mengembalikan path relatif terhadap folder public.
     */
    public function simpanFoto(?string $dataUrl, string $folder = 'absensi'): string
    {
        if (! $dataUrl || ! preg_match('#^data:image/(jpeg|jpg|png|webp);base64,#', $dataUrl, $m)) {
            throw new DomainException('Foto selfie wajib diambil terlebih dahulu.');
        }

        $biner = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);
        if ($biner === false || strlen($biner) < 50) {
            throw new DomainException('Data foto tidak valid.');
        }
        if (strlen($biner) > 6 * 1024 * 1024) {
            throw new DomainException('Ukuran foto terlalu besar (maksimal 6 MB).');
        }

        $info = @getimagesizefromstring($biner);
        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw new DomainException('File foto tidak dikenali.');
        }

        $ext = match ($info[2]) {
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
            default => 'jpg',
        };

        $dir = 'uploads/'.$folder.'/'.now()->format('Y/m');
        if (! is_dir(public_path($dir))) {
            mkdir(public_path($dir), 0775, true);
        }

        $nama = Str::uuid().'.'.$ext;
        file_put_contents(public_path($dir.'/'.$nama), $biner);

        return $dir.'/'.$nama;
    }

    /* ------------------------------------------------------------------ */
    /*  Absen pegawai                                                      */
    /* ------------------------------------------------------------------ */

    public function masukPegawai(Pegawai $pegawai, ?string $foto, ?float $lat, ?float $lng): Absensi
    {
        $now = now();
        $ada = Absensi::where('pegawai_id', $pegawai->id)->whereDate('tanggal', $now)->first();

        if ($ada && $ada->jam_masuk) {
            throw new DomainException('Anda sudah absen masuk hari ini pukul '.substr($ada->jam_masuk, 0, 5).'.');
        }

        $lokasi = $this->periksaLokasi($lat, $lng);
        $path = $this->simpanFoto($foto);

        $data = [
            'tipe' => 'pegawai',
            'pegawai_id' => $pegawai->id,
            'tanggal' => $now->toDateString(),
            'jam_masuk' => $now->format('H:i:s'),
            'status' => $this->hitungStatusMasuk($now),
            'foto_masuk' => $path,
            'lat_masuk' => $lat,
            'lng_masuk' => $lng,
            'lokasi_id' => $lokasi?->id,
        ];

        return $ada ? tap($ada)->update($data) : Absensi::create($data);
    }

    public function pulangPegawai(Pegawai $pegawai, ?string $foto, ?float $lat, ?float $lng): Absensi
    {
        $now = now();
        $ada = Absensi::where('pegawai_id', $pegawai->id)->whereDate('tanggal', $now)->first();

        if (! $ada || ! $ada->jam_masuk) {
            throw new DomainException('Anda belum absen masuk hari ini, jadi belum bisa absen pulang.');
        }
        if ($ada->jam_pulang) {
            throw new DomainException('Anda sudah absen pulang hari ini pukul '.substr($ada->jam_pulang, 0, 5).'.');
        }

        $this->periksaLokasi($lat, $lng);
        $path = $this->simpanFoto($foto);

        $ada->update([
            'jam_pulang' => $now->format('H:i:s'),
            'foto_pulang' => $path,
            'lat_pulang' => $lat,
            'lng_pulang' => $lng,
        ]);

        return $ada;
    }

    /* ------------------------------------------------------------------ */
    /*  Absen mahasiswa                                                    */
    /* ------------------------------------------------------------------ */

    public function masukMahasiswa(Mahasiswa|array $mahasiswa, ?string $foto, ?float $lat, ?float $lng): Absensi
    {
        $now = now();
        $isModel = $mahasiswa instanceof Mahasiswa;
        $nim = $isModel ? $mahasiswa->nim : $mahasiswa['nim'];
        $mahasiswaId = $isModel ? $mahasiswa->id : ($mahasiswa['id'] ?? null);

        $ada = Absensi::where('tipe', 'mahasiswa')->where('nim', $nim)->whereDate('tanggal', $now)->first();

        if ($ada && $ada->jam_masuk) {
            throw new DomainException('NIM '.$nim.' sudah absen masuk hari ini pukul '.substr($ada->jam_masuk, 0, 5).'.');
        }

        $lokasi = $this->periksaLokasi($lat, $lng);
        $path = $this->simpanFoto($foto);

        return Absensi::create([
            'tipe' => 'mahasiswa',
            'mahasiswa_id' => $mahasiswaId,
            'tanggal' => $now->toDateString(),
            'nama' => $isModel ? $mahasiswa->nama : $mahasiswa['nama'],
            'nim' => $nim,
            'no_hp' => $isModel ? $mahasiswa->no_hp : ($mahasiswa['no_hp'] ?? null),
            'email' => $isModel ? $mahasiswa->email : ($mahasiswa['email'] ?? null),
            'prodi_id' => $isModel ? $mahasiswa->prodi_id : ($mahasiswa['prodi_id'] ?? null),
            'ut_daerah_id' => $isModel ? $mahasiswa->ut_daerah_id : ($mahasiswa['ut_daerah_id'] ?? null),
            'jam_masuk' => $now->format('H:i:s'),
            'status' => $this->hitungStatusMasuk($now),
            'foto_masuk' => $path,
            'lat_masuk' => $lat,
            'lng_masuk' => $lng,
            'lokasi_id' => $lokasi?->id,
        ]);
    }

    public function absenMahasiswaHariIni(string $nim): ?Absensi
    {
        return Absensi::with(['prodi', 'utDaerah'])
            ->where('tipe', 'mahasiswa')
            ->where('nim', $nim)
            ->whereDate('tanggal', now())
            ->first();
    }

    public function pulangMahasiswa(Mahasiswa|string $mahasiswa, ?string $foto, ?float $lat, ?float $lng): Absensi
    {
        $isModel = $mahasiswa instanceof Mahasiswa;
        $nim = $isModel ? $mahasiswa->nim : $mahasiswa;
        $ada = $this->absenMahasiswaHariIni($nim);

        if (! $ada || ! $ada->jam_masuk) {
            throw new DomainException('NIM '.$nim.' belum absen masuk hari ini. Silakan absen masuk terlebih dahulu.');
        }
        if ($ada->jam_pulang) {
            throw new DomainException('NIM '.$nim.' sudah absen pulang hari ini pukul '.substr($ada->jam_pulang, 0, 5).'.');
        }

        $this->periksaLokasi($lat, $lng);
        $path = $this->simpanFoto($foto);

        $dataUpdate = [
            'jam_pulang' => now()->format('H:i:s'),
            'foto_pulang' => $path,
            'lat_pulang' => $lat,
            'lng_pulang' => $lng,
        ];
        if ($isModel && ! $ada->mahasiswa_id) {
            $dataUpdate['mahasiswa_id'] = $mahasiswa->id;
        }

        $ada->update($dataUpdate);

        return $ada;
    }

    /* ------------------------------------------------------------------ */
    /*  Riwayat & rekap pegawai                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Status seorang pegawai pada suatu tanggal.
     * hadir | terlambat | tidak_hadir | libur | belum (hari ini belum absen)
     * Mengembalikan null bila tanggal belum tiba atau pegawai belum terdaftar.
     */
    public function statusHari(Pegawai $pegawai, Carbon $tgl, ?Absensi $absen): ?string
    {
        if ($absen && $absen->jam_masuk) {
            return $absen->status;
        }
        if ($absen && $absen->status === 'tidak_hadir') {
            return 'tidak_hadir'; // ditetapkan manual oleh admin
        }
        if ($this->keteranganLibur($tgl)) {
            return 'libur';
        }
        if ($tgl->isFuture() && ! $tgl->isToday()) {
            return null;
        }
        if ($tgl->isToday()) {
            return 'belum';
        }
        if ($pegawai->created_at && $tgl->lt($pegawai->created_at->copy()->startOfDay())) {
            return null;
        }

        return 'tidak_hadir';
    }

    /**
     * Baris harian untuk seorang pegawai pada rentang tanggal (terbaru dulu).
     *
     * @return array<int,array{tanggal:Carbon,status:string,keterangan:?string,absensi:?Absensi}>
     */
    public function baris(Pegawai $pegawai, Carbon $dari, Carbon $sampai): array
    {
        $sampai = $sampai->copy()->min(Carbon::today());

        $absensi = Absensi::where('pegawai_id', $pegawai->id)
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->get()
            ->keyBy(fn ($a) => $a->tanggal->toDateString());

        $hasil = [];
        for ($d = $sampai->copy(); $d->gte($dari); $d->subDay()) {
            $a = $absensi->get($d->toDateString());
            $status = $this->statusHari($pegawai, $d, $a);
            if ($status === null) {
                continue;
            }

            $hasil[] = [
                'tanggal' => $d->copy(),
                'status' => $status,
                'keterangan' => $this->keteranganLibur($d),
                'absensi' => $a,
            ];
        }

        return $hasil;
    }
}
