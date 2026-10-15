<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PegawaiAuthController extends Controller
{
    private const MAKS_GAGAL = 5;
    private const MENIT_KUNCI = 5;

    public function form()
    {
        // Pegawai yang sudah masuk langsung diarahkan
        if (session('pegawai_id') && Pegawai::where('id', session('pegawai_id'))->where('aktif', true)->exists()) {
            return redirect()->route('absen');
        }

        return view('pegawai.masuk');
    }

    /** Pencarian nama atau NIP (autocomplete). Tidak pernah membocorkan status admin. */
    public function cari(Request $request)
    {
        $q = trim((string) $request->query('q'));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $cleanQ = str_replace(['%', '_'], ['\\%', '\\_'], $q);

        $hasil = Pegawai::with('unit')
            ->where('aktif', true)
            ->where(function ($query) use ($cleanQ) {
                $query->where('nama', 'like', '%'.$cleanQ.'%')
                      ->orWhere('nip', 'like', '%'.$cleanQ.'%');
            })
            ->orderBy('nama')
            ->limit(15)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nama' => $p->nama,
                'nip' => $p->nip,
                'jabatan' => $p->jabatan,
                'unit' => $p->unit->nama ?? '-',
            ]);

        return response()->json($hasil);
    }

    public function masuk(Request $request)
    {
        $data = $request->validate(['pegawai_id' => 'required|integer']);

        $pegawai = Pegawai::where('aktif', true)->find($data['pegawai_id']);
        if (! $pegawai) {
            return back()->with('galat', 'Nama pegawai tidak ditemukan. Silakan cari dan pilih nama dari daftar.');
        }

        $request->session()->regenerate();
        session(['pegawai_id' => $pegawai->id]);
        session()->forget('admin_ok');

        return $pegawai->is_admin
            ? redirect()->route('pegawai.pin')
            : redirect()->route('absen');
    }

    public function pinForm()
    {
        $pegawai = $this->pegawaiSesi();
        if (! $pegawai) {
            return redirect()->route('pegawai.masuk');
        }
        if (! $pegawai->is_admin || session('admin_ok')) {
            return redirect()->route('absen');
        }

        return view('pegawai.pin', ['pegawai' => $pegawai]);
    }

    public function pinCek(Request $request)
    {
        $pegawai = $this->pegawaiSesi();
        if (! $pegawai || ! $pegawai->is_admin) {
            return redirect()->route('pegawai.masuk');
        }

        $request->validate(['pin' => 'required|string|max:20']);

        if ($pegawai->terkunci()) {
            $sisa = now()->diffInMinutes($pegawai->terkunci_sampai, false);

            return back()->with('galat', 'Terlalu banyak percobaan. Coba lagi dalam '.max(1, (int) ceil($sisa)).' menit.');
        }

        if (! $pegawai->pin || ! Hash::check($request->pin, $pegawai->pin)) {
            $gagal = $pegawai->pin_gagal + 1;
            $update = ['pin_gagal' => $gagal];

            if ($gagal >= self::MAKS_GAGAL) {
                $update = ['pin_gagal' => 0, 'terkunci_sampai' => now()->addMinutes(self::MENIT_KUNCI)];
                LogAktivitas::catat('PIN admin terkunci', 'Akun '.$pegawai->nama.' terkunci '.self::MENIT_KUNCI.' menit karena salah PIN berulang.');
            }
            $pegawai->update($update);

            return back()->with('galat', $gagal >= self::MAKS_GAGAL
                ? 'PIN salah '.self::MAKS_GAGAL.' kali. Akun dikunci '.self::MENIT_KUNCI.' menit.'
                : 'PIN salah. Sisa percobaan: '.(self::MAKS_GAGAL - $gagal).'.');
        }

        $pegawai->update(['pin_gagal' => 0, 'terkunci_sampai' => null]);
        $request->session()->regenerate();
        session(['admin_ok' => true, 'pegawai_id' => $pegawai->id]);

        return redirect()->route('admin.dashboard');
    }

    public function keluar(Request $request)
    {
        $request->session()->flush();
        $request->session()->regenerateToken();

        return redirect()->route('beranda');
    }

    private function pegawaiSesi(): ?Pegawai
    {
        return session('pegawai_id') ? Pegawai::where('aktif', true)->find(session('pegawai_id')) : null;
    }
}
