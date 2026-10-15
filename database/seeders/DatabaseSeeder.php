<?php

namespace Database\Seeders;

use App\Models\HariLibur;
use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\Prodi;
use App\Models\Twibbon;
use App\Models\Unit;
use App\Models\UtDaerah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Pengaturan::DEFAULT as $k => $v) {
            Pengaturan::firstOrCreate(['kunci' => $k], ['nilai' => $v]);
        }

        $units = ['Bagian Akademik', 'Bagian Keuangan', 'Bagian Kepegawaian', 'Bagian Umum', 'Pusat Teknologi Informasi'];
        foreach ($units as $u) {
            Unit::firstOrCreate(['nama' => $u]);
        }

        $prodis = [
            'Manajemen', 'Akuntansi', 'Ilmu Administrasi Negara', 'Ilmu Hukum', 'Sistem Informasi',
            'Pendidikan Guru Sekolah Dasar (PGSD)', 'Pendidikan Bahasa Inggris', 'Matematika', 'Biologi', 'Agribisnis',
        ];
        foreach ($prodis as $p) {
            Prodi::firstOrCreate(['nama' => $p]);
        }

        $daerah = [
            'UT Jakarta', 'UT Bogor', 'UT Bandung', 'UT Semarang', 'UT Yogyakarta', 'UT Surabaya',
            'UT Medan', 'UT Palembang', 'UT Makassar', 'UT Denpasar', 'UT Pontianak', 'UT Banjarmasin',
        ];
        foreach ($daerah as $d) {
            UtDaerah::firstOrCreate(['nama' => $d]);
        }

        // Admin pertama. PIN awal: 123456 (segera ganti di Panel Admin > Pengaturan).
        Pegawai::firstOrCreate(['nip' => '000000000000000001'], [
            'nama' => 'Administrator Sistem',
            'jabatan' => 'Administrator',
            'unit_id' => Unit::where('nama', 'Pusat Teknologi Informasi')->value('id'),
            'is_admin' => true,
            'pin' => Hash::make('123456'),
        ]);

        // Data Pegawai (dari CSV jika ada, atau contoh)
        $csvPath = database_path('data/Daftar_Pegawai.csv');
        if (file_exists($csvPath)) {
            \Illuminate\Support\Facades\Artisan::call('pegawai:impor', ['file' => $csvPath]);
        } else {
            $contoh = [
                ['198503122010011005', 'Budi Santoso', 'Staf Administrasi', 'Bagian Akademik'],
                ['199001152015022003', 'Siti Rahmawati', 'Kepala Sub Bagian', 'Bagian Keuangan'],
                ['198807202012011002', 'Agus Pratama', 'Analis Kepegawaian', 'Bagian Kepegawaian'],
                ['199205102018032001', 'Dewi Lestari', 'Pranata Komputer', 'Pusat Teknologi Informasi'],
            ];
            foreach ($contoh as [$nip, $nama, $jabatan, $unit]) {
                Pegawai::firstOrCreate(['nip' => $nip], [
                    'nama' => $nama,
                    'jabatan' => $jabatan,
                    'unit_id' => Unit::where('nama', $unit)->value('id'),
                ]);
            }
        }

        HariLibur::firstOrCreate(['tanggal' => '2026-12-25'], ['keterangan' => 'Hari Raya Natal']);
        HariLibur::firstOrCreate(['tanggal' => '2027-01-01'], ['keterangan' => 'Tahun Baru Masehi']);

        // Twibbon contoh (dibuat oleh: php artisan twibbon:contoh)
        if (file_exists(public_path('uploads/twibbon/contoh.png')) && Twibbon::count() === 0) {
            Twibbon::create(['nama' => 'Twibbon Contoh', 'file' => 'uploads/twibbon/contoh.png', 'aktif' => true]);
        }
    }
}
