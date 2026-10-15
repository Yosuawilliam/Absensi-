<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Pegawai;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PengaturanController extends Controller
{
    public function index()
    {
        return view('admin.pengaturan', [
            'p' => collect(Pengaturan::DEFAULT)->mapWithKeys(fn ($v, $k) => [$k => Pengaturan::ambil($k)]),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_institusi' => 'required|string|max:150',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_pulang' => 'required|date_format:H:i|after:jam_masuk',
            'toleransi_menit' => 'required|integer|min:0|max:240',
        ], ['jam_pulang.after' => 'Jam pulang harus setelah jam masuk.']);

        Pengaturan::simpan('nama_institusi', $data['nama_institusi']);
        Pengaturan::simpan('jam_masuk', $data['jam_masuk']);
        Pengaturan::simpan('jam_pulang', $data['jam_pulang']);
        Pengaturan::simpan('toleransi_menit', $data['toleransi_menit']);
        Pengaturan::simpan('libur_sabtu', $request->boolean('libur_sabtu') ? '1' : '0');
        Pengaturan::simpan('libur_minggu', $request->boolean('libur_minggu') ? '1' : '0');
        Pengaturan::simpan('gps_wajib', $request->boolean('gps_wajib') ? '1' : '0');

        if ($request->hasFile('logo')) {
            $lama = Pengaturan::ambil('logo');
            $nama = Str::uuid().'.'.$request->file('logo')->getClientOriginalExtension();
            $request->file('logo')->move(public_path('uploads/logo'), $nama);
            Pengaturan::simpan('logo', 'uploads/logo/'.$nama);
            if ($lama) {
                @unlink(public_path($lama));
            }
        }

        LogAktivitas::catat('Ubah pengaturan', 'Pengaturan institusi dan jam absen diperbarui.');

        return back()->with('sukses', 'Pengaturan disimpan.');
    }

    public function hapusLogo()
    {
        if ($lama = Pengaturan::ambil('logo')) {
            @unlink(public_path($lama));
        }
        Pengaturan::simpan('logo', '');

        return back()->with('sukses', 'Logo dihapus.');
    }

    /** Admin mengganti PIN-nya sendiri. */
    public function ubahPin(Request $request)
    {
        $request->validate([
            'pin_lama' => 'required',
            'pin_baru' => 'required|digits_between:4,8|confirmed',
        ], [
            'pin_baru.digits_between' => 'PIN baru harus 4–8 angka.',
            'pin_baru.confirmed' => 'Konfirmasi PIN baru tidak sama.',
        ]);

        $pegawai = Pegawai::findOrFail(session('pegawai_id'));
        if (! Hash::check($request->pin_lama, $pegawai->pin)) {
            return back()->withErrors(['pin_lama' => 'PIN lama salah.'])->withInput();
        }

        $pegawai->update(['pin' => Hash::make($request->pin_baru)]);
        LogAktivitas::catat('Ubah PIN admin', 'PIN milik "'.$pegawai->nama.'" diganti.');

        return back()->with('sukses', 'PIN berhasil diganti.');
    }
}
