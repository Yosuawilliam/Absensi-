@extends('layouts.app')

@section('title', 'Masuk Mahasiswa')

@section('content')
<div class="max-w-xl mx-auto px-4 py-10 sm:py-16" x-data="mahasiswaLoginApp()">
    
    <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-44 h-44 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Header -->
        <div class="text-center mb-8 relative z-10">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 inline-flex items-center justify-center mb-3 shadow-md shadow-indigo-500/10">
                <i data-lucide="graduation-cap" class="w-7 h-7"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Portal Mahasiswa</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1.5">
                Masukkan NIM Anda untuk login dan mengakses verifikasi presensi kamera twibbon.
            </p>
        </div>

        <form action="{{ route('mahasiswa.masuk.kirim') }}" method="POST" id="formLoginMahasiswa">
            @csrf

            <!-- Input NIM -->
            <div class="mb-5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Nomor Induk Mahasiswa (NIM) <span class="text-rose-500">*</span>
                </label>
                <div class="relative flex">
                    <input type="text" 
                           name="nim"
                           x-model="nim" 
                           @input.debounce.400ms="cekNim()"
                           @keydown.enter.prevent="cekNim()"
                           placeholder="Masukkan 6-20 digit NIM Anda..." 
                           class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-300 rounded-2xl text-sm font-mono font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                           required
                           autofocus>
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="hash" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-1.5 px-1">
                    <span class="text-[11px] text-slate-400">Contoh: 041234567 (hanya angka)</span>
                    <button type="button" 
                            @click="cekNim()" 
                            class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 transition">
                        Periksa Status NIM
                    </button>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div x-show="loading" class="py-4 text-center text-xs text-indigo-600 font-semibold flex items-center justify-center space-x-2">
                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                <span>Memeriksa database mahasiswa...</span>
            </div>

            <!-- Kasus A: NIM Sudah Terdaftar -->
            <div x-show="terdaftar && !loading" x-transition class="mb-6 p-5 rounded-2xl bg-indigo-50/70 border border-indigo-200 text-slate-800">
                <div class="flex items-center justify-between mb-3 border-b border-indigo-200/60 pb-2">
                    <span class="text-xs font-bold text-indigo-800 uppercase tracking-wider flex items-center space-x-1.5">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                        <span>Mahasiswa Ditemukan</span>
                    </span>
                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Aktif</span>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nama Lengkap:</span>
                        <span class="font-bold text-slate-900 text-right" x-text="dataMahasiswa.nama"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">NIM:</span>
                        <span class="font-mono font-semibold text-slate-900 text-right" x-text="dataMahasiswa.nim"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Program Studi:</span>
                        <span class="font-semibold text-indigo-700 text-right" x-text="dataMahasiswa.prodi"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">UT Daerah:</span>
                        <span class="font-semibold text-slate-900 text-right" x-text="dataMahasiswa.ut_daerah"></span>
                    </div>
                </div>
            </div>

            <!-- Kasus B: NIM Belum Terdaftar -> Form Biodata Mahasiswa Baru -->
            <div x-show="belumTerdaftar && !loading" x-transition class="mb-6 space-y-4 pt-2">
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs">
                    <div class="flex items-start space-x-2">
                        <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                        <div>
                            <span class="font-bold block">NIM belum terdaftar di sistem.</span>
                            Silakan lengkapi biodata Anda di bawah ini satu kali saja untuk aktivasi akun presensi.
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="nama"
                           x-model="formBaru.nama" 
                           :required="belumTerdaftar"
                           placeholder="Nama Lengkap" 
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-2xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nomor HP / WhatsApp <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="no_hp"
                               x-model="formBaru.no_hp" 
                               :required="belumTerdaftar"
                               placeholder="08123456789..." 
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-2xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Email Aktif <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" 
                               name="email"
                               x-model="formBaru.email" 
                               :required="belumTerdaftar"
                               placeholder="nama@ecampus.ut.ac.id..." 
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-2xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Program Studi (Prodi) <span class="text-rose-500">*</span>
                    </label>
                    <select name="prodi_id" 
                            x-model="formBaru.prodi_id" 
                            :required="belumTerdaftar"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-2xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach($prodis as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        UT Daerah <span class="text-rose-500">*</span>
                    </label>
                    <select name="ut_daerah_id" 
                            x-model="formBaru.ut_daerah_id" 
                            :required="belumTerdaftar"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-2xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih UT Daerah --</option>
                        @foreach($daerahs as $d)
                            <option value="{{ $d->id }}">{{ $d->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" 
                    :disabled="nim.trim().length < 6 || loading"
                    class="w-full py-4 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-sm transition shadow-xl shadow-indigo-600/30 flex items-center justify-center space-x-2">
                <i data-lucide="camera" class="w-5 h-5"></i>
                <span x-text="belumTerdaftar ? 'Simpan Biodata & Lanjut ke Presensi' : 'Masuk ke Verifikasi Absen & Twibbon'"></span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

        <!-- Footer Link -->
        <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <a href="{{ route('beranda') }}" class="hover:text-indigo-600 transition flex items-center space-x-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Beranda</span>
            </a>
            <a href="{{ route('pegawai.masuk') }}" class="hover:text-brand-600 transition font-medium">
                Portal Pegawai &rarr;
            </a>
        </div>

    </div>
</div>

<script>
function mahasiswaLoginApp() {
    return {
        nim: '{{ old('nim', '') }}',
        loading: false,
        terdaftar: false,
        belumTerdaftar: false,
        dataMahasiswa: {},
        formBaru: {
            nama: '{{ old('nama', '') }}',
            no_hp: '{{ old('no_hp', '') }}',
            email: '{{ old('email', '') }}',
            prodi_id: '{{ old('prodi_id', '') }}',
            ut_daerah_id: '{{ old('ut_daerah_id', '') }}'
        },

        init() {
            if (this.nim.trim().length >= 6) {
                this.cekNim();
            }
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        async cekNim() {
            const cleanNim = this.nim.trim();
            if (cleanNim.length < 6) {
                this.terdaftar = false;
                this.belumTerdaftar = false;
                return;
            }

            this.loading = true;
            try {
                const res = await fetch(`{{ route('mahasiswa.cek_nim') }}?nim=${encodeURIComponent(cleanNim)}`);
                const data = await res.json();
                if (data.terdaftar) {
                    this.terdaftar = true;
                    this.belumTerdaftar = false;
                    this.dataMahasiswa = data;
                } else {
                    this.terdaftar = false;
                    this.belumTerdaftar = true;
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.loading = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        }
    }
}
</script>
@endsection
