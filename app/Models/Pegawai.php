<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawais';

    protected $fillable = [
        'nip', 'nama', 'jabatan', 'unit_id', 'is_admin', 'pin', 'aktif',
        'pin_gagal', 'terkunci_sampai',
    ];

    protected $hidden = ['pin'];

    protected $casts = [
        'is_admin' => 'boolean',
        'aktif' => 'boolean',
        'terkunci_sampai' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    public function terkunci(): bool
    {
        return $this->terkunci_sampai && $this->terkunci_sampai->isFuture();
    }
}
