@extends('layouts.admin')

@section('title', 'Log Aktivitas Sistem')

@section('content')
<div class="space-y-8">
    
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900">Log Aktivitas Sistem</h1>
        <p class="text-xs text-slate-500 mt-1">Audit log perubahan status admin, koreksi manual absensi, penguncian PIN, dan aktivitas penting lainnya.</p>
    </div>

    <!-- Search Bar -->
    <div class="bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.log') }}" class="flex gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari aksi, pelaku, atau keterangan log..." class="flex-grow px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl text-xs font-semibold">
            <button type="submit" class="px-6 py-2.5 bg-slate-900 text-white font-bold text-xs rounded-2xl">Cari Log</button>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs divide-y divide-slate-100">
                <thead class="bg-slate-50 font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Pelaku</th>
                        <th class="px-6 py-4">Aksi / Peristiwa</th>
                        <th class="px-6 py-4">Keterangan Audit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($logs as $l)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 font-mono font-bold text-slate-500 whitespace-nowrap">
                                {{ $l->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ $l->pelaku ?: ($l->pegawai->nama ?? 'Sistem') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-800 border">
                                    {{ $l->aksi }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $l->keterangan }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">Belum ada catatan log aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
