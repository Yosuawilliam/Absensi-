@extends('layouts.admin')

@section('title', 'Koreksi Absensi Manual')

@section('content')
<div class="space-y-8">
    
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Koreksi Absensi Manual</h1>
            <p class="text-xs text-slate-500 mt-1">Ubah atau tambah presensi dengan alasan wajib. Data asli akan selalu tercatat di Log Aktivitas.</p>
        </div>

        <a href="{{ route('admin.koreksi.baru') }}" class="px-5 py-2.5 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-lg shadow-brand-600/30 flex items-center space-x-2 transition">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Presensi Manual</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.koreksi.index') }}" class="flex flex-wrap gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari Nama / NIP / NIM..." class="px-4 py-2 bg-slate-50 border border-slate-300 rounded-2xl text-xs font-semibold">
            <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="px-4 py-2 bg-slate-50 border border-slate-300 rounded-2xl text-xs font-semibold">
            <select name="tipe" class="px-4 py-2 bg-slate-50 border border-slate-300 rounded-2xl text-xs font-semibold">
                <option value="">-- Semua Tipe --</option>
                <option value="pegawai" {{ request('tipe') === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                <option value="mahasiswa" {{ request('tipe') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
            </select>
            <button type="submit" class="px-5 py-2 bg-slate-900 text-white font-bold text-xs rounded-2xl">Filter</button>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs divide-y divide-slate-100">
                <thead class="bg-slate-50 font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Tipe</th>
                        <th class="px-6 py-4">Pengguna</th>
                        <th class="px-6 py-4">Jam Masuk</th>
                        <th class="px-6 py-4">Jam Pulang</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Status Koreksi</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($data as $row)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $row->tanggal->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 uppercase text-[10px] font-extrabold">{{ $row->tipe }}</td>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $row->namaTampil() }}</td>
                            <td class="px-6 py-4 font-mono font-bold">{{ $row->jam_masuk ? substr($row->jam_masuk, 0, 5) : '-' }}</td>
                            <td class="px-6 py-4 font-mono font-bold">{{ $row->jam_pulang ? substr($row->jam_pulang, 0, 5) : '-' }}</td>
                            <td class="px-6 py-4 font-bold">
                                {{ \App\Models\Absensi::labelStatus($row->status) }}
                            </td>
                            <td class="px-6 py-4">
                                @if($row->dikoreksi)
                                    <span class="px-2.5 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[11px] font-bold" title="Alasan: {{ $row->catatan_koreksi }}">
                                        Dikoreksi Manual
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px]">Sistem Asli</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.koreksi.ubah', $row) }}" class="p-2 text-slate-400 hover:text-brand-600 rounded-xl hover:bg-brand-50 inline-block" title="Koreksi">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">Belum ada data presensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($data->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $data->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
