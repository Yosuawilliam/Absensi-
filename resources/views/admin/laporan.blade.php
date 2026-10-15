@extends('layouts.admin')

@section('title', 'Dashboard & Laporan Presensi')

@section('content')
<div class="space-y-8">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Dashboard & Laporan Presensi</h1>
            <p class="text-xs text-slate-500 mt-1">Rekap harian & bulanan presensi Pegawai dan Mahasiswa. Siap diunduh sebagai Excel (CSV) atau Cetak (PDF).</p>
        </div>

        <!-- Export Buttons -->
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.laporan.csv', request()->query()) }}" class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 flex items-center space-x-2 transition">
                <i data-lucide="file-spread-sheet" class="w-4 h-4"></i>
                <span>Ekspor Excel (CSV)</span>
            </a>
            <a href="{{ route('admin.laporan.cetak', request()->query()) }}" target="_blank" class="px-4 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-md flex items-center space-x-2 transition">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak / PDF</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.laporan') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 text-xs font-semibold">
            
            <div>
                <label class="block text-slate-500 font-bold uppercase mb-1">Tipe Pengguna</label>
                <select name="tipe" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                    <option value="pegawai" {{ $f['tipe'] === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                    <option value="mahasiswa" {{ $f['tipe'] === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                </select>
            </div>

            <div>
                <label class="block text-slate-500 font-bold uppercase mb-1">Periode Laporan</label>
                <select name="periode" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                    <option value="harian" {{ $f['periode'] === 'harian' ? 'selected' : '' }}>Laporan Harian</option>
                    <option value="bulanan" {{ $f['periode'] === 'bulanan' ? 'selected' : '' }}>Rekap Bulanan</option>
                </select>
            </div>

            <div>
                <label class="block text-slate-500 font-bold uppercase mb-1">Pilih Tanggal / Bulan</label>
                <input type="{{ $f['periode'] === 'bulanan' ? 'month' : 'date' }}" name="tanggal" value="{{ $f['periode'] === 'bulanan' ? $f['bulan'] : $f['tanggal'] }}" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
            </div>

            <div>
                <label class="block text-slate-500 font-bold uppercase mb-1">Status Kehadiran</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                    <option value="">-- Semua Status --</option>
                    <option value="semua_hadir" {{ ($f['status'] ?? '') === 'semua_hadir' ? 'selected' : '' }}>Hadir (Semua)</option>
                    <option value="hadir" {{ ($f['status'] ?? '') === 'hadir' ? 'selected' : '' }}>Hadir (Tepat Waktu)</option>
                    <option value="terlambat" {{ ($f['status'] ?? '') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                    <option value="tidak_hadir" {{ ($f['status'] ?? '') === 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir / Belum</option>
                </select>
            </div>

            @if($f['tipe'] === 'pegawai')
                <div>
                    <label class="block text-slate-500 font-bold uppercase mb-1">Unit Kerja</label>
                    <select name="unit" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                        <option value="">-- Semua Unit --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ $f['unit'] == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div>
                    <label class="block text-slate-500 font-bold uppercase mb-1">Prodi</label>
                    <select name="prodi" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                        <option value="">-- Semua Prodi --</option>
                        @foreach($prodis as $p)
                            <option value="{{ $p->id }}" {{ $f['prodi'] == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

        </form>
    </div>

    <!-- Summary Box -->
    <div class="bg-brand-900 rounded-3xl p-6 text-white shadow-xl flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-xs text-brand-300 font-bold uppercase tracking-wider block">Judul Laporan</span>
            <h2 class="text-lg font-bold text-white mt-0.5">{{ $lap['judul'] }}</h2>
        </div>
        
        <div class="flex items-center space-x-6 text-xs">
            @foreach($lap['ringkasan'] as $lbl => $val)
                <div class="text-center">
                    <span class="text-slate-300 block font-medium">{{ $lbl }}</span>
                    <span class="text-xl font-extrabold text-white font-mono">{{ $val }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Main Report Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs divide-y divide-slate-100">
                <thead class="bg-slate-50 font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4 w-12">No</th>
                        @foreach($lap['kolom'] as $k)
                            <th class="px-6 py-4">{{ $k }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($lap['baris'] as $idx => $b)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            @foreach($b as $colIdx => $val)
                                <td class="px-6 py-4">
                                    @if($colIdx === count($b) - 1 && $f['periode'] === 'harian')
                                        @if(str_contains(strtolower($val), 'hadir') && !str_contains(strtolower($val), 'tidak'))
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                {{ $val }}
                                            </span>
                                        @elseif(str_contains(strtolower($val), 'terlambat'))
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                {{ $val }}
                                            </span>
                                        @elseif(str_contains(strtolower($val), 'tidak hadir'))
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                {{ $val }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">
                                                {{ $val }}
                                            </span>
                                        @endif
                                    @else
                                        {{ $val }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($lap['kolom']) + 1 }}" class="px-6 py-12 text-center text-slate-400">
                                Tidak ada data untuk kriteria laporan yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
