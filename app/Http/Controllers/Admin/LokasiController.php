<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Lokasi;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    public function index()
    {
        return view('admin.lokasi', ['lokasis' => Lokasi::orderBy('nama')->get()]);
    }

    public function store(Request $request)
    {
        $l = Lokasi::create($this->validasi($request) + ['aktif' => $request->boolean('aktif', true)]);
        LogAktivitas::catat('Tambah lokasi absen', '"'.$l->nama.'" radius '.$l->radius.' m.');

        return back()->with('sukses', 'Lokasi absen ditambahkan.');
    }

    public function update(Request $request, Lokasi $lokasi)
    {
        $lokasi->update($this->validasi($request) + ['aktif' => $request->boolean('aktif')]);
        LogAktivitas::catat('Ubah lokasi absen', '"'.$lokasi->nama.'" diperbarui (radius '.$lokasi->radius.' m).');

        return back()->with('sukses', 'Lokasi absen diperbarui.');
    }

    public function destroy(Lokasi $lokasi)
    {
        LogAktivitas::catat('Hapus lokasi absen', '"'.$lokasi->nama.'" dihapus.');
        $lokasi->delete();

        return back()->with('sukses', 'Lokasi absen dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama' => 'required|string|max:120',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:5000',
        ], [
            'radius.min' => 'Radius minimal 10 meter.',
            'radius.max' => 'Radius maksimal 5000 meter.',
        ]);
    }
}
