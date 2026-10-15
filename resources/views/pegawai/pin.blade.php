@extends('layouts.app')

@section('title', 'Verifikasi PIN Admin')

@section('content')
<div class="max-w-md mx-auto px-4 py-12 sm:py-16">
    <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-xl">
        
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 inline-flex items-center justify-center mb-3">
                <i data-lucide="lock" class="w-6 h-6"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900">Verifikasi PIN Admin</h1>
            <p class="text-xs text-slate-500 mt-1">Halo <span class="font-bold text-slate-800">{{ $pegawai->nama }}</span>, masukkan PIN Admin Anda</p>
        </div>

        @if($pegawai->terkunci())
            @php $sisa = max(1, (int) ceil(now()->diffInMinutes($pegawai->terkunci_sampai, false))); @endphp
            <div class="p-4 mb-6 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center space-x-3">
                <i data-lucide="shield-alert" class="w-5 h-5 text-rose-600 shrink-0"></i>
                <span>Akun Anda sementara dikunci karena salah PIN 5 kali. Silakan tunggu {{ $sisa }} menit lagi.</span>
            </div>
        @endif

        <form action="{{ route('pegawai.pin.cek') }}" method="POST">
            @csrf
            
            <div class="mb-6" x-data="{ show: false }">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    PIN Keamanan <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" 
                           name="pin" 
                           maxlength="8"
                           placeholder="Masukkan 4 - 8 angka PIN..." 
                           class="w-full pl-4 pr-12 py-3 bg-slate-50 border border-slate-300 rounded-2xl text-center font-mono text-xl tracking-widest focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition"
                           required autofocus
                           :disabled="{{ $pegawai->terkunci() ? 'true' : 'false' }}">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600">
                        <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-5 h-5"></i>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 text-center">Batasan: Maksimal 5x kesalahan berturut-turut sebelum akun terkunci 5 menit.</p>
            </div>

            <button type="submit" 
                    :disabled="{{ $pegawai->terkunci() ? 'true' : 'false' }}"
                    class="w-full py-3.5 px-4 rounded-2xl bg-slate-900 hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-sm transition shadow-lg shadow-slate-900/20 flex items-center justify-center space-x-2">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                <span>Verifikasi PIN & Masuk Admin</span>
            </button>
        </form>

    </div>
</div>
@endsection
