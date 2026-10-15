<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Absensi Twibbon') - {{ \App\Models\Pengaturan::namaInstitusi() }}</title>
    
    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN & Alpine.js -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Animasi Button & Micro-interactions */
        @keyframes shimmer {
            0% { transform: translateX(-150%) rotate(25deg); }
            100% { transform: translateX(250%) rotate(25deg); }
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4), 0 10px 15px -3px rgba(37, 99, 235, 0.2); }
            50% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0), 0 10px 15px -3px rgba(37, 99, 235, 0.2); }
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        @keyframes bounceSoft {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }
        .btn-animate {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            user-select: none;
        }
        .btn-animate:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.15);
        }
        .btn-animate:active {
            transform: translateY(0) scale(0.96);
            box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.1);
        }
        .btn-shimmer {
            position: relative;
            overflow: hidden;
        }
        .btn-shimmer::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 25%;
            height: 200%;
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.4) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: rotate(25deg);
            pointer-events: none;
        }
        .btn-shimmer:hover::after {
            animation: shimmer 1.1s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }
        .btn-pulse {
            animation: pulseGlow 2.5s infinite;
        }
        .btn-gradient {
            background-size: 200% 200% !important;
            animation: gradientShift 4s ease infinite;
        }
        .btn-floating {
            animation: bounceSoft 2.5s ease-in-out infinite;
        }

        /* Hilangkan Scrollbar (tetap bisa di-scroll dengan mouse wheel / touch) */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
            width: 0px;
            background: transparent;
        }
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50 flex flex-col selection:bg-brand-500 selection:text-white">
    
    <!-- Header / Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                
                <!-- Logo & Title -->
                <a href="{{ route('beranda') }}" class="flex items-center space-x-3 group">
                    @if($logo = \App\Models\Pengaturan::logoUrl())
                        <img src="{{ asset($logo) }}" alt="Logo" class="h-10 w-auto object-contain transition group-hover:scale-105">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-brand-500/20 group-hover:scale-105 transition">
                            <i data-lucide="camera" class="w-5 h-5"></i>
                        </div>
                    @endif
                    <div>
                        <span class="block text-lg font-bold text-slate-900 leading-tight group-hover:text-brand-600 transition">
                            {{ \App\Models\Pengaturan::namaInstitusi() }}
                        </span>
                        <span class="block text-xs font-medium text-slate-500">Absensi Digital</span>
                    </div>
                </a>

                <!-- Jam Server Live -->
                <div class="hidden md:flex items-center space-x-4 bg-slate-100/80 px-4 py-2 rounded-xl border border-slate-200/60" id="serverClockWidget">
                    <i data-lucide="clock" class="w-4 h-4 text-brand-600 animate-pulse"></i>
                    <div class="text-xs">
                        <span id="clockDate" class="text-slate-500 font-medium block"></span>
                        <span id="clockTime" class="text-slate-900 font-bold font-mono text-sm block">--:--:-- WIB</span>
                    </div>
                </div>

                <!-- Action Button / User Menu -->
                <div class="flex items-center space-x-2 sm:space-x-3">
                    @if(session('pegawai_id'))
                        @php $pegawaiSesi = \App\Models\Pegawai::find(session('pegawai_id')); @endphp
                        @if($pegawaiSesi)
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('absen') }}" class="inline-flex items-center space-x-2 px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-semibold text-brand-700 bg-brand-50 rounded-xl hover:bg-brand-100 transition">
                                    <i data-lucide="user-check" class="w-4 h-4"></i>
                                    <span class="hidden sm:inline">{{ $pegawaiSesi->nama }}</span>
                                </a>
                                @if(session('admin_ok'))
                                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-semibold text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition shadow-sm">
                                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                        <span>Panel Admin</span>
                                    </a>
                                @endif
                                <form action="{{ route('keluar') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-2 text-slate-400 hover:text-red-600 rounded-xl hover:bg-red-50 transition" title="Keluar">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    @elseif(session('mahasiswa_id'))
                        @php $mhsSesi = \App\Models\Mahasiswa::find(session('mahasiswa_id')); @endphp
                        @if($mhsSesi)
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('mahasiswa') }}" class="inline-flex items-center space-x-2 px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-semibold text-indigo-700 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition">
                                    <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                                    <span class="hidden sm:inline">{{ $mhsSesi->nama }}</span>
                                </a>
                                <form action="{{ route('mahasiswa.keluar') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-2 text-slate-400 hover:text-red-600 rounded-xl hover:bg-red-50 transition" title="Keluar">
                                        <i data-lucide="log-out" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    @else
                        <a href="{{ route('mahasiswa.masuk') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition">
                            <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                            <span>Masuk Mahasiswa</span>
                        </a>
                        <a href="{{ route('pegawai.masuk') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                            <i data-lucide="user-check" class="w-4 h-4 text-brand-600"></i>
                            <span>Masuk Pegawai</span>
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </header>

    <!-- Notifikasi Flash Alert -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if(session('sukses'))
            <div class="p-4 mb-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 flex items-start space-x-3 shadow-sm">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5"></i>
                <div class="text-sm font-medium">{{ session('sukses') }}</div>
            </div>
        @endif

        @if(session('galat'))
            <div class="p-4 mb-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 flex items-start space-x-3 shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
                <div class="text-sm font-medium">{{ session('galat') }}</div>
            </div>
        @endif

        @if(session('peringatan'))
            <div class="p-4 mb-4 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-800 flex items-start space-x-3 shadow-sm">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                <div class="text-sm font-medium">{{ session('peringatan') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 mb-4 rounded-2xl bg-rose-50 border border-rose-200/80 text-rose-800 flex items-start space-x-3 shadow-sm">
                <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
                <div class="text-sm">
                    <span class="font-bold block mb-1">Mohon perbaiki kesalahan berikut:</span>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-12 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div>
                &copy; {{ date('Y') }} <span class="font-semibold text-slate-700">{{ \App\Models\Pengaturan::namaInstitusi() }}</span>. Seluruh Hak Cipta Dilindungi.
            </div>
            <div class="flex items-center space-x-4">
                <span>Jam Server WIB</span>
                <span>•</span>
                <span>Sistem Absensi GPS & Twibbon</span>
            </div>
        </div>
    </footer>

    <!-- Lucide Icons Initialize -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }

            // Sync Server Time
            let diffMs = 0;
            fetch('{{ route("waktu") }}')
                .then(r => r.json())
                .then(d => {
                    diffMs = d.epoch - Date.now();
                    const elDate = document.getElementById('clockDate');
                    if (elDate) elDate.innerText = d.tanggal;
                }).catch(() => {});

            function updateClock() {
                const now = new Date(Date.now() + diffMs);
                const hrs = String(now.getHours()).padStart(2, '0');
                const mins = String(now.getMinutes()).padStart(2, '0');
                const secs = String(now.getSeconds()).padStart(2, '0');
                const elTime = document.getElementById('clockTime');
                if (elTime) elTime.innerText = `${hrs}:${mins}:${secs} WIB`;
            }
            setInterval(updateClock, 1000);
            updateClock();
        });
    </script>
    @stack('scripts')
</body>
</html>
