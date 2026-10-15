<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\Pegawai;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $q = Pegawai::with('unit')->orderBy('nama');

        if ($cari = trim((string) $request->query('q'))) {
            $q->where(fn ($w) => $w->where('nama', 'like', "%{$cari}%")->orWhere('nip', 'like', "%{$cari}%"));
        }
        if ($unit = $request->query('unit')) {
            $q->where('unit_id', $unit);
        }

        return view('admin.pegawai', [
            'pegawais' => $q->paginate(15)->withQueryString(),
            'units' => Unit::orderBy('nama')->get(),
            'jumlahAdmin' => Pegawai::where('is_admin', true)->where('aktif', true)->count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['aktif'] = $request->boolean('aktif', true);
        $data['is_admin'] = $request->boolean('is_admin');

        if ($data['is_admin']) {
            $request->validate(['pin' => 'required|digits_between:4,8'], ['pin.required' => 'PIN wajib diisi untuk admin (4–8 angka).']);
            $data['pin'] = Hash::make($request->pin);
        } else {
            unset($data['pin']);
        }

        $p = Pegawai::create($data);
        LogAktivitas::catat('Tambah pegawai', 'Pegawai "'.$p->nama.'" ('.$p->nip.') ditambahkan'.($p->is_admin ? ' sebagai admin.' : '.'));

        return back()->with('sukses', 'Pegawai ditambahkan.');
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $data = $this->validasi($request, $pegawai);
        $adminBaru = $request->boolean('is_admin');
        $aktifBaru = $request->boolean('aktif');

        // Jangan sampai sistem kehilangan admin terakhir
        if ($pegawai->is_admin && $pegawai->aktif && (! $adminBaru || ! $aktifBaru) && $this->adminLain($pegawai) === 0) {
            return back()->with('galat', 'Tidak bisa mencabut/menonaktifkan admin terakhir. Jadikan pegawai lain sebagai admin terlebih dahulu.');
        }

        if ($adminBaru) {
            $perluPin = ! $pegawai->pin || $request->filled('pin');
            if ($perluPin) {
                $request->validate(['pin' => 'required|digits_between:4,8'], ['pin.required' => 'PIN wajib diisi untuk admin (4–8 angka).']);
                $data['pin'] = Hash::make($request->pin);
                $data['pin_gagal'] = 0;
                $data['terkunci_sampai'] = null;
            }
        } else {
            $data['pin'] = null;
        }

        $data['is_admin'] = $adminBaru;
        $data['aktif'] = $aktifBaru;

        if ($pegawai->is_admin !== $adminBaru) {
            LogAktivitas::catat(
                $adminBaru ? 'Beri status admin' : 'Cabut status admin',
                'Status admin untuk "'.$pegawai->nama.'" '.($adminBaru ? 'diberikan.' : 'dicabut.'),
                $pegawai->only(['id', 'nip', 'nama', 'is_admin'])
            );
        }

        $pegawai->update($data);

        return back()->with('sukses', 'Data pegawai diperbarui.');
    }

    public function destroy(Pegawai $pegawai)
    {
        if ($pegawai->id === session('pegawai_id')) {
            return back()->with('galat', 'Anda tidak dapat menghapus akun yang sedang dipakai.');
        }
        if ($pegawai->is_admin && $pegawai->aktif && $this->adminLain($pegawai) === 0) {
            return back()->with('galat', 'Tidak bisa menghapus admin terakhir.');
        }

        LogAktivitas::catat('Hapus pegawai', 'Pegawai "'.$pegawai->nama.'" ('.$pegawai->nip.') dihapus beserta riwayat absensinya.', $pegawai->only(['id', 'nip', 'nama', 'jabatan', 'is_admin']));
        $pegawai->delete();

        return back()->with('sukses', 'Pegawai dihapus.');
    }

    /* ----------------------------- Impor CSV ----------------------------- */

    public function templateCsv()
    {
        $isi = "\xEF\xBB\xBF"."NIP;Nama;Jabatan;Unit\r\n"
            ."198503122010011005;Budi Santoso;Staf Administrasi;Bagian Akademik\r\n"
            ."199001152015022003;Siti Rahmawati;Kepala Sub Bagian;Bagian Keuangan\r\n";

        return response($isi, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-pegawai.csv"',
        ]);
    }

    public function impor(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:4096'], [
            'file.mimes' => 'File harus berformat CSV. Dari Excel gunakan Simpan Sebagai > CSV.',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $baris1 = fgets($handle);
        rewind($handle);
        $baris1 = ltrim((string) $baris1, "\xEF\xBB\xBF");
        $pemisah = substr_count($baris1, ';') >= substr_count($baris1, ',') ? ';' : ',';

        $tambah = $ubah = $lewati = 0;
        $galat = [];
        $no = 0;

        while (($r = fgetcsv($handle, 0, $pemisah)) !== false) {
            $no++;
            if ($no === 1) {
                $r[0] = ltrim((string) $r[0], "\xEF\xBB\xBF");
                if (strtolower(trim($r[0])) === 'nip') {
                    continue; // header
                }
            }
            if (count(array_filter($r, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            [$nip, $nama, $jabatan, $unitNama] = array_pad(array_map(fn ($v) => trim((string) $v), $r), 4, '');
            if ($nip === '' || $nama === '') {
                $lewati++;
                $galat[] = "Baris {$no}: NIP atau Nama kosong.";
                continue;
            }

            $unitId = $unitNama !== '' ? Unit::firstOrCreate(['nama' => $unitNama])->id : null;

            $p = Pegawai::where('nip', $nip)->first();
            if ($p) {
                // Impor tidak pernah mengubah status admin / PIN
                $p->update(['nama' => $nama, 'jabatan' => $jabatan ?: null, 'unit_id' => $unitId]);
                $ubah++;
            } else {
                Pegawai::create(['nip' => $nip, 'nama' => $nama, 'jabatan' => $jabatan ?: null, 'unit_id' => $unitId]);
                $tambah++;
            }
        }
        fclose($handle);

        LogAktivitas::catat('Impor pegawai', "Impor CSV: {$tambah} baru, {$ubah} diperbarui, {$lewati} dilewati.");

        $pesan = "Impor selesai: {$tambah} pegawai baru, {$ubah} diperbarui, {$lewati} dilewati.";
        $res = back()->with($lewati ? 'peringatan' : 'sukses', $pesan);

        return $galat ? $res->with('detail', array_slice($galat, 0, 10)) : $res;
    }

    /* ------------------------------ Pembantu ----------------------------- */

    private function adminLain(Pegawai $pegawai): int
    {
        return Pegawai::where('is_admin', true)->where('aktif', true)->where('id', '!=', $pegawai->id)->count();
    }

    private function validasi(Request $request, ?Pegawai $pegawai = null): array
    {
        return $request->validate([
            'nip' => ['required', 'string', 'max:30', Rule::unique('pegawais', 'nip')->ignore($pegawai?->id)],
            'nama' => 'required|string|max:120',
            'jabatan' => 'nullable|string|max:120',
            'unit_id' => 'nullable|exists:units,id',
        ]);
    }
}
