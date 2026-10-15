@extends('layouts.admin')

@section('title', 'Galeri Foto Presensi')

@section('content')
<div class="space-y-8">
    
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Galeri Foto Presensi</h1>
        <p class="text-xs text-slate-500 mt-1">Dokumentasi foto selfie berbingkai Twibbon hasil presensi Pegawai & Mahasiswa.</p>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.galeri') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs font-semibold">
            <div>
                <label class="block text-slate-500 font-bold uppercase mb-1">Cari Nama / NIM</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik kata kunci..." class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
            </div>

            <div>
                <label class="block text-slate-500 font-bold uppercase mb-1">Tanggal Presensi</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
            </div>

            <div>
                <label class="block text-slate-500 font-bold uppercase mb-1">Tipe Pengguna</label>
                <select name="tipe" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                    <option value="">-- Semua Tipe --</option>
                    <option value="pegawai" {{ request('tipe') === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                    <option value="mahasiswa" {{ request('tipe') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                </select>
            </div>

            <div>
                <label class="block text-slate-500 font-bold uppercase mb-1">Status Kehadiran</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                    <option value="">-- Semua Status --</option>
                    <option value="hadir" {{ request('status') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="terlambat" {{ request('status') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Photos Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" x-data="{ lightboxImg: null }">
        @forelse($data as $row)
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md {{ $row->tipe === 'pegawai' ? 'bg-brand-50 text-brand-700' : 'bg-indigo-50 text-indigo-700' }}">
                            {{ $row->tipe }}
                        </span>
                        <span class="text-[11px] font-bold text-slate-500 font-mono">{{ $row->tanggal->format('d/m/Y') }}</span>
                    </div>

                    <h3 class="text-sm font-extrabold text-slate-900 leading-tight truncate">{{ $row->namaTampil() }}</h3>

                    <!-- Photos Thumbnails -->
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        @if($row->foto_masuk)
                            <div class="relative aspect-square rounded-2xl overflow-hidden bg-slate-900 border border-slate-200 cursor-pointer group" @click="lightboxImg = '{{ asset($row->foto_masuk) }}'">
                                <img src="{{ asset($row->foto_masuk) }}" alt="Foto Masuk" class="w-full h-full object-cover group-hover:scale-105 transition">
                                <span class="absolute bottom-1 left-1 right-1 bg-slate-900/80 backdrop-blur-sm text-white text-[9px] font-bold py-0.5 px-1 rounded text-center">
                                    Masuk {{ substr($row->jam_masuk, 0, 5) }}
                                </span>
                            </div>
                        @endif

                        @if($row->foto_pulang)
                            <div class="relative aspect-square rounded-2xl overflow-hidden bg-slate-900 border border-slate-200 cursor-pointer group" @click="lightboxImg = '{{ asset($row->foto_pulang) }}'">
                                <img src="{{ asset($row->foto_pulang) }}" alt="Foto Pulang" class="w-full h-full object-cover group-hover:scale-105 transition">
                                <span class="absolute bottom-1 left-1 right-1 bg-slate-900/80 backdrop-blur-sm text-white text-[9px] font-bold py-0.5 px-1 rounded text-center">
                                    Pulang {{ substr($row->jam_pulang, 0, 5) }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-600">Status:</span>
                    @if($row->status === 'hadir')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Hadir</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Terlambat</span>
                    @endif
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl p-12 text-center text-slate-400">
                Tidak ada foto presensi untuk kriteria yang dipilih.
            </div>
        @endforelse
    </div>

    @if($data->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $data->links() }}
        </div>
    @endif

    <!-- Lightbox Modal with Download -->
    <div x-show="lightboxImg" 
         x-transition 
         @click.self="lightboxImg = null" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm" 
         style="display: none;">
        <div class="bg-white rounded-3xl p-6 max-w-lg w-full text-center shadow-2xl border border-slate-200">
            <div class="relative aspect-square rounded-2xl overflow-hidden mb-4 bg-slate-900">
                <img :src="lightboxImg" alt="Foto Presensi" class="w-full h-full object-cover">
            </div>
            <div class="flex items-center justify-center space-x-3">
                <a :href="lightboxImg" download="foto-twibbon-presensi.jpg" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md transition flex items-center space-x-2">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Unduh Foto Twibbon</span>
                </a>
                <button type="button" @click="lightboxImg = null" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
