<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Prodi;
use App\Models\Unit;
use App\Models\UtDaerah;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Kelola daftar Unit (pegawai), Prodi dan UT Daerah (mahasiswa). */
class MasterController extends Controller
{
    private const JENIS = [
        'unit' => ['model' => Unit::class, 'tabel' => 'units', 'label' => 'Unit'],
        'prodi' => ['model' => Prodi::class, 'tabel' => 'prodis', 'label' => 'Prodi'],
        'daerah' => ['model' => UtDaerah::class, 'tabel' => 'ut_daerahs', 'label' => 'UT Daerah'],
    ];

    public function index()
    {
        return view('admin.master', [
            'daftar' => collect(self::JENIS)->map(fn ($j, $kode) => [
                'kode' => $kode,
                'label' => $j['label'],
                'item' => $j['model']::orderBy('nama')->get(),
            ]),
        ]);
    }

    public function store(Request $request, string $jenis)
    {
        $j = $this->jenis($jenis);
        $data = $request->validate(['nama' => ['required', 'string', 'max:150', Rule::unique($j['tabel'], 'nama')]], [
            'nama.unique' => $j['label'].' tersebut sudah ada.',
        ]);

        $j['model']::create($data);
        LogAktivitas::catat('Tambah '.$j['label'], '"'.$data['nama'].'" ditambahkan.');

        return back()->with('sukses', $j['label'].' ditambahkan.');
    }

    public function update(Request $request, string $jenis, int $id)
    {
        $j = $this->jenis($jenis);
        $row = $j['model']::findOrFail($id);
        $data = $request->validate(['nama' => ['required', 'string', 'max:150', Rule::unique($j['tabel'], 'nama')->ignore($id)]], [
            'nama.unique' => $j['label'].' tersebut sudah ada.',
        ]);

        $row->update($data);
        LogAktivitas::catat('Ubah '.$j['label'], 'Nama diubah menjadi "'.$data['nama'].'".');

        return back()->with('sukses', $j['label'].' diperbarui.');
    }

    public function destroy(string $jenis, int $id)
    {
        $j = $this->jenis($jenis);
        $row = $j['model']::findOrFail($id);
        LogAktivitas::catat('Hapus '.$j['label'], '"'.$row->nama.'" dihapus.');
        $row->delete();

        return back()->with('sukses', $j['label'].' dihapus.');
    }

    private function jenis(string $jenis): array
    {
        abort_unless(isset(self::JENIS[$jenis]), 404);

        return self::JENIS[$jenis];
    }
}
