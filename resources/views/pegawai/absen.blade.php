@extends('layouts.app')

@section('title', 'Presensi Pegawai')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 sm:py-12" x-data="absenPegawaiApp()">
    
    <!-- User Header Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-600 text-white font-bold text-xl flex items-center justify-center shadow-md shadow-brand-500/30">
                {{ substr($pegawai->nama, 0, 1) }}
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-tight">{{ $pegawai->nama }}</h1>
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 mt-1">
                    <span class="font-mono bg-slate-100 px-2 py-0.5 rounded-md font-semibold text-slate-700">NIP: {{ $pegawai->nip }}</span>
                    <span>•</span>
                    <span>{{ $pegawai->jabatan ?: 'Pegawai' }}</span>
                    <span>•</span>
                    <span class="font-bold text-brand-600">{{ $pegawai->unit->nama ?? '-' }}</span>
                </div>
            </div>
        </div>

        <a href="{{ route('riwayat') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
            <i data-lucide="history" class="w-4 h-4 text-brand-600"></i>
            <span>Lihat Riwayat Presensi</span>
        </a>
    </div>

    <!-- Status Cards & Today's Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        
        <!-- Status Absen Masuk -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Jam Absen Masuk</span>
            <div class="flex items-center justify-between">
                <span class="text-2xl font-mono font-extrabold text-slate-900">
                    {{ $absen && $absen->jam_masuk ? substr($absen->jam_masuk, 0, 5) : '--:--' }}
                </span>
                @if($absen && $absen->jam_masuk)
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        {{ \App\Models\Absensi::labelStatus($absen->status) }}
                    </span>
                @else
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">Belum</span>
                @endif
            </div>
        </div>

        <!-- Status Absen Pulang -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Jam Absen Pulang</span>
            <div class="flex items-center justify-between">
                <span class="text-2xl font-mono font-extrabold text-slate-900">
                    {{ $absen && $absen->jam_pulang ? substr($absen->jam_pulang, 0, 5) : '--:--' }}
                </span>
                @if($absen && $absen->jam_pulang)
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">Pulang</span>
                @else
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">Belum</span>
                @endif
            </div>
        </div>

        <!-- Status GPS & Lokasi -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Validasi Lokasi GPS</span>
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full shrink-0" :class="gpsValid ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                <span class="text-xs font-bold text-slate-800 truncate" x-text="gpsStatusText">Mendeteksi lokasi...</span>
            </div>
        </div>

    </div>

    @if($libur)
        <div class="p-4 mb-8 rounded-2xl bg-sky-50 border border-sky-200 text-sky-800 flex items-center space-x-3 text-xs font-semibold">
            <i data-lucide="info" class="w-5 h-5 text-sky-600 shrink-0"></i>
            <span>Hari ini adalah hari libur: <strong>{{ $libur }}</strong>. Anda tetap diperbolehkan absen bila bertugas.</span>
        </div>
    @endif

    <!-- Main Camera & Selfie Twibbon Section -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xl">
        
        <h2 class="text-lg font-extrabold text-slate-900 mb-4 flex items-center space-x-2">
            <i data-lucide="camera" class="w-5 h-5 text-brand-600"></i>
            <span>Kamera Selfie & Twibbon Aktif</span>
        </h2>

        <!-- Camera / Twibbon Canvas Container -->
        <div class="relative aspect-square max-w-md mx-auto bg-slate-900 rounded-3xl overflow-hidden shadow-2xl border-4 border-slate-900 group">
            
            <!-- Video Feed Live -->
            <video x-ref="video" autoplay playsinline class="w-full h-full object-cover transform -scale-x-100" x-show="!fotoDataUrl"></video>
            
            <!-- Twibbon Overlay Image Live -->
            @if($twibbon)
                <img src="{{ asset($twibbon->file) }}" alt="Twibbon Active" class="absolute inset-0 w-full h-full object-contain pointer-events-none z-10" x-show="!fotoDataUrl">
            @endif

            <!-- Result Captured Preview Canvas (Hidden element used for capturing) -->
            <canvas x-ref="canvas" class="hidden"></canvas>
            
            <!-- Image captured preview -->
            <img :src="fotoDataUrl" class="w-full h-full object-cover z-20" x-show="fotoDataUrl">

            <!-- Camera overlay control buttons -->
            <div class="absolute bottom-4 left-0 right-0 z-30 flex justify-center space-x-3 px-4">
                <button type="button" @click="ambilFoto()" x-show="!fotoDataUrl" class="px-5 py-2.5 rounded-full bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-lg shadow-brand-600/40 flex items-center space-x-2 transition transform active:scale-95">
                    <i data-lucide="aperture" class="w-4 h-4"></i>
                    <span>Ambil Foto Selfie</span>
                </button>
                <button type="button" @click="ulangFoto()" x-show="fotoDataUrl" class="px-5 py-2.5 rounded-full bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs shadow-lg flex items-center space-x-2 transition">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>Foto Ulang</span>
                </button>
            </div>
        </div>

        <!-- Preview Action Buttons (Unduh Sebelum Kirim) -->
        <div x-show="fotoDataUrl" x-transition class="mt-4 flex justify-center">
            <button type="button" @click="unduhDataUrl(fotoDataUrl, 'selfie-twibbon-preview.jpg')" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition shadow-sm">
                <i data-lucide="download" class="w-4 h-4 text-brand-600"></i>
                <span>Unduh Foto Selfie Ini</span>
            </button>
        </div>

        <p class="text-center text-xs text-slate-400 mt-4">
            Pastikan wajah terlihat jelas. Twibbon resmi otomatis digabungkan dan dapat langsung diunduh.
        </p>

        <!-- Submit Absen Actions -->
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
            
            <!-- Button Absen Masuk -->
            <button type="button" 
                    @click="submitAbsen('masuk')"
                    :disabled="!fotoDataUrl || (absenState && absenState.jam_masuk) || loading"
                    class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-sm shadow-xl shadow-emerald-600/20 flex items-center justify-center space-x-3 transition">
                <i data-lucide="log-in" class="w-5 h-5"></i>
                <span x-text="loading ? 'Memproses...' : 'Kirim Absen Masuk'"></span>
            </button>

            <!-- Button Absen Pulang -->
            <button type="button" 
                    @click="submitAbsen('pulang')"
                    :disabled="!fotoDataUrl || !absenState?.jam_masuk || absenState?.jam_pulang || loading"
                    class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-sm shadow-xl shadow-indigo-600/20 flex items-center justify-center space-x-3 transition">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                <span x-text="loading ? 'Memproses...' : 'Kirim Absen Pulang'"></span>
            </button>

        </div>

    </div>

    <!-- Foto Twibbon Presensi Hari Ini (Jika sudah pernah absen hari ini) -->
    @if($absen && ($absen->foto_masuk || $absen->foto_pulang))
        <div class="mt-8 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
            <h2 class="text-base font-extrabold text-slate-900 mb-4 flex items-center space-x-2">
                <i data-lucide="image" class="w-5 h-5 text-brand-600"></i>
                <span>Foto Presensi & Twibbon Hari Ini</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @if($absen->foto_masuk)
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 text-center">
                        <div class="relative aspect-square rounded-2xl overflow-hidden mb-3 shadow-md border border-slate-200 bg-slate-900">
                            <img src="{{ asset($absen->foto_masuk) }}" alt="Foto Absen Masuk" class="w-full h-full object-cover">
                            <span class="absolute top-2 left-2 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-600 text-white shadow">
                                Masuk • {{ substr($absen->jam_masuk, 0, 5) }} WIB
                            </span>
                        </div>
                        <div class="flex justify-center">
                            <a href="{{ asset($absen->foto_masuk) }}" download="foto-absen-masuk-{{ $pegawai->nip }}.jpg" class="inline-flex items-center space-x-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>Unduh Foto</span>
                            </a>
                        </div>
                    </div>
                @endif
                @if($absen->foto_pulang)
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 text-center">
                        <div class="relative aspect-square rounded-2xl overflow-hidden mb-3 shadow-md border border-slate-200 bg-slate-900">
                            <img src="{{ asset($absen->foto_pulang) }}" alt="Foto Absen Pulang" class="w-full h-full object-cover">
                            <span class="absolute top-2 left-2 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-600 text-white shadow">
                                Pulang • {{ substr($absen->jam_pulang, 0, 5) }} WIB
                            </span>
                        </div>
                        <div class="flex justify-center">
                            <a href="{{ asset($absen->foto_pulang) }}" download="foto-absen-pulang-{{ $pegawai->nip }}.jpg" class="inline-flex items-center space-x-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>Unduh Foto</span>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Modal Sukses Presensi & Download Twibbon -->
    <div x-show="modalSukses" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full border border-slate-200 shadow-2xl text-center transform transition-all">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 inline-flex items-center justify-center mb-4">
                <i data-lucide="check-circle-2" class="w-8 h-8"></i>
            </div>
            <h3 class="text-xl font-extrabold text-slate-900 mb-1">Presensi Berhasil Dicatat!</h3>
            <p class="text-xs text-slate-500 mb-4" x-text="pesanSukses"></p>

            <!-- Preview Hasil Foto -->
            <div class="relative aspect-square max-w-[260px] mx-auto rounded-2xl overflow-hidden shadow-lg border-2 border-emerald-500/30 mb-6 bg-slate-900">
                <img :src="hasilFotoUrl" alt="Foto Hasil Presensi" class="w-full h-full object-cover">
            </div>

            <div class="flex items-center justify-center mb-3">
                <a :href="hasilFotoUrl" download="foto-absen-twibbon-{{ $pegawai->nip }}.jpg" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center space-x-2">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Unduh Foto Twibbon</span>
                </a>
            </div>

            <button type="button" @click="tutupModal()" class="w-full py-2.5 text-xs text-slate-400 hover:text-slate-600 font-semibold transition">
                Tutup & Perbarui Halaman
            </button>
        </div>
    </div>

