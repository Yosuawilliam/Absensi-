@extends('layouts.admin')

@section('title', 'Lokasi & Radius Presensi')

@section('content')
<div class="space-y-8" x-data="lokasiAdminApp()">
    
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Lokasi & Radius Presensi GPS</h1>
            <p class="text-xs text-slate-500 mt-1">Atur titik koordinat GPS dan batas jarak maksimal (radius meter). Absen di luar radius akan ditolak oleh sistem.</p>
        </div>

        <button type="button" @click="bukaTambah()" class="px-5 py-2.5 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-lg shadow-brand-600/30 flex items-center space-x-2 transition">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Lokasi Baru</span>
        </button>
    </div>

    <!-- Active Locations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($lokasis as $lok)
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-brand-50 text-brand-700 font-bold text-xs">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                            <span>{{ $lok->nama }}</span>
                        </span>
                        @if($lok->aktif)
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">AKTIF</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold">NONAKTIF</span>
                        @endif
                    </div>

                    <div class="space-y-1.5 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Koordinat Latitude:</span>
                            <span class="font-mono font-bold text-slate-800">{{ $lok->lat }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Koordinat Longitude:</span>
                            <span class="font-mono font-bold text-slate-800">{{ $lok->lng }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Radius Absen Maksimal:</span>
                            <span class="font-bold text-brand-600 font-mono">{{ $lok->radius }} Meter</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end space-x-2">
                    <button type="button" @click="bukaEdit({{ json_encode($lok) }})" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center space-x-1">
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        <span>Edit</span>
                    </button>
                    
                    <form action="{{ route('admin.lokasi.hapus', $lok) }}" method="POST" onsubmit="return confirm('Hapus lokasi {{ $lok->nama }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs flex items-center space-x-1">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Hapus</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl p-12 text-center text-slate-400">
                Belum ada lokasi kantor/ruang absen yang dikonfigurasi.
            </div>
        @endforelse
    </div>

    <!-- Modal Form Tambah/Edit Lokasi with Leaflet Map Picker -->
    <div x-show="modalForm" @click.outside="modalForm = false" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <h3 class="text-base font-extrabold text-slate-900" x-text="isEdit ? 'Edit Lokasi Presensi' : 'Tambah Lokasi Baru'"></h3>
                <button type="button" @click="modalForm = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="isEdit ? `/admin/lokasi/${form.id}` : '{{ route('admin.lokasi.simpan') }}'" method="POST" class="space-y-4 text-xs">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lokasi / Gedung <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" x-model="form.nama" placeholder="Contoh: Gedung Kantor Pusat UT..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl font-semibold" required>
                </div>

                <!-- Leaflet Interactive Map Canvas -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Klik pada peta untuk menentukan koordinat GPS</label>
                    <div id="mapPicker" class="w-full h-60 rounded-2xl border border-slate-300 overflow-hidden z-10"></div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Latitude <span class="text-rose-500">*</span></label>
                        <input type="text" id="inputLat" name="lat" x-model="form.lat" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-mono font-bold" required>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Longitude <span class="text-rose-500">*</span></label>
                        <input type="text" id="inputLng" name="lng" x-model="form.lng" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-mono font-bold" required>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Radius (Meter) <span class="text-rose-500">*</span></label>
                        <input type="number" name="radius" x-model="form.radius" min="10" max="5000" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-mono font-bold" required>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-center space-x-2 font-bold cursor-pointer">
                        <input type="checkbox" name="aktif" value="1" x-model="form.aktif" class="w-4 h-4 text-brand-600 rounded">
                        <span>Aktifkan Lokasi Ini</span>
                    </label>
                </div>

                <div class="pt-4 flex justify-end space-x-3">
                    <button type="button" @click="modalForm = false" class="px-5 py-2.5 rounded-xl text-slate-600 font-bold hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-md">Simpan Lokasi</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function lokasiAdminApp() {
    return {
        modalForm: false,
        isEdit: false,
        form: { id: null, nama: '', lat: -6.175392, lng: 106.827153, radius: 100, aktif: true },
        map: null,
        marker: null,

        bukaTambah() {
            this.isEdit = false;
            this.form = { id: null, nama: '', lat: -6.175392, lng: 106.827153, radius: 100, aktif: true };
            this.modalForm = true;
            this.$nextTick(() => this.initMap());
        },

        bukaEdit(item) {
            this.isEdit = true;
            this.form = { ...item, aktif: !!item.aktif };
            this.modalForm = true;
            this.$nextTick(() => this.initMap());
        },

        initMap() {
            const lat = parseFloat(this.form.lat) || -6.175392;
            const lng = parseFloat(this.form.lng) || 106.827153;

            if (this.map) {
                this.map.remove();
            }

            this.map = L.map('mapPicker').setView([lat, lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.map);

            this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);

            this.marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                this.form.lat = pos.lat.toFixed(7);
                this.form.lng = pos.lng.toFixed(7);
            });

            this.map.on('click', (e) => {
                this.marker.setLatLng(e.latlng);
                this.form.lat = e.latlng.lat.toFixed(7);
                this.form.lng = e.latlng.lng.toFixed(7);
            });
        }
    }
}
</script>
@endsection
