<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\UtDaerah;
use Illuminate\Http\Request;

class MahasiswaAuthController extends Controller
{
    public function form()
    {
        if (session('mahasiswa_id') && Mahasiswa::where('id', session('mahasiswa_id'))->where('aktif', true)->exists()) {
            return redirect()->route('mahasiswa');
        }

        return view('mahasiswa.masuk', [
            'prodis' => Prodi::orderBy('nama')->get(),
            'daerahs' => UtDaerah::orderBy('nama')->get(),
        ]);
    }

    /** Cek apakah NIM sudah terdaftar di sistem */
    public function cekNim(Request $request)
    {
        $nim = trim((string) $request->query('nim'));
        if ($nim === '') {
            return response()->json(['terdaftar' => false]);
        }

        $m = Mahasiswa::with(['prodi', 'utDaerah'])
            ->where('nim', $nim)
            ->where('aktif', true)
            ->first();

        if (! $m) {
            return response()->json(['terdaftar' => false]);
        }

        return response()->json([
            'terdaftar' => true,
            'id' => $m->id,
            'nama' => $m->nama,
            'nim' => $m->nim,
            'no_hp' => $m->no_hp,
            'email' => $m->email,
            'prodi' => $m->prodi->nama ?? '-',
            'ut_daerah' => $m->utDaerah->nama ?? '-',
        ]);
    }

    /** Proses login dengan NIM atau registrasi biodata cepat */
    public function masuk(Request $request)
    {
        $nim = trim((string) $request->input('nim'));

        // Cek apakah mahasiswa dengan NIM ini sudah terdaftar
        $m = Mahasiswa::where('nim', $nim)->first();

        if ($m) {
            if (! $m->aktif) {
                return back()->with('galat', 'Akun mahasiswa dengan NIM ini sedang dinonaktifkan.');
            }

            $request->session()->regenerate();
            session(['mahasiswa_id' => $m->id]);

            return redirect()->route('mahasiswa')->with('sukses', 'Selamat datang kembali, '.$m->nama.'!');
        }

        // Jika belum terdaftar, validasi form biodata lengkap
        $data = $request->validate([
            'nim' => ['required', 'regex:/^[0-9]{6,20}$/', 'unique:mahasiswas,nim'],
            'nama' => ['required', 'string', 'max:120'],
            'no_hp' => ['required', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'email' => ['required', 'email', 'max:120'],
            'prodi_id' => ['required', 'exists:prodis,id'],
            'ut_daerah_id' => ['required', 'exists:ut_daerahs,id'],
        ], [
            'nim.regex' => 'NIM hanya boleh berisi angka (6–20 digit).',
            'nim.unique' => 'NIM ini sudah terdaftar. Silakan login langsung.',
            'no_hp.regex' => 'Nomor HP tidak valid.',
        ]);

        $m = Mahasiswa::create([
            'nim' => $data['nim'],
            'nama' => $data['nama'],
            'no_hp' => $data['no_hp'],
            'email' => $data['email'],
            'prodi_id' => $data['prodi_id'],
            'ut_daerah_id' => $data['ut_daerah_id'],
            'aktif' => true,
        ]);

        $request->session()->regenerate();
        session(['mahasiswa_id' => $m->id]);

        return redirect()->route('mahasiswa')->with('sukses', 'Data berhasil didaftarkan. Selamat datang, '.$m->nama.'!');
    }

    public function keluar(Request $request)
    {
        $request->session()->forget('mahasiswa_id');

        return redirect()->route('beranda')->with('sukses', 'Anda telah berhasil keluar.');
    }
}
