<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $q = LogAktivitas::orderByDesc('id');

        if ($cari = trim((string) $request->query('q'))) {
            $q->where(fn ($w) => $w->where('aksi', 'like', "%{$cari}%")
                ->orWhere('keterangan', 'like', "%{$cari}%")
                ->orWhere('pelaku', 'like', "%{$cari}%"));
        }

        return view('admin.log', ['logs' => $q->paginate(25)->withQueryString()]);
    }
}
