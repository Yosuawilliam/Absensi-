@extends('layouts.admin')

@section('title', 'Data Absensi Mahasiswa')

@section('content')
<div class="space-y-8">
    
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Data Absensi Mahasiswa</h1>
        <p class="text-xs text-slate-500 mt-1">Daftar presensi mahasiswa yang telah masuk ke sistem beserta filter lengkap.</p>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.mahasiswa') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cari NIM / Nama</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik NIM atau Nama..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ request('dari') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ request('sampai') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Prodi</label>
                <select name="prodi" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold">
                    <option value="">-- Semua Prodi --</option>
                    @foreach($prodis as $p)
                        <option value="{{ $p->id }}" {{ request('prodi') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">UT Daerah</label>
                <select name="daerah" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold">
                    <option value="">-- Semua Daerah --</option>
                    @foreach($daerahs as $d)
                        <option value="{{ $d->id }}" {{ request('daerah') == $d->id ? 'selected' : '' }}>{{ $d->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-5 flex justify-end space-x-2 pt-2">
                <a href="{{ route('admin.mahasiswa') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">Reset</a>
                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm">Terapkan Filter</button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs divide-y divide-slate-100">
                <thead class="bg-slate-50 font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">NIM</th>
                        <th class="px-6 py-4">Nama Mahasiswa</th>
                        <th class="px-6 py-4">Prodi & UT Daerah</th>
                        <th class="px-6 py-4">Kontak</th>
                        <th class="px-6 py-4">Jam Masuk</th>
                        <th class="px-6 py-4">Jam Pulang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($data as $row)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $row->tanggal->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 font-mono font-bold text-indigo-700">{{ $row->nim }}</td>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $row->nama }}</td>
                            <td class="px-6 py-4">
                                <span class="block font-bold text-slate-800">{{ $row->prodi->nama ?? '-' }}</span>
                                <span class="block text-[11px] text-slate-400">{{ $row->utDaerah->nama ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                <span class="block font-mono">{{ $row->no_hp ?: '-' }}</span>
                                <span class="block text-[11px]">{{ $row->email ?: '-' }}</span>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-emerald-600">{{ $row->jam_masuk ? substr($row->jam_masuk, 0, 5) : '-' }}</td>
                            <td class="px-6 py-4 font-mono font-bold text-indigo-600">{{ $row->jam_pulang ? substr($row->jam_pulang, 0, 5) : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">Tidak ada data absensi mahasiswa.</td>
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
