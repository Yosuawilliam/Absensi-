@extends('layouts.admin')

@section('title', 'Kalender Hari Libur')

@section('content')
<div class="space-y-8" x-data="{ modalTambah: false }">
    
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Kalender Hari Libur</h1>
            <p class="text-xs text-slate-500 mt-1">Tanggal libur tidak dihitung sebagai Alpa / Tidak Hadir untuk seluruh pegawai.</p>
        </div>

        <div class="flex items-center space-x-3">
            <form method="GET" action="{{ route('admin.libur') }}">
                <select name="tahun" onchange="this.form.submit()" class="px-4 py-2 bg-white border border-slate-300 rounded-2xl text-xs font-bold shadow-sm">
                    @foreach($daftarTahun as $t)
                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>Tahun {{ $t }}</option>
                    @endforeach
                </select>
            </form>

            <button type="button" @click="modalTambah = true" class="px-5 py-2.5 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-lg shadow-brand-600/30 flex items-center space-x-2 transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Tambah Hari Libur</span>
            </button>
        </div>
    </div>

    <!-- Holiday List Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden max-w-3xl">
        <table class="w-full text-left text-xs divide-y divide-slate-100">
            <thead class="bg-slate-50 font-bold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-4">Tanggal Libur</th>
                    <th class="px-6 py-4">Keterangan Hari Libur</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                @forelse($libur as $l)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-6 py-4 font-bold text-slate-900 font-mono">
                            {{ $l->tanggal->translatedFormat('l, d F Y') }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-800">
                            {{ $l->keterangan }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <form action="{{ route('admin.libur.hapus', $l) }}" method="POST" onsubmit="return confirm('Hapus hari libur {{ $l->keterangan }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition" title="Hapus">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-slate-400">Belum ada hari libur nasional / khusus yang didaftarkan untuk tahun {{ $tahun }}.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal Form Tambah Hari Libur -->
    <div x-show="modalTambah" @click.outside="modalTambah = false" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-base font-extrabold text-slate-900">Tambah Hari Libur / Cuti Bersama</h3>
                <button type="button" @click="modalTambah = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.libur.simpan') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Mulai <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold" required>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Sampai (Opsional untuk Rentang)</label>
                    <input type="date" name="sampai" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold">
                    <p class="text-[11px] text-slate-400 mt-1">Isi tanggal sampai jika ingin memasukkan cuti bersama beberapa hari sekaligus.</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan Libur <span class="text-rose-500">*</span></label>
                    <input type="text" name="keterangan" placeholder="Contoh: Hari Raya Idul Fitri..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold" required>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <button type="button" @click="modalTambah = false" class="px-5 py-2.5 rounded-xl text-slate-600 font-bold hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-md">Simpan Hari Libur</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
