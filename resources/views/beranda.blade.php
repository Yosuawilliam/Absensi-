@extends('layouts.app')

@section('title', 'Selamat Datang di Portal Absensi Online')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    
    <!-- Hero Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-brand-900 to-indigo-900 text-white p-8 sm:p-12 shadow-2xl mb-12">
        <div class="absolute -right-10 -bottom-10 w-96 h-96 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 max-w-2xl">
            <span class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-brand-500/20 text-brand-300 border border-brand-400/30 text-xs font-semibold uppercase tracking-wider mb-4">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                <span>Sistem Absensi Berbasis GPS & Twibbon</span>
            </span>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight text-white mb-4">
                Portal Absensi Online <br/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-300 via-sky-200 to-white">
                    {{ \App\Models\Pengaturan::namaInstitusi() }}
                </span>
            </h1>
            <p class="text-slate-300 text-sm sm:text-base leading-relaxed mb-8">
                Absensi mudah, cepat, dan aman untuk Pegawai dan Mahasiswa. Lengkapi kehadiran harian Anda dengan verifikasi foto selfie berbingkai Twibbon dan presisi lokasi GPS.
            </p>

            <div class="flex flex-wrap gap-4">
                <a href="{{ route('pegawai.masuk') }}" class="btn-animate btn-shimmer inline-flex items-center space-x-3 px-6 py-3.5 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm shadow-lg shadow-brand-600/30">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                    <span>Portal Absen Pegawai</span>
                </a>
                <a href="{{ session('mahasiswa_id') ? route('mahasiswa') : route('mahasiswa.masuk') }}" class="btn-animate inline-flex items-center space-x-3 px-6 py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm backdrop-blur-md border border-white/20">
                    <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                    <span>{{ session('mahasiswa_id') ? 'Portal Presensi Mahasiswa' : 'Masuk Portal Mahasiswa' }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Portal Choices Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        
        <!-- Pegawai Card -->
        <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm hover:shadow-xl transition group flex flex-col justify-between">
            <div>
                <div class="w-14 h-14 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mb-6 group-hover:scale-110 transition">
                    <i data-lucide="briefcase" class="w-7 h-7"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-3 group-hover:text-brand-600 transition">Absen Pegawai</h2>
                <p class="text-slate-600 text-sm leading-relaxed mb-6">
                    Ketik nama untuk pencarian otomatis, tampilkan data NIP, Jabatan & Unit secara instan. Pegawai biasa tanpa perlu mengingat password.
                </p>
                <ul class="space-y-2 text-xs font-medium text-slate-500 mb-8">
                    <li class="flex items-center space-x-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                        <span>Absen Masuk & Pulang Terintegrasi Jam Server</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                        <span>Selfie Frame Twibbon & Validasi Radius GPS</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                        <span>Riwayat Kehadiran (Hadir, Terlambat, Libur)</span>
                    </li>
                </ul>
            </div>
            <a href="{{ session('pegawai_id') ? route('absen') : route('pegawai.masuk') }}" class="btn-animate w-full py-3.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm text-center flex items-center justify-center space-x-2">
                <span>{{ session('pegawai_id') ? 'Lanjutkan Absen Pegawai' : 'Masuk Ke Portal Pegawai' }}</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

        <!-- Mahasiswa Card -->
        <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm hover:shadow-xl transition group flex flex-col justify-between">
            <div>
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-6 group-hover:scale-110 transition">
                    <i data-lucide="graduation-cap" class="w-7 h-7"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-3 group-hover:text-indigo-600 transition">Absen Mahasiswa</h2>
                <p class="text-slate-600 text-sm leading-relaxed mb-6">
                    Login praktis dengan NIM. Mahasiswa baru mengisi biodata sekali saja, lalu langsung terhubung ke verifikasi presensi kamera dan twibbon resmi.
                </p>
                <ul class="space-y-2 text-xs font-medium text-slate-500 mb-8">
                    <li class="flex items-center space-x-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                        <span>Login Sederhana dengan NIM Mahasiswa</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                        <span>Verifikasi Kamera Selfie & Frame Twibbon Terkunci Akun</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                        <span>Presensi Masuk, Pulang, dan Unduh Hasil Foto</span>
                    </li>
                </ul>
            </div>
            <a href="{{ session('mahasiswa_id') ? route('mahasiswa') : route('mahasiswa.masuk') }}" class="btn-animate btn-shimmer w-full py-3.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm text-center flex items-center justify-center space-x-2 shadow-md shadow-indigo-600/20">
                <span>{{ session('mahasiswa_id') ? 'Buka Verifikasi Absen & Twibbon' : 'Masuk Ke Portal Mahasiswa' }}</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

    </div>

    <!-- Active Twibbon Showcase Card -->
    @if($twibbon)
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-8 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-8 border border-slate-700">
            <div class="max-w-xl">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-semibold mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Twibbon Resmi Aktif</span>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">{{ $twibbon->nama }}</h3>
                <p class="text-slate-300 text-sm leading-relaxed mb-4">
                    Twibbon ini dipasang secara otomatis pada foto selfie setiap kali Pegawai atau Mahasiswa melakukan absen masuk dan pulang hari ini.
                </p>
                <div class="text-xs text-slate-400 font-mono">
                    Status: Berlaku untuk Seluruh Pengguna
                </div>
            </div>

            <div class="relative w-40 h-40 bg-slate-950 rounded-2xl p-2 border border-slate-700 shadow-2xl shrink-0 group">
                <img src="{{ asset($twibbon->file) }}" alt="{{ $twibbon->nama }}" class="w-full h-full object-contain rounded-xl transition group-hover:scale-105">
            </div>
        </div>
    @endif

</div>
@endsection
