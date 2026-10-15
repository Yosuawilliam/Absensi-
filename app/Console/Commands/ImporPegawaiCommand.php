<?php

namespace App\Console\Commands;

use App\Models\Pegawai;
use App\Models\Unit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImporPegawaiCommand extends Command
{
    protected $signature = 'pegawai:impor {file?} {--sync : Hapus pegawai lama/dummy yang tidak ada di file CSV (kecuali admin)}';
    protected $description = 'Impor atau perbarui data pegawai dari file CSV';

    public function handle(): int
    {
        $filePath = $this->argument('file') ?: base_path('database/data/Daftar_Pegawai.csv');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Membaca file: {$filePath}...");

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->error("Gagal membuka file.");
            return Command::FAILURE;
        }

        // Cari header row
        $headerFound = false;
        $barisData = [];

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if (!$headerFound) {
                foreach ($row as $col) {
                    if (strtolower(trim($col)) === 'nip') {
                        $headerFound = true;
                        break;
                    }
                }
                continue;
            }

            if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) {
                continue;
            }

            // Expected format: No, NIP, Nama, Jabatan, Unit
            if (count($row) >= 5) {
                $nip = trim((string)$row[1]);
                $nama = trim((string)$row[2]);
                $jabatan = trim((string)$row[3]);
                $unit = trim((string)$row[4]);
            } else {
                // Format: NIP, Nama, Jabatan, Unit
                $nip = trim((string)($row[0] ?? ''));
                $nama = trim((string)($row[1] ?? ''));
                $jabatan = trim((string)($row[2] ?? ''));
                $unit = trim((string)($row[3] ?? ''));
            }

            if ($nip !== '' && $nama !== '') {
                $barisData[] = [
                    'nip' => $nip,
                    'nama' => $nama,
                    'jabatan' => $jabatan ?: null,
                    'unit' => $unit ?: null,
                ];
            }
        }
        fclose($handle);

        $totalBaris = count($barisData);
        $this->info("Ditemukan {$totalBaris} baris data pegawai yang valid. Memproses ke database...");

        DB::beginTransaction();
        try {
            // Cache unit untuk performa optimal
            $unitMap = Unit::pluck('id', 'nama')->toArray();

            // Kumpulkan semua unit baru terlebih dahulu
            $unitsToCreate = [];
            foreach ($barisData as $item) {
                if ($item['unit'] && !isset($unitMap[$item['unit']]) && !isset($unitsToCreate[$item['unit']])) {
                    $unitsToCreate[$item['unit']] = true;
                }
            }

            foreach (array_keys($unitsToCreate) as $unitNama) {
                $u = Unit::firstOrCreate(['nama' => $unitNama]);
                $unitMap[$unitNama] = $u->id;
            }

            $tambah = 0;
            $ubah = 0;

            // Ambil semua pegawai yang sudah ada berdasarkan NIP
            $nips = array_column($barisData, 'nip');
            $existingPegawais = Pegawai::whereIn('nip', $nips)->get()->keyBy('nip');

            $barisJson = [];

            foreach ($barisData as $item) {
                $unitId = $item['unit'] && isset($unitMap[$item['unit']]) ? $unitMap[$item['unit']] : null;

                $barisJson[] = [
                    'nip' => $item['nip'],
                    'nama' => $item['nama'],
                    'jabatan' => $item['jabatan'],
                    'unit' => $item['unit'],
                ];

                if (isset($existingPegawais[$item['nip']])) {
                    $p = $existingPegawais[$item['nip']];
                    $p->nama = $item['nama'];
                    $p->jabatan = $item['jabatan'];
                    $p->unit_id = $unitId;
                    $p->save();
                    $ubah++;
                } else {
                    Pegawai::create([
                        'nip' => $item['nip'],
                        'nama' => $item['nama'],
                        'jabatan' => $item['jabatan'],
                        'unit_id' => $unitId,
                        'is_admin' => false,
                        'aktif' => true,
                    ]);
                    $tambah++;
                }
            }

            if ($this->option('sync')) {
                $deleted = Pegawai::whereNotIn('nip', $nips)->where('is_admin', false)->delete();
                if ($deleted > 0) {
                    $this->warn("Menghapus {$deleted} data pegawai lama/dummy yang tidak ada di file CSV.");
                }
            }

            DB::commit();

            // Perbarui juga scratch/pegawai_data.json agar sinkron
            $jsonPath = base_path('scratch/pegawai_data.json');
            file_put_contents($jsonPath, json_encode($barisJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->info("Berhasil mengimpor data pegawai!");
            $this->table(
                ['Kategori', 'Jumlah'],
                [
                    ['Total Data di File', $totalBaris],
                    ['Pegawai Baru Ditambahkan', $tambah],
                    ['Pegawai Lama Diperbarui', $ubah],
                    ['Total Pegawai Sekarang', Pegawai::count()],
                    ['Total Unit Sekarang', Unit::count()],
                ]
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Gagal melakukan impor: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
