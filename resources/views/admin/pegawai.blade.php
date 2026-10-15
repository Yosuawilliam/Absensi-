@extends('layouts.admin')

@section('title', 'Kelola Data Pegawai')

@section('content')
<div class="space-y-8" x-data="pegawaiAdminApp()">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Kelola Data Pegawai</h1>
            <p class="text-xs text-slate-500 mt-1">Tambah, edit, hapus pegawai & kelola hak akses Admin (PIN hashed).</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" @click="modalImpor = true" class="px-4 py-2.5 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 font-bold text-xs flex items-center space-x-2 transition">
                <i data-lucide="file-spread-sheet" class="w-4 h-4"></i>
                <span>Impor CSV / Excel</span>
            </button>
            <button type="button" @click="bukaTambah()" class="px-5 py-2.5 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-lg shadow-brand-600/30 flex items-center space-x-2 transition">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Tambah Pegawai Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.pegawai') }}" class="w-full flex flex-col sm:flex-row gap-3">
            <div class="relative flex-grow">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari Nama atau NIP Pegawai..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3"></i>
            </div>
            
            <select name="unit" onchange="this.form.submit()" class="px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="">-- Semua Unit --</option>
                @foreach($units as $u)
                    <option value="{{ $u->id }}" {{ request('unit') == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2.5 bg-slate-900 text-white text-xs font-bold rounded-2xl">Filter</button>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs divide-y divide-slate-100">
                <thead class="bg-slate-50 font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">NIP</th>
                        <th class="px-6 py-4">Nama Lengkap</th>
                        <th class="px-6 py-4">Jabatan</th>
                        <th class="px-6 py-4">Unit Kerja</th>
                        <th class="px-6 py-4">Status Admin</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($pegawais as $p)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900">{{ $p->nip }}</td>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $p->nama }}</td>
                            <td class="px-6 py-4">{{ $p->jabatan ?: '-' }}</td>
                            <td class="px-6 py-4 font-semibold text-brand-700">{{ $p->unit->nama ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if($p->is_admin)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        Admin
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">
                                        Pegawai Biasa
                                    </span>
                                @endif

                                @if(!$p->aktif)
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200 ml-1">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button type="button" @click="bukaEdit({{ json_encode($p) }})" class="p-2 text-slate-400 hover:text-brand-600 rounded-xl hover:bg-brand-50 transition" title="Edit">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>
                                
                                <form action="{{ route('admin.pegawai.hapus', $p) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pegawai {{ $p->nama }}?')">
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
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">Tidak ada data pegawai yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($pegawais->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $pegawais->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Tambah/Edit Pegawai -->
    <div x-show="modalForm" @click.outside="modalForm = false" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-base font-extrabold text-slate-900" x-text="isEdit ? 'Ubah Data Pegawai' : 'Tambah Pegawai Baru'"></h3>
                <button type="button" @click="modalForm = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="isEdit ? `/admin/pegawai/${form.id}` : '{{ route('admin.pegawai.simpan') }}'" method="POST" class="space-y-4 text-xs">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">NIP <span class="text-rose-500">*</span></label>
                    <input type="text" name="nip" x-model="form.nip" placeholder="Contoh: 198503122010011005..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-mono font-semibold" required>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" x-model="form.nama" placeholder="Nama pegawai beserta gelar..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold" required>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jabatan</label>
                    <input type="text" name="jabatan" x-model="form.jabatan" placeholder="Contoh: Staf Keuangan..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Unit Kerja</label>
                    <select name="unit_id" x-model="form.unit_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold">
                        <option value="">-- Pilih Unit --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}">{{ $u->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Checkbox Status Aktif Pegawai -->
                <div class="pt-2 border-t border-slate-100">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="aktif" value="1" x-model="form.aktif" class="w-4 h-4 text-emerald-600 rounded">
                        <div>
                            <span class="font-bold text-slate-900 block">Status Pegawai Aktif</span>
                            <span class="text-[11px] text-slate-500">Centang agar pegawai dapat dicari dan melakukan absensi online.</span>
                        </div>
                    </label>
                </div>

                <!-- Checkbox Admin Status -->
                <div class="pt-2 border-t border-slate-100">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="is_admin" value="1" x-model="form.is_admin" class="w-4 h-4 text-brand-600 rounded">
                        <span class="font-bold text-slate-900">Beri Hak Akses Administrator</span>
                    </label>
                </div>

                <!-- PIN Admin Field (Required if admin checked) -->
                <div x-show="form.is_admin" x-transition class="p-4 rounded-2xl bg-purple-50 border border-purple-200 space-y-2">
                    <label class="block font-bold text-purple-900 uppercase tracking-wider">
                        PIN Admin (4 - 8 angka) <span class="text-rose-500" x-show="!isEdit">*</span>
                    </label>
                    <input type="password" name="pin" maxlength="8" placeholder="Masukkan 4-8 angka PIN..." class="w-full px-4 py-2 bg-white border border-purple-300 rounded-xl font-mono text-center font-bold tracking-widest">
                    <p class="text-[11px] text-purple-700" x-show="isEdit">Kosongkan PIN jika tidak ingin mengubah PIN saat ini.</p>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <button type="button" @click="modalForm = false" class="px-5 py-2.5 rounded-xl text-slate-600 font-bold hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-md">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Impor CSV/Excel -->
    <div x-show="modalImpor" @click.outside="modalImpor = false" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-base font-extrabold text-slate-900">Impor Pegawai via CSV / Excel</h3>
                <button type="button" @click="modalImpor = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="text-xs text-slate-600 space-y-3">
                <p>Format file CSV harus memiliki kolom header: <code>NIP;Nama;Jabatan;Unit</code></p>
                <a href="{{ route('admin.pegawai.template') }}" class="inline-flex items-center space-x-2 text-brand-600 font-bold hover:underline">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Unduh File Template CSV</span>
                </a>
            </div>

            <form action="{{ route('admin.pegawai.impor') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih File CSV <span class="text-rose-500">*</span></label>
                    <input type="file" name="file" accept=".csv,text/csv" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold" required>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <button type="button" @click="modalImpor = false" class="px-5 py-2.5 rounded-xl text-slate-600 font-bold hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md">Mulai Impor</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function pegawaiAdminApp() {
    return {
        modalForm: false,
        modalImpor: false,
        isEdit: false,
        form: { id: null, nip: '', nama: '', jabatan: '', unit_id: '', is_admin: false, aktif: true },

        bukaTambah() {
            this.isEdit = false;
            this.form = { id: null, nip: '', nama: '', jabatan: '', unit_id: '', is_admin: false, aktif: true };
            this.modalForm = true;
        },

        bukaEdit(item) {
            this.isEdit = true;
            this.form = { ...item, is_admin: !!item.is_admin, aktif: (item.aktif === undefined || item.aktif === null) ? true : !!item.aktif };
            this.modalForm = true;
        }
    }
}
</script>
@endsection
