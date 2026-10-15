@extends('layouts.admin')

@section('title', 'Kelola Twibbon')

@section('content')
<div class="space-y-8" x-data="twibbonAdminApp()">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Kelola Twibbon Presensi</h1>
            <p class="text-xs text-slate-500 mt-1">Unggah PNG transparan. Hanya 1 twibbon yang aktif berlaku untuk semua pengguna.</p>
        </div>

        <button type="button" @click="modalUnggah = true" class="px-5 py-2.5 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-lg shadow-brand-600/30 flex items-center space-x-2 transition">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Unggah Twibbon Baru</span>
        </button>
    </div>

    <!-- Active Twibbon Banner -->
    @if($berlaku)
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-6 text-white border border-slate-700 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center space-x-4">
                <div class="w-20 h-20 bg-slate-950 rounded-2xl p-2 border border-slate-700 shrink-0">
                    <img src="{{ asset($berlaku->file) }}" alt="{{ $berlaku->nama }}" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold uppercase mb-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping mr-1"></span>
                        Twibbon Berjadwal/Aktif Berjalan
                    </span>
                    <h2 class="text-xl font-bold text-white">{{ $berlaku->nama }}</h2>
                    <p class="text-xs text-slate-300 mt-1">
                        @if($berlaku->tampil_mulai)
                            Jadwal: {{ $berlaku->tampil_mulai->format('d/m/Y') }} s.d. {{ $berlaku->tampil_selesai ? $berlaku->tampil_selesai->format('d/m/Y') : 'Seterusnya' }}
                        @else
                            Diaktifkan Secara Manual (Tanpa Jadwal Tanggal)
                        @endif
                    </p>
                </div>
            </div>
            
            @if($berlaku->aktif)
                <form action="{{ route('admin.twibbon.nonaktifkan', $berlaku) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-200 text-xs font-bold transition">
                        Nonaktifkan Twibbon Ini
                    </button>
                </form>
            @endif
        </div>
    @endif

    <!-- Twibbon List Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        @forelse($twibbons as $t)
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                
                <div class="p-6 text-center border-b border-slate-100">
                    <!-- Preview Box -->
                    <div class="relative w-48 h-48 mx-auto bg-slate-900 rounded-2xl p-2 border border-slate-200 shadow-inner mb-4 flex items-center justify-center">
                        <img src="{{ asset($t->file) }}" alt="{{ $t->nama }}" class="w-full h-full object-contain">
                        @if($t->aktif)
                            <span class="absolute top-2 right-2 px-2.5 py-1 rounded-full bg-emerald-500 text-white font-bold text-[10px] shadow-md">
                                AKTIF
                            </span>
                        @endif
                    </div>
                    
                    <h3 class="text-sm font-extrabold text-slate-900 mb-1">{{ $t->nama }}</h3>
                    <p class="text-xs text-slate-400 font-medium">
                        @if($t->tampil_mulai)
                            Jadwal: {{ $t->tampil_mulai->format('d/m/Y') }} - {{ $t->tampil_selesai ? $t->tampil_selesai->format('d/m/Y') : 'Selamanya' }}
                        @else
                            Tanpa Jadwal
                        @endif
                    </p>
                </div>

                <div class="p-4 bg-slate-50 flex items-center justify-between gap-2">
                    @if(!$t->aktif)
                        <form action="{{ route('admin.twibbon.aktifkan', $t) }}" method="POST" class="flex-grow">
                            @csrf
                            <button type="submit" class="w-full py-2 px-3 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                                Aktifkan
                            </button>
                        </form>
                    @else
                        <span class="flex-grow text-center text-xs font-bold text-emerald-600 py-2">Sedang Digunakan</span>
                    @endif

                    <form action="{{ route('admin.twibbon.hapus', $t) }}" method="POST" onsubmit="return confirm('Hapus twibbon ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition" title="Hapus">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl p-12 text-center text-slate-400">
                Belum ada twibbon yang diunggah. Klik tombol di atas untuk mengunggah file PNG transparan.
            </div>
        @endforelse
    </div>

    <!-- Modal Unggah Twibbon -->
    <div x-show="modalUnggah" @click.outside="modalUnggah = false" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-base font-extrabold text-slate-900">Unggah Twibbon Transparan</h3>
                <button type="button" @click="modalUnggah = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.twibbon.simpan') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Twibbon <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" placeholder="Contoh: Twibbon HUT Institusi 2026..." class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-2xl font-semibold" required>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">File PNG Transparan / WebP <span class="text-rose-500">*</span></label>
                    <input type="file" name="file" accept="image/png,image/webp" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold" required>
                    <p class="text-[11px] text-slate-400 mt-1">Disarankan gambar berasio 1:1 (contoh: 1080x1080px) dengan latar belakang transparan.</p>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jadwal Mulai (Opsional)</label>
                        <input type="date" name="tampil_mulai" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jadwal Selesai (Opsional)</label>
                        <input type="date" name="tampil_selesai" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold">
                    </div>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <button type="button" @click="modalUnggah = false" class="px-5 py-2.5 rounded-xl text-slate-600 font-bold hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-md">Unggah & Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function twibbonAdminApp() {
    return {
        modalUnggah: false
    }
}
</script>
@endsection
