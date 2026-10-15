<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Twibbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TwibbonController extends Controller
{
    public function index()
    {
        return view('admin.twibbon', [
            'twibbons' => Twibbon::orderByDesc('aktif')->orderByDesc('id')->get(),
            'berlaku' => Twibbon::berlaku(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'file' => 'required|file|mimes:png,webp|max:5120',
            'tampil_mulai' => 'nullable|date',
            'tampil_selesai' => 'nullable|date|after_or_equal:tampil_mulai',
        ], [
            'file.mimes' => 'Twibbon harus berformat PNG atau WebP (disarankan PNG transparan).',
            'file.max' => 'Ukuran twibbon maksimal 5 MB.',
        ]);

        $nama = Str::uuid().'.'.$request->file('file')->getClientOriginalExtension();
        $request->file('file')->move(public_path('uploads/twibbon'), $nama);

        $t = Twibbon::create([
            'nama' => $data['nama'],
            'file' => 'uploads/twibbon/'.$nama,
            'aktif' => false,
            'tampil_mulai' => $data['tampil_mulai'] ?? null,
            'tampil_selesai' => $data['tampil_selesai'] ?? null,
        ]);

        LogAktivitas::catat('Unggah twibbon', 'Twibbon "'.$t->nama.'" diunggah.');

        return back()->with('sukses', 'Twibbon berhasil diunggah. Klik "Aktifkan" untuk memakainya.');
    }

    /** Hanya satu twibbon aktif untuk semua pengguna. */
    public function aktifkan(Twibbon $twibbon)
    {
        DB::transaction(function () use ($twibbon) {
            Twibbon::where('aktif', true)->update(['aktif' => false]);
            $twibbon->update(['aktif' => true]);
        });

        LogAktivitas::catat('Aktifkan twibbon', 'Twibbon "'.$twibbon->nama.'" diaktifkan untuk semua pengguna.');

        $berlaku = Twibbon::berlaku();
        if ($berlaku && $berlaku->id !== $twibbon->id) {
            return back()->with('peringatan', 'Twibbon "'.$twibbon->nama.'" sudah diaktifkan, tetapi twibbon berjadwal "'
                .$berlaku->nama.'" masih sedang tampil dan didahulukan. Hapus atau ubah jadwalnya agar twibbon ini terpakai.');
        }

        return back()->with('sukses', 'Twibbon "'.$twibbon->nama.'" sekarang aktif untuk semua pengguna.');
    }

    public function nonaktifkan(Twibbon $twibbon)
    {
        $twibbon->update(['aktif' => false]);
        LogAktivitas::catat('Nonaktifkan twibbon', 'Twibbon "'.$twibbon->nama.'" dinonaktifkan.');

        return back()->with('sukses', 'Twibbon dinonaktifkan.');
    }

    public function jadwal(Request $request, Twibbon $twibbon)
    {
        $data = $request->validate([
            'tampil_mulai' => 'nullable|date',
            'tampil_selesai' => 'nullable|date|after_or_equal:tampil_mulai',
        ]);

        if (empty($data['tampil_mulai'])) {
            $data['tampil_selesai'] = null;
        }

        $twibbon->update($data);
        LogAktivitas::catat('Atur jadwal twibbon', 'Jadwal twibbon "'.$twibbon->nama.'" diperbarui.');

        return back()->with('sukses', 'Jadwal twibbon disimpan.');
    }

    public function destroy(Twibbon $twibbon)
    {
        @unlink(public_path($twibbon->file));
        LogAktivitas::catat('Hapus twibbon', 'Twibbon "'.$twibbon->nama.'" dihapus.');
        $twibbon->delete();

        return back()->with('sukses', 'Twibbon dihapus.');
    }
}
