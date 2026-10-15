@extends('layouts.app')

@section('title', 'Login Pegawai')

@section('content')
<div class="max-w-md mx-auto px-4 py-12 sm:py-16">
    <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-xl" x-data="pegawaiAuth()">
        
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 inline-flex items-center justify-center mb-3">
                <i data-lucide="user-search" class="w-6 h-6"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900">Masuk Pegawai</h1>
            <p class="text-xs text-slate-500 mt-1">Cari dan pilih nama Anda untuk melanjutkan absensi</p>
        </div>

        <form action="{{ route('pegawai.masuk.kirim') }}" method="POST" @submit="verifikasiForm">
            @csrf
            
            <!-- Hidden Selected ID -->
            <input type="hidden" name="pegawai_id" :value="terpilih ? terpilih.id : ''">

            <!-- Search Autocomplete Input -->
            <div class="mb-6 relative">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Ketik Nama atau NIP Pegawai <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" 
                           x-model="q" 
                           @input.debounce.300ms="cariNama()"
                           @focus="fokus = true"
                           placeholder="Ketik minimal 2 huruf Nama atau NIP Anda..." 
                           class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-300 rounded-2xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition"
                           required>
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                </div>

                <!-- Dropdown Autocomplete Items -->
                <div x-show="fokus && hasil.length > 0" 
                     @click.outside="fokus = false" 
                     x-transition 
                     class="absolute z-30 w-full mt-2 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden max-h-60 overflow-y-auto divide-y divide-slate-100">
                    <template x-for="item in hasil" :key="item.id">
                        <button type="button" 
                                @click="pilih(item)" 
                                class="w-full px-4 py-3 text-left hover:bg-brand-50 transition flex flex-col justify-center">
                            <span class="text-sm font-bold text-slate-900" x-text="item.nama"></span>
                            <div class="flex items-center space-x-2 text-xs text-slate-500 mt-0.5">
                                <span class="font-mono" x-text="item.nip"></span>
                                <span>•</span>
                                <span x-text="item.jabatan || 'Staf'"></span>
                                <span>•</span>
                                <span class="font-semibold text-brand-600" x-text="item.unit"></span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Card Tampilan Data Otomatis Pegawai Terpilih -->
            <div x-show="terpilih" x-transition class="mb-6 p-4 rounded-2xl bg-brand-50/60 border border-brand-200 text-slate-800">
                <div class="flex items-center justify-between mb-3 border-b border-brand-200/60 pb-2">
                    <span class="text-xs font-bold text-brand-700 uppercase tracking-wider">Data Pegawai Terkonfirmasi</span>
                    <i data-lucide="check-circle" class="w-4 h-4 text-brand-600"></i>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nama Lengkap:</span>
                        <span class="font-bold text-slate-900 text-right" x-text="terpilih?.nama"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">NIP:</span>
                        <span class="font-mono font-semibold text-slate-900 text-right" x-text="terpilih?.nip"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Jabatan:</span>
                        <span class="font-semibold text-slate-900 text-right" x-text="terpilih?.jabatan || '-'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Unit Kerja:</span>
                        <span class="font-bold text-brand-700 text-right" x-text="terpilih?.unit || '-'"></span>
                    </div>
                </div>
            </div>

            <button type="submit" 
                    :disabled="!terpilih"
                    class="w-full py-3.5 px-4 rounded-2xl bg-brand-600 hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-sm transition shadow-lg shadow-brand-600/30 flex items-center justify-center space-x-2">
                <span>Lanjutkan Ke Absensi</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

    </div>
</div>

<script>
function pegawaiAuth() {
    return {
        q: '',
        hasil: [],
        fokus: false,
        terpilih: null,

        async cariNama() {
            if (this.q.length < 2) {
                this.hasil = [];
                return;
            }
            try {
                const res = await fetch(`{{ route('pegawai.cari') }}?q=${encodeURIComponent(this.q)}`);
                this.hasil = await res.json();
                this.fokus = true;
            } catch (e) {
                console.error(e);
            }
        },

        pilih(item) {
            this.terpilih = item;
            this.q = item.nama;
            this.hasil = [];
            this.fokus = false;
        },

        verifikasiForm(e) {
            if (!this.terpilih) {
                e.preventDefault();
                alert('Silakan pilih nama pegawai dari daftar autocomplete!');
            }
        }
    }
}
</script>
@endsection
