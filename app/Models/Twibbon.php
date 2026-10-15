<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Twibbon extends Model
{
    protected $fillable = ['nama', 'file', 'aktif', 'tampil_mulai', 'tampil_selesai'];

    protected $casts = [
        'aktif' => 'boolean',
        'tampil_mulai' => 'date',
        'tampil_selesai' => 'date',
    ];

    public function url(): string
    {
        return asset($this->file);
    }

    /**
     * Twibbon yang berlaku saat ini.
     * Twibbon berjadwal yang sedang dalam rentang tanggalnya didahulukan,
     * jika tidak ada maka dipakai twibbon yang diaktifkan manual.
     */
    public static function berlaku(): ?self
    {
        $hariIni = Carbon::today()->toDateString();

        $terjadwal = static::query()
            ->whereNotNull('tampil_mulai')
            ->whereDate('tampil_mulai', '<=', $hariIni)
            ->where(function ($q) use ($hariIni) {
                $q->whereNull('tampil_selesai')->orWhereDate('tampil_selesai', '>=', $hariIni);
            })
            ->orderByDesc('tampil_mulai')
            ->first();

        return $terjadwal ?? static::where('aktif', true)->first();
    }

    public function statusJadwal(): string
    {
        $hariIni = Carbon::today();
        if (! $this->tampil_mulai) {
            return '';
        }
        if ($hariIni->lt($this->tampil_mulai)) {
            return 'Akan tampil';
        }
        if ($this->tampil_selesai && $hariIni->gt($this->tampil_selesai)) {
            return 'Selesai';
        }

        return 'Sedang tampil';
    }
}
