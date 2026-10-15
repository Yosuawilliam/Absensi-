@extends('layouts.admin')

@section('title', $absen->exists ? 'Koreksi Data Absensi' : 'Tambah Absensi Manual')

@section('content')
<div class="max-w-2xl mx-auto space-y-8" x-data="{ tipe: '{{ old('tipe', $absen->tipe ?? 'pegawai') }}' }">
    
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">{{ $absen->exists ? 'Koreksi Data Absensi' : 'Tambah Absensi Manual' }}</h1>
        <p class="text-xs text-slate-500 mt-1">Seluruh perubahan yang dilakukan wajib menyertakan alasan koreksi secara jelas.</p>
    </div>

    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xl">
        <form action="{{ $absen->exists ? route('admin.koreksi.perbarui', $absen) : route('admin.koreksi.simpan') }}" method="POST" class="space-y-6 text-xs">
            @csrf
            @if($absen->exists)
                @method('PUT')
            @endif

            <!-- Selection Tipe -->
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-2">Tipe Presensi <span class="text-rose-500">*</span></label>
                <div class="flex space-x-4">
                    <label class="flex items-center space-x-2 font-bold cursor-pointer">
                        <input type="radio" name="tipe" value="pegawai" x-model="tipe" class="text-brand-600">
                        <span>Pegawai</span>
                    </label>
                    <label class="flex items-center space-x-2 font-bold cursor-pointer">
                        <input type="radio" name="tipe" value="mahasiswa" x-model="tipe" class="text-brand-600">
                        <span>Mahasiswa</span>
                    </label>
                </div>
            </div>

            <!-- Tipe Pegawai Selector -->
            <div x-show="tipe === 'pegawai'">
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Pegawai <span class="text-rose-500">*</span></label>
                <select name="pegawai_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-2xl font-semibold">
                    <option value="">-- Pilih Pegawai --</option>
                    @foreach($pegawais as $p)
                        <option value="{{ $p->id }}" {{ old('pegawai_id', $absen->pegawai_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }} (NIP: {{ $p->nip }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tipe Mahasiswa Fields -->
            <div x-show="tipe === 'mahasiswa'" class="space-y-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Mahasiswa <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama', $absen->nama) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">NIM <span class="text-rose-500">*</span></label>
                    <input type="text" name="nim" value="{{ old('nim', $absen->nim) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-mono font-semibold">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Prodi</label>
                        <select name="prodi_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold">
                            <option value="">-- Pilih Prodi --</option>
                            @foreach($prodis as $pr)
                                <option value="{{ $pr->id }}" {{ old('prodi_id', $absen->prodi_id) == $pr->id ? 'selected' : '' }}>{{ $pr->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">UT Daerah</label>
                        <select name="ut_daerah_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold">
                            <option value="">-- Pilih UT Daerah --</option>
                            @foreach($daerahs as $dr)
                                <option value="{{ $dr->id }}" {{ old('ut_daerah_id', $absen->ut_daerah_id) == $dr->id ? 'selected' : '' }}>{{ $dr->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Detail Presensi -->
            <div class="grid grid-cols-2 gap-3 pt-2">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Presensi <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', $absen->tanggal ? $absen->tanggal->toDateString() : today()->toDateString()) }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold" required>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Kehadiran <span class="text-rose-500">*</span></label>
                    <select name="status" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold" required>
                        <option value="hadir" {{ old('status', $absen->status) === 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="terlambat" {{ old('status', $absen->status) === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="tidak_hadir" {{ old('status', $absen->status) === 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir (Alpa)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jam Masuk (HH:MM)</label>
                    <input type="time" name="jam_masuk" value="{{ old('jam_masuk', $absen->jam_masuk ? substr($absen->jam_masuk, 0, 5) : '') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-mono font-semibold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jam Pulang (HH:MM)</label>
                    <input type="time" name="jam_pulang" value="{{ old('jam_pulang', $absen->jam_pulang ? substr($absen->jam_pulang, 0, 5) : '') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-mono font-semibold">
                </div>
            </div>

            <!-- Mandatory Reason -->
            <div class="pt-2">
                <label class="block font-bold text-rose-700 uppercase tracking-wider mb-1.5">Alasan Koreksi Manual <span class="text-rose-500">* Wajib Diisi</span></label>
                <textarea name="catatan_koreksi" rows="3" placeholder="Contoh: Lupa absen karena gangguan koneksi / Surat tugas resmi..." class="w-full p-3 bg-rose-50/50 border border-rose-200 rounded-2xl font-medium text-slate-900 focus:bg-white" required>{{ old('catatan_koreksi', $absen->catatan_koreksi) }}</textarea>
            </div>

            <div class="pt-4 flex justify-between items-center">
                <a href="{{ route('admin.koreksi.index') }}" class="px-4 py-2 text-slate-500 font-bold hover:text-slate-800">Batal</a>
                <button type="submit" class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-2xl shadow-lg shadow-brand-600/30">
                    Simpan Koreksi Absensi
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