</div>

<script>
function absenPegawaiApp() {
    return {
        fotoDataUrl: null,
        lat: null,
        lng: null,
        gpsValid: false,
        gpsStatusText: 'Mengambil lokasi GPS...',
        loading: false,
        absenState: @json($absen),
        hasilFotoUrl: null,
        pesanSukses: '',
        modalSukses: false,

        init() {
            this.startCamera();
            this.getGPS();
        },

        async startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: 720, height: 720 } });
                this.$refs.video.srcObject = stream;
            } catch (err) {
                alert('Gagal mengakses kamera web. Izinkan akses kamera di browser Anda.');
            }
        },

        getGPS() {
            if (!navigator.geolocation) {
                this.gpsStatusText = 'Browser tidak mendukung GPS';
                return;
            }
            navigator.geolocation.getCurrentPosition(
                pos => {
                    this.lat = pos.coords.latitude;
                    this.lng = pos.coords.longitude;
                    this.gpsValid = true;
                    this.gpsStatusText = `GPS Aktif: ${this.lat.toFixed(5)}, ${this.lng.toFixed(5)}`;
                },
                err => {
                    this.gpsValid = false;
                    this.gpsStatusText = 'GPS tidak diizinkan / tidak aktif';
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },

        ambilFoto() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            canvas.width = 720;
            canvas.height = 720;
            const ctx = canvas.getContext('2d');

            // Draw video (mirror horizontally)
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            ctx.setTransform(1, 0, 0, 1, 0, 0); // reset transform

            // Draw Twibbon overlay if available
            @if($twibbon)
                const twibbonImg = new Image();
                twibbonImg.crossOrigin = "anonymous";
                twibbonImg.onload = () => {
                    ctx.drawImage(twibbonImg, 0, 0, canvas.width, canvas.height);
                    this.fotoDataUrl = canvas.toDataURL('image/jpeg', 0.9);
                };
                twibbonImg.src = "{{ asset($twibbon->file) }}";
            @else
                this.fotoDataUrl = canvas.toDataURL('image/jpeg', 0.9);
            @endif
        },

        ulangFoto() {
            this.fotoDataUrl = null;
            this.hasilFotoUrl = null;
        },

        unduhDataUrl(dataUrl, namaFile) {
            const a = document.createElement('a');
            a.href = dataUrl;
            a.download = namaFile || 'foto-absen-twibbon.jpg';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        },

        tutupModal() {
            this.modalSukses = false;
            window.location.reload();
        },

        async submitAbsen(jenis) {
            if (!this.fotoDataUrl) return;
            this.loading = true;

            const url = jenis === 'masuk' ? '{{ route("absen.masuk") }}' : '{{ route("absen.pulang") }}';
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        foto: this.fotoDataUrl,
                        lat: this.lat,
                        lng: this.lng
                    })
                });

                const data = await res.json();
                if (!data.ok) {
                    alert(data.pesan || 'Terjadi kesalahan saat menyimpan presensi.');
                } else {
                    this.pesanSukses = data.pesan;
                    this.hasilFotoUrl = data.foto;
                    this.modalSukses = true;
                    // Auto-refresh lucide icons in modal
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                }
            } catch (e) {
                alert('Gagal terhubung ke server.');
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection
