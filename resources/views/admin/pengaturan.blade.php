@extends('layouts.admin')

@section('title', 'Pengaturan Sistem & PIN Admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Pengaturan Sistem & PIN Admin</h1>
        <p class="text-xs text-slate-500 mt-1">Konfigurasi nama institusi, logo, jam masuk/pulang, toleransi keterlambatan, dan ganti PIN Admin.</p>
    </div>

    <!-- General Settings Form -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xl">
        <h2 class="text-base font-extrabold text-slate-900 mb-6 flex items-center space-x-2">
            <i data-lucide="sliders" class="w-5 h-5 text-brand-600"></i>
            <span>Pengaturan Institusi & Jam Kerja</span>
        </h2>

        <form action="{{ route('admin.pengaturan.simpan') }}" method="POST" enctype="multipart/form-data" class="space-y-6 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Institusi / Organisasi <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_institusi" value="{{ old('nama_institusi', $p['nama_institusi']) }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-2xl font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500" required>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Logo Institusi (PNG/JPG)</label>
                    <div class="flex items-center space-x-4">
                        @if($p['logo'])
                            <img src="{{ asset($p['logo']) }}" alt="Logo" class="w-12 h-12 object-contain bg-slate-100 rounded-xl p-1 border">
                        @endif
                        <input type="file" name="logo" accept="image/*" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-2xl">
                    </div>
                </div>

            </div>

            <div class="pt-4 border-t border-slate-100 grid grid-cols-1 md:grid-cols-3 gap-4">
                
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jam Masuk Absen <span class="text-rose-500">*</span></label>
                    <input type="time" name="jam_masuk" value="{{ old('jam_masuk', $p['jam_masuk']) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-mono font-bold" required>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jam Pulang Absen <span class="text-rose-500">*</span></label>
                    <input type="time" name="jam_pulang" value="{{ old('jam_pulang', $p['jam_pulang']) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-mono font-bold" required>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Toleransi Terlambat (Menit) <span class="text-rose-500">*</span></label>
                    <input type="number" name="toleransi_menit" value="{{ old('toleransi_menit', $p['toleransi_menit']) }}" min="0" max="240" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-mono font-bold" required>
                </div>

            </div>

            <div class="pt-4 border-t border-slate-100 space-y-3">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="libur_sabtu" value="1" {{ $p['libur_sabtu'] === '1' ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded">
                    <span class="font-bold text-slate-800">Tetapkan Hari Sabtu Sebagai Libur Rutin</span>
                </label>

                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="libur_minggu" value="1" {{ $p['libur_minggu'] === '1' ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded">
                    <span class="font-bold text-slate-800">Tetapkan Hari Minggu Sebagai Libur Rutin</span>
                </label>

                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="gps_wajib" value="1" {{ $p['gps_wajib'] === '1' ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded">
                    <span class="font-bold text-slate-800">Wajibkan GPS & Validasi Radius Presensi</span>
                </label>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-2xl shadow-lg shadow-brand-600/30">
                    Simpan Pengaturan Sistem
                </button>
            </div>
        </form>
    </div>

    <!-- Ubah PIN Admin Form -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xl">
        <h2 class="text-base font-extrabold text-slate-900 mb-6 flex items-center space-x-2">
            <i data-lucide="key-round" class="w-5 h-5 text-amber-600"></i>
            <span>Ganti PIN Keamanan Admin Anda</span>
        </h2>

        <form action="{{ route('admin.pengaturan.pin') }}" method="POST" class="space-y-4 max-w-md text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">PIN Lama <span class="text-rose-500">*</span></label>
                <input type="password" name="pin_lama" maxlength="8" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-mono font-bold tracking-widest" required>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">PIN Baru (4 - 8 angka) <span class="text-rose-500">*</span></label>
                <input type="password" name="pin_baru" maxlength="8" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-mono font-bold tracking-widest" required>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi PIN Baru <span class="text-rose-500">*</span></label>
                <input type="password" name="pin_baru_confirmation" maxlength="8" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-mono font-bold tracking-widest" required>
            </div>

            <div class="pt-4">
                <button type="submit" class="px-8 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-2xl shadow-lg shadow-slate-900/20">
                    Ubah PIN Admin
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
