<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensis';

    protected $fillable = [
        'tipe', 'pegawai_id', 'mahasiswa_id', 'tanggal', 'nama', 'nim', 'no_hp', 'email',
        'prodi_id', 'ut_daerah_id', 'jam_masuk', 'jam_pulang', 'status',
        'foto_masuk', 'foto_pulang', 'lat_masuk', 'lng_masuk',
        'lat_pulang', 'lng_pulang', 'lokasi_id', 'dikoreksi', 'catatan_koreksi',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'dikoreksi' => 'boolean',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function utDaerah()
    {
        return $this->belongsTo(UtDaerah::class);
    }

    public function namaTampil(): string
    {
        return $this->tipe === 'pegawai' ? ($this->pegawai->nama ?? '-') : ($this->nama ?? '-');
    }

    public static function labelStatus(string $status): string
    {
        return match ($status) {
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'tidak_hadir' => 'Tidak Hadir',
            'libur' => 'Libur',
            default => ucfirst($status),
        };
    }
}
