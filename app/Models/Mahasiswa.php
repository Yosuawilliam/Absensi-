<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswas';

    protected $fillable = [
        'nim',
        'nama',
        'no_hp',
        'email',
        'prodi_id',
        'ut_daerah_id',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function utDaerah()
    {
        return $this->belongsTo(UtDaerah::class);
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class, 'mahasiswa_id');
    }
}
