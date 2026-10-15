<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Admin') - {{ \App\Models\Pengaturan::namaInstitusi() }}</title>
    
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

    <!-- Leaflet JS & CSS for Map Location Picker -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Chart.js for Statistics Graphic -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .no-scrollbar::-webkit-scrollbar,
        aside nav::-webkit-scrollbar {
            display: none;
            width: 0px;
            background: transparent;
        }
        .no-scrollbar,
        aside nav {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-100 flex flex-col sm:flex-row" x-data="{ sidebarOpen: false }">
    
    <!-- Sidebar Mobile Backdrop -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/50 sm:hidden"></div>

    <!-- Sidebar Admin -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col transform transition-transform duration-200 ease-in-out sm:static sm:translate-x-0 shrink-0 shadow-xl"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        
        <!-- Header Sidebar -->
        <div class="h-16 flex items-center px-6 bg-slate-950/60 border-b border-slate-800 justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center text-white font-bold text-lg shadow-md shadow-brand-500/30">
                    A
                </div>
                <div>
                    <span class="font-bold text-white text-sm block leading-tight">Panel Admin</span>
                    <span class="text-[10px] text-slate-400 font-medium block truncate max-w-[130px]">{{ \App\Models\Pengaturan::namaInstitusi() }}</span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="sm:hidden text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Navigation Menu Links -->
        <nav class="flex-grow px-3 py-4 space-y-1 overflow-y-auto no-scrollbar text-xs font-medium">
            
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white font-semibold shadow-md shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span>Dashboard</span>
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Pengaturan Twibbon & GPS</div>

            <a href="{{ route('admin.twibbon') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.twibbon') ? 'bg-brand-600 text-white font-semibold shadow-md shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="image" class="w-4 h-4"></i>
                <span>Kelola Twibbon</span>
            </a>

            <a href="{{ route('admin.lokasi') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.lokasi') ? 'bg-brand-600 text-white font-semibold shadow-md shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="map-pin" class="w-4 h-4"></i>
                <span>Lokasi & Radius</span>
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Data Pengguna & Master</div>

            <a href="{{ route('admin.pegawai') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.pegawai') ? 'bg-brand-600 text-white font-semibold shadow-md shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Data Pegawai</span>
            </a>

            <a href="{{ route('admin.mahasiswa') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.mahasiswa') ? 'bg-brand-600 text-white font-semibold shadow-md shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                <span>Absensi Mahasiswa</span>
            </a>

            <a href="{{ route('admin.master') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.master') ? 'bg-brand-600 text-white font-semibold shadow-md shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="database" class="w-4 h-4"></i>
                <span>Master Data</span>
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Kelola Absensi & Laporan</div>

            <a href="{{ route('admin.koreksi.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.koreksi.*') ? 'bg-brand-600 text-white font-semibold shadow-md shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
                <span>Koreksi Absensi</span>
            </a>

            <a href="{{ route('admin.libur') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.libur') ? 'bg-brand-600 text-white font-semibold shadow-md shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="calendar" class="w-4 h-4"></i>
                <span>Kalender Libur</span>
            </a>

            <a href="{{ route('admin.laporan') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.laporan') ? 'bg-brand-600 text-white font-semibold shadow-md shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Laporan & Rekap</span>
            </a>

            <a href="{{ route('admin.galeri') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.galeri') ? 'bg-brand-600 text-white font-semibold shadow-md shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="images" class="w-4 h-4"></i>
                <span>Galeri Foto</span>
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Sistem</div>

            <a href="{{ route('admin.pengaturan') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.pengaturan') ? 'bg-brand-600 text-white font-semibold shadow-md shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="settings" class="w-4 h-4"></i>
                <span>Pengaturan System</span>
            </a>

            <a href="{{ route('admin.log') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.log') ? 'bg-brand-600 text-white font-semibold shadow-md shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="shield-alert" class="w-4 h-4"></i>
                <span>Log Aktivitas</span>
            </a>

        </nav>

        <!-- User Info Footer -->
        <div class="p-4 bg-slate-950/80 border-t border-slate-800 flex items-center justify-between text-xs">
            <div class="truncate">
                @php $peg = \App\Models\Pegawai::find(session('pegawai_id')); @endphp
                <span class="font-bold text-white block truncate">{{ $peg->nama ?? 'Admin' }}</span>
                <span class="text-[10px] text-slate-400 block truncate">Administrator Active</span>
            </div>
            <a href="{{ route('absen') }}" class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition" title="Kembali ke Mode Absen">
                <i data-lucide="user-check" class="w-4 h-4"></i>
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-grow flex flex-col min-w-0">
        
        <!-- Top Bar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-8 shrink-0">
            <div class="flex items-center space-x-4">
                <button @click="sidebarOpen = true" class="sm:hidden text-slate-600 hover:text-slate-900 p-1">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="text-base sm:text-lg font-bold text-slate-900 truncate">
                    @yield('title', 'Dashboard Admin')
                </h1>
            </div>

            <div class="flex items-center space-x-4">
                <a href="{{ route('beranda') }}" target="_blank" class="inline-flex items-center space-x-1 text-xs font-semibold text-brand-600 hover:text-brand-800 bg-brand-50 px-3 py-1.5 rounded-lg transition">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>Buka Halaman Utama</span>
                </a>
            </div>
        </header>

        <!-- Alert Banner -->
        <div class="px-4 sm:px-8 mt-4">
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

        <!-- Page Body Content -->
        <main class="p-4 sm:p-8 flex-grow">
            @yield('content')
        </main>
    </div>

    <!-- Lucide Icon Init -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
