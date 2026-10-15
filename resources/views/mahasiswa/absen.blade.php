@extends('layouts.app')

@section('title', 'Verifikasi Presensi & Twibbon Mahasiswa')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 sm:py-12" x-data="absenMahasiswaApp()">
    
    <!-- User Profile Header Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-sky-600 text-white font-bold text-xl flex items-center justify-center shadow-md shadow-indigo-500/30">
                {{ substr($mahasiswa->nama, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-tight">{{ $mahasiswa->nama }}</h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700">Mahasiswa</span>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 mt-1">
                    <span class="font-mono bg-slate-100 px-2 py-0.5 rounded-md font-semibold text-slate-700">NIM: {{ $mahasiswa->nim }}</span>
                    <span>•</span>
                    <span class="font-semibold text-indigo-600">{{ $mahasiswa->prodi->nama ?? '-' }}</span>
                    <span>•</span>
                    <span>{{ $mahasiswa->utDaerah->nama ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
            <form action="{{ route('mahasiswa.keluar') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 font-semibold text-xs transition">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Keluar Akun</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Status Cards & Today's Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        
        <!-- Status Absen Masuk -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Jam Absen Masuk</span>
            <div class="flex items-center justify-between">
                <span class="text-2xl font-mono font-extrabold text-slate-900" x-text="jamMasukTampil">
                    {{ $absen && $absen->jam_masuk ? substr($absen->jam_masuk, 0, 5) : '--:--' }}
                </span>
                <template x-if="statusMasuk">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200" x-text="statusMasuk"></span>
                </template>
                <template x-if="!statusMasuk">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">Belum</span>
                </template>
            </div>
        </div>

        <!-- Status Absen Pulang -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Jam Absen Pulang</span>
            <div class="flex items-center justify-between">
                <span class="text-2xl font-mono font-extrabold text-slate-900" x-text="jamPulangTampil">
                    {{ $absen && $absen->jam_pulang ? substr($absen->jam_pulang, 0, 5) : '--:--' }}
                </span>
                <template x-if="sudahPulang">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">Pulang</span>
                </template>
                <template x-if="!sudahPulang">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">Belum</span>
                </template>
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
            <span>Hari ini adalah hari libur: <strong>{{ $libur }}</strong>. Anda tetap diperbolehkan melakukan absensi.</span>
        </div>
    @endif

    <!-- Main Camera & Selfie Twibbon Section -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xl">
        
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 flex items-center space-x-2">
                    <i data-lucide="camera" class="w-5 h-5 text-indigo-600"></i>
                    <span>Verifikasi Selfie & Twibbon</span>
                </h2>
                <p class="text-xs text-slate-500 mt-1">Posisikan wajah Anda di dalam bingkai Twibbon dan ambil foto selfie.</p>
            </div>
            @if($twibbon)
                <span class="hidden sm:inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>{{ $twibbon->nama }}</span>
                </span>
            @endif
        </div>

        <!-- Camera / Twibbon Canvas Container -->
        <div class="relative aspect-square max-w-md mx-auto bg-slate-900 rounded-3xl overflow-hidden shadow-2xl border-4 border-slate-900 group">
            
            <!-- Video Feed Live -->
            <video x-ref="video" autoplay playsinline class="w-full h-full object-cover transform -scale-x-100" x-show="!fotoDataUrl"></video>
            
            <!-- Twibbon Overlay Image Live -->
            @if($twibbon)
                <img src="{{ asset($twibbon->file) }}" alt="Twibbon Active" class="absolute inset-0 w-full h-full object-contain pointer-events-none z-10" x-show="!fotoDataUrl">
            @endif

            <!-- Result Captured Preview Canvas (Hidden element) -->
            <canvas x-ref="canvas" class="hidden"></canvas>
            
            <!-- Image captured preview -->
            <img :src="fotoDataUrl" class="w-full h-full object-cover z-20" x-show="fotoDataUrl">

            <!-- Camera overlay control buttons -->
            <div class="absolute bottom-4 left-0 right-0 z-30 flex justify-center space-x-3 px-4">
                <button type="button" @click="ambilFoto()" x-show="!fotoDataUrl" class="px-5 py-2.5 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/40 flex items-center space-x-2 transition transform active:scale-95">
                    <i data-lucide="aperture" class="w-4 h-4"></i>
                    <span>Ambil Foto Selfie</span>
                </button>
                <button type="button" @click="ulangFoto()" x-show="fotoDataUrl" class="px-5 py-2.5 rounded-full bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs shadow-lg flex items-center space-x-2 transition">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>Foto Ulang</span>
                </button>
            </div>
        </div>

        <!-- Download preview before submission -->
        <div x-show="fotoDataUrl" x-transition class="mt-4 flex justify-center">
            <button type="button" @click="unduhDataUrl(fotoDataUrl, 'selfie-twibbon-mahasiswa.jpg')" class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition shadow-sm">
                <i data-lucide="download" class="w-3.5 h-3.5 text-indigo-600"></i>
                <span>Unduh Foto Selfie Ini</span>
            </button>
        </div>

        <!-- Presensi Action Controls -->
        <div class="mt-8 pt-6 border-t border-slate-100">
            <template x-if="!sudahMasuk">
                <div>
                    <button type="button" 
                            @click="kirimAbsensi('masuk')"
                            :disabled="!fotoDataUrl || loading"
                            class="w-full py-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-sm shadow-xl shadow-indigo-600/25 flex items-center justify-center space-x-3 transition">
                        <i data-lucide="log-in" class="w-5 h-5"></i>
                        <span x-text="loading ? 'Memproses Presensi...' : 'Kirim Absen Masuk Mahasiswa'"></span>
                    </button>
                    <p class="text-center text-[11px] text-slate-400 mt-2">Ambil foto selfie di atas terlebih dahulu untuk mengaktifkan tombol absen masuk.</p>
                </div>
            </template>

            <template x-if="sudahMasuk && !sudahPulang">
                <div>
                    <button type="button" 
                            @click="kirimAbsensi('pulang')"
                            :disabled="!fotoDataUrl || loading"
                            class="w-full py-4 rounded-2xl bg-brand-600 hover:bg-brand-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-sm shadow-xl shadow-brand-600/25 flex items-center justify-center space-x-3 transition">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        <span x-text="loading ? 'Memproses Presensi...' : 'Kirim Absen Pulang Mahasiswa'"></span>
                    </button>
                    <p class="text-center text-[11px] text-slate-400 mt-2">Anda sudah absen masuk. Silakan ambil selfie baru untuk verifikasi absen pulang.</p>
                </div>
            </template>

            <template x-if="sudahMasuk && sudahPulang">
                <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-200 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 mb-2">
                        <i data-lucide="check-check" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-extrabold text-emerald-900">Presensi Hari Ini Selesai</h3>
                    <p class="text-xs text-emerald-700 mt-1">Anda telah menyelesaikan presensi masuk dan pulang untuk hari ini. Terima kasih!</p>
                </div>
            </template>
        </div>

        <!-- Download Twibbon Result Section After Submission -->
        <div x-show="hasilFotoUrl" x-transition class="mt-6 p-5 rounded-2xl bg-indigo-50 border border-indigo-200 text-center">
            <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 mb-2">
                <i data-lucide="check" class="w-5 h-5"></i>
            </div>
            <p class="text-xs font-bold text-indigo-900 mb-3" x-text="pesanSukses"></p>
            <div class="flex justify-center">
                <a :href="hasilFotoUrl" download="foto-absen-twibbon-mahasiswa.jpg" class="inline-flex items-center space-x-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md transition">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Unduh Hasil Twibbon Tersimpan</span>
                </a>
            </div>
        </div>

    </div>

</div>

<script>
function absenMahasiswaApp() {
    return {
        sudahMasuk: {{ ($absen && $absen->jam_masuk) ? 'true' : 'false' }},
        sudahPulang: {{ ($absen && $absen->jam_pulang) ? 'true' : 'false' }},
        jamMasukTampil: '{{ ($absen && $absen->jam_masuk) ? substr($absen->jam_masuk, 0, 5) : "--:--" }}',
        jamPulangTampil: '{{ ($absen && $absen->jam_pulang) ? substr($absen->jam_pulang, 0, 5) : "--:--" }}',
        statusMasuk: '{{ ($absen && $absen->jam_masuk) ? \App\Models\Absensi::labelStatus($absen->status) : "" }}',
        
        fotoDataUrl: null,
        lat: null,
        lng: null,
        gpsValid: false,
        gpsStatusText: 'Mengambil lokasi GPS...',
        loading: false,
        hasilFotoUrl: null,
        pesanSukses: '',

        init() {
            this.startCamera();
            this.getGPS();
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        async startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: 720, height: 720 }
                });
                this.$refs.video.srcObject = stream;
            } catch (err) {
                alert('Tidak dapat mengakses kamera. Pastikan izin kamera telah diberikan.');
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
                    this.gpsStatusText = `GPS Aktif (${this.lat.toFixed(4)}, ${this.lng.toFixed(4)})`;
                },
                err => {
                    this.gpsStatusText = 'GPS tidak diizinkan / nonaktif';
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

            // Mirror preview flip
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            ctx.setTransform(1, 0, 0, 1, 0, 0);

            @if($twibbon)
                const twibbonImg = new Image();
                twibbonImg.crossOrigin = "anonymous";
                twibbonImg.onload = () => {
                    ctx.drawImage(twibbonImg, 0, 0, canvas.width, canvas.height);
                    this.fotoDataUrl = canvas.toDataURL('image/jpeg', 0.9);
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                };
                twibbonImg.src = "{{ asset($twibbon->file) }}";
            @else
                this.fotoDataUrl = canvas.toDataURL('image/jpeg', 0.9);
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            @endif
        },

        ulangFoto() {
            this.fotoDataUrl = null;
            this.hasilFotoUrl = null;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        unduhDataUrl(dataUrl, namaFile) {
            const a = document.createElement('a');
            a.href = dataUrl;
            a.download = namaFile || 'foto-absen-mahasiswa.jpg';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        },

        async kirimAbsensi(jenis) {
            if (!this.fotoDataUrl) {
                return alert('Silakan ambil foto selfie dengan twibbon terlebih dahulu!');
            }
            this.loading = true;

            const url = jenis === 'masuk' 
                ? '{{ route("mahasiswa.absen.masuk") }}' 
                : '{{ route("mahasiswa.absen.pulang") }}';

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
                    alert(data.pesan || 'Terjadi galat saat memproses absensi.');
                } else {
                    this.pesanSukses = data.pesan;
                    this.hasilFotoUrl = data.foto;
                    if (jenis === 'masuk') {
                        this.sudahMasuk = true;
                        this.jamMasukTampil = data.jam;
                        this.statusMasuk = data.status;
                    } else {
                        this.sudahPulang = true;
                        this.jamPulangTampil = data.jam;
                    }
                    this.fotoDataUrl = null;
                    alert(data.pesan);
                }
            } catch (e) {
                alert('Gagal terhubung ke server.');
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
