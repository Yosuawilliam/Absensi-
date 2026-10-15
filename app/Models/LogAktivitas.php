<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';

    protected $fillable = ['pegawai_id', 'pelaku', 'aksi', 'keterangan', 'data_lama'];

    protected $casts = ['data_lama' => 'array'];

    public static function catat(string $aksi, ?string $keterangan = null, ?array $dataLama = null): void
    {
        $pegawaiId = session('pegawai_id');
        $pelaku = $pegawaiId ? (Pegawai::find($pegawaiId)->nama ?? null) : null;

        static::create([
            'pegawai_id' => $pegawaiId,
            'pelaku' => $pelaku,
            'aksi' => $aksi,
            'keterangan' => $keterangan,
            'data_lama' => $dataLama,
        ]);
    }
}
