@extends('layouts.app')

@section('title', 'Riwayat Presensi Pegawai')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 sm:py-12">
    
    <!-- Top Bar Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Riwayat Presensi Saya</h1>
            <p class="text-xs text-slate-500 mt-1">Status Kehadiran Bulanan — {{ $pegawai->nama }} (NIP: {{ $pegawai->nip }})</p>
        </div>

        <!-- Month Filter -->
        <form method="GET" action="{{ route('riwayat') }}" class="flex items-center space-x-2">
            <input type="month" 
                   name="bulan" 
                   value="{{ $bulan->format('Y-m') }}" 
                   onchange="this.form.submit()"
                   class="px-4 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
        </form>
    </div>

    <!-- Summary Stats Matrix -->
    @php
        $hadir = collect($baris)->where('status', 'hadir')->count();
        $terlambat = collect($baris)->where('status', 'terlambat')->count();
        $alpa = collect($baris)->where('status', 'tidak_hadir')->count();
        $liburCount = collect($baris)->where('status', 'libur')->count();
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-4 border border-emerald-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 block mb-1">Hadir Tepat Waktu</span>
            <span class="text-2xl font-extrabold text-emerald-600 font-mono">{{ $hadir }}</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-amber-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 block mb-1">Terlambat</span>
            <span class="text-2xl font-extrabold text-amber-600 font-mono">{{ $terlambat }}</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-rose-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 block mb-1">Tidak Hadir (Alpa)</span>
            <span class="text-2xl font-extrabold text-rose-600 font-mono">{{ $alpa }}</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-sky-200/80 shadow-sm">
            <span class="text-xs font-bold text-slate-400 block mb-1">Hari Libur</span>
            <span class="text-2xl font-extrabold text-sky-600 font-mono">{{ $liburCount }}</span>
        </div>
    </div>

    <!-- Attendance History Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs divide-y divide-slate-100">
                <thead class="bg-slate-50 font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Jam Masuk</th>
                        <th class="px-6 py-4">Jam Pulang</th>
                        <th class="px-6 py-4">Foto Twibbon</th>
                        <th class="px-6 py-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($baris as $b)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 font-bold text-slate-900">
                                {{ $b['tanggal']->translatedFormat('l, d F Y') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($b['status'] === 'hadir')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Hadir</span>
                                @elseif($b['status'] === 'terlambat')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">Terlambat</span>
                                @elseif($b['status'] === 'tidak_hadir')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">Tidak Hadir</span>
                                @elseif($b['status'] === 'libur')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200">Libur</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">Belum Absen</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-slate-800">
                                {{ $b['absensi'] && $b['absensi']->jam_masuk ? substr($b['absensi']->jam_masuk, 0, 5) : '-' }}
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-slate-800">
                                {{ $b['absensi'] && $b['absensi']->jam_pulang ? substr($b['absensi']->jam_pulang, 0, 5) : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($b['absensi'] && ($b['absensi']->foto_masuk || $b['absensi']->foto_pulang))
                                    <div class="flex items-center space-x-2">
                                        @if($b['absensi']->foto_masuk)
                                            <a href="{{ asset($b['absensi']->foto_masuk) }}" download="twibbon-masuk-{{ $b['tanggal']->format('Ymd') }}.jpg" title="Unduh Foto Absen Masuk" class="group relative block w-10 h-10 rounded-lg overflow-hidden border border-slate-200 shadow-sm hover:ring-2 hover:ring-brand-500 transition">
                                                <img src="{{ asset($b['absensi']->foto_masuk) }}" alt="Foto Masuk" class="w-full h-full object-cover">
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                                    <i data-lucide="download" class="w-3.5 h-3.5 text-white"></i>
                                                </div>
                                            </a>
                                        @endif
                                        @if($b['absensi']->foto_pulang)
                                            <a href="{{ asset($b['absensi']->foto_pulang) }}" download="twibbon-pulang-{{ $b['tanggal']->format('Ymd') }}.jpg" title="Unduh Foto Absen Pulang" class="group relative block w-10 h-10 rounded-lg overflow-hidden border border-slate-200 shadow-sm hover:ring-2 hover:ring-indigo-500 transition">
                                                <img src="{{ asset($b['absensi']->foto_pulang) }}" alt="Foto Pulang" class="w-full h-full object-cover">
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                                    <i data-lucide="download" class="w-3.5 h-3.5 text-white"></i>
                                                </div>
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ $b['keterangan'] ?: ($b['absensi'] && $b['absensi']->dikoreksi ? 'Dikoreksi Admin' : '-') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium">
                                Tidak ada data presensi pada bulan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
