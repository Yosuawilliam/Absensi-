@extends('layouts.admin')

@section('title', 'Dashboard Overview')

@section('content')
<div class="space-y-8">
    
    <!-- Top Banner Hari Ini -->
    <div class="bg-gradient-to-r from-slate-900 via-brand-900 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
            <span class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-brand-500/20 text-brand-300 border border-brand-400/30 text-xs font-semibold uppercase tracking-wider mb-2">
                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                <span>{{ today()->translatedFormat('l, d F Y') }}</span>
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Ringkasan Kehadiran Hari Ini</h2>
            <p class="text-slate-300 text-xs sm:text-sm mt-1">
                @if($libur)
                    <span class="text-amber-300 font-bold">Hari Libur Official: {{ $libur }}</span>
                @else
                    Status Hari Kerja Normal. Toleransi Keterlambatan: <strong>{{ \App\Models\Pengaturan::ambil('toleransi_menit') }} Menit</strong>.
                @endif
            </p>
        </div>

        @if($twibbon)
            <div class="flex items-center space-x-3 bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/20">
                <div class="w-10 h-10 bg-slate-900 rounded-xl p-1 shrink-0">
                    <img src="{{ asset($twibbon->file) }}" alt="Twibbon" class="w-full h-full object-contain">
                </div>
                <div class="text-xs">
                    <span class="text-emerald-400 font-bold block">Twibbon Aktif</span>
                    <span class="text-white font-medium block truncate max-w-[120px]">{{ $twibbon->nama }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Summary Statistics Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 sm:gap-6">
        
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center mb-3">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Pegawai</span>
            <span class="text-2xl font-extrabold text-slate-900 font-mono mt-1 block">{{ $totalPegawai }}</span>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-emerald-200/80 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Hadir Tepat Waktu</span>
            <span class="text-2xl font-extrabold text-emerald-600 font-mono mt-1 block">{{ $tepatWaktu }}</span>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-amber-200/80 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Pegawai Terlambat</span>
            <span class="text-2xl font-extrabold text-amber-600 font-mono mt-1 block">{{ $terlambat }}</span>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mb-3">
                <i data-lucide="user-x" class="w-5 h-5"></i>
            </div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Belum Absen</span>
            <span class="text-2xl font-extrabold text-rose-600 font-mono mt-1 block">{{ $belum }}</span>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-indigo-200/80 shadow-sm col-span-2 lg:col-span-1">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3">
                <i data-lucide="graduation-cap" class="w-5 h-5"></i>
            </div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Mahasiswa Absen</span>
            <span class="text-2xl font-extrabold text-indigo-600 font-mono mt-1 block">{{ $mahasiswaHariIni }}</span>
        </div>

    </div>

    <!-- Chart Trend Graph -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
        <h3 class="text-base font-extrabold text-slate-900 mb-6 flex items-center space-x-2">
            <i data-lucide="trending-up" class="w-5 h-5 text-brand-600"></i>
            <span>Grafik Tren Kehadiran 14 Hari Terakhir</span>
        </h3>
        <div class="h-72 w-full">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <!-- Latest Presensi Activity Table -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-base font-extrabold text-slate-900 flex items-center space-x-2">
                <i data-lucide="activity" class="w-5 h-5 text-brand-600"></i>
                <span>Aktivitas Presensi Terbaru Hari Ini</span>
            </h3>
            <a href="{{ route('admin.laporan') }}" class="text-xs font-bold text-brand-600 hover:text-brand-800">
                Lihat Laporan Lengkap &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs divide-y divide-slate-100">
                <thead class="bg-slate-50 font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Pengguna</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Jam Masuk</th>
                        <th class="px-4 py-3">Jam Pulang</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($terbaru as $row)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3 font-mono text-slate-500">{{ $row->updated_at->format('H:i:s') }}</td>
                            <td class="px-4 py-3 font-bold text-slate-900">{{ $row->namaTampil() }}</td>
                            <td class="px-4 py-3 uppercase text-[10px] font-extrabold">
                                <span class="px-2 py-0.5 rounded-md {{ $row->tipe === 'pegawai' ? 'bg-brand-50 text-brand-700' : 'bg-indigo-50 text-indigo-700' }}">
                                    {{ $row->tipe }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono font-bold">{{ $row->jam_masuk ? substr($row->jam_masuk, 0, 5) : '-' }}</td>
                            <td class="px-4 py-3 font-mono font-bold">{{ $row->jam_pulang ? substr($row->jam_pulang, 0, 5) : '-' }}</td>
                            <td class="px-4 py-3">
                                @if($row->status === 'hadir')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Hadir</span>
                                @elseif($row->status === 'terlambat')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Terlambat</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Tidak Hadir</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada aktivitas presensi hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const trenData = @json($tren);
    const labels = trenData.map(d => d.label);
    const dataPegawai = trenData.map(d => d.pegawai);
    const dataMahasiswa = trenData.map(d => d.mahasiswa);

    const ctx = document.getElementById('trendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pegawai Absen',
                    data: dataPegawai,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 3
                },
                {
                    label: 'Mahasiswa Absen',
                    data: dataMahasiswa,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
});
</script>
@endpush
@endsection
