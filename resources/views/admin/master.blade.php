@extends('layouts.admin')

@section('title', 'Kelola Master Data')

@section('content')
<div class="space-y-8" x-data="{ tab: 'unit' }">
    
    <!-- Top Header -->
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Kelola Master Data</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola daftar Unit Kerja Pegawai, Program Studi (Prodi), dan UT Daerah untuk Mahasiswa.</p>
    </div>

    <!-- Category Tabs -->
    <div class="flex border-b border-slate-200 space-x-6 text-sm font-bold">
        <button type="button" @click="tab = 'unit'" :class="tab === 'unit' ? 'border-brand-600 text-brand-600 border-b-2 pb-3' : 'text-slate-500 hover:text-slate-800 pb-3'">
            Unit Kerja Pegawai
        </button>
        <button type="button" @click="tab = 'prodi'" :class="tab === 'prodi' ? 'border-brand-600 text-brand-600 border-b-2 pb-3' : 'text-slate-500 hover:text-slate-800 pb-3'">
            Program Studi (Prodi)
        </button>
        <button type="button" @click="tab = 'daerah'" :class="tab === 'daerah' ? 'border-brand-600 text-brand-600 border-b-2 pb-3' : 'text-slate-500 hover:text-slate-800 pb-3'">
            UT Daerah
        </button>
    </div>

    @foreach($daftar as $kode => $m)
        <div x-show="tab === '{{ $kode }}'" class="space-y-6">
            
            <!-- Form Tambah Baru -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm max-w-xl">
                <h3 class="text-sm font-extrabold text-slate-900 mb-4">Tambah {{ $m['label'] }} Baru</h3>
                <form action="{{ route('admin.master.simpan', $kode) }}" method="POST" class="flex gap-3">
                    @csrf
                    <input type="text" name="nama" placeholder="Nama {{ $m['label'] }} baru..." class="flex-grow px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500" required>
                    <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-2xl shadow-sm shrink-0">
                        Tambah {{ $m['label'] }}
                    </button>
                </form>
            </div>

            <!-- Table List -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden max-w-2xl">
                <table class="w-full text-left text-xs divide-y divide-slate-100">
                    <thead class="bg-slate-50 font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4">#</th>
                            <th class="px-6 py-4">Nama {{ $m['label'] }}</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($m['item'] as $i => $row)
                            <tr class="hover:bg-slate-50/60 transition" x-data="{ editMode: false, namaEdit: '{{ $row->nama }}' }">
                                <td class="px-6 py-4 text-slate-400 font-mono">{{ $i + 1 }}</td>
                                
                                <td class="px-6 py-4 font-bold text-slate-900">
                                    <span x-show="!editMode">{{ $row->nama }}</span>
                                    <form x-show="editMode" action="{{ route('admin.master.ubah', [$kode, $row->id]) }}" method="POST" class="flex gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="nama" x-model="namaEdit" class="px-3 py-1 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold" required>
                                        <button type="submit" class="px-3 py-1 bg-emerald-600 text-white rounded-xl font-bold">Simpan</button>
                                        <button type="button" @click="editMode = false" class="px-3 py-1 bg-slate-200 text-slate-700 rounded-xl font-bold">Batal</button>
                                    </form>
                                </td>

                                <td class="px-6 py-4 text-right space-x-2">
                                    <button type="button" @click="editMode = !editMode" class="p-2 text-slate-400 hover:text-brand-600 rounded-xl hover:bg-brand-50 transition" title="Edit">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    
                                    <form action="{{ route('admin.master.hapus', [$kode, $row->id]) }}" method="POST" class="inline" onsubmit="return confirm('Hapus {{ $m['label'] }} {{ $row->nama }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition" title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-slate-400">Belum ada data {{ $m['label'] }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    @endforeach

</div>
@endsection
