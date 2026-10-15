<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturans';
    protected $primaryKey = 'kunci';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['kunci', 'nilai'];

    public const DEFAULT = [
        'nama_institusi' => 'Universitas Terbuka',
        'logo' => '',
        'jam_masuk' => '08:00',
        'jam_pulang' => '16:00',
        'toleransi_menit' => '15',
        'libur_sabtu' => '1',
        'libur_minggu' => '1',
        'gps_wajib' => '1',
    ];

    public static function ambil(string $kunci, $default = null)
    {
        $row = static::find($kunci);
        if ($row && $row->nilai !== null) {
            return $row->nilai;
        }

        return $default ?? (self::DEFAULT[$kunci] ?? null);
    }

    public static function simpan(string $kunci, $nilai): void
    {
        static::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
    }

    public static function namaInstitusi(): string
    {
        return static::ambil('nama_institusi', 'Universitas Terbuka');
    }

    public static function logoUrl(): ?string
    {
        $logo = static::ambil('logo');
        return !empty($logo) ? $logo : null;
    }
}
