<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $t) {
            $t->id();
            $t->string('nama')->unique();
            $t->timestamps();
        });

        Schema::create('prodis', function (Blueprint $t) {
            $t->id();
            $t->string('nama')->unique();
            $t->timestamps();
        });

        Schema::create('ut_daerahs', function (Blueprint $t) {
            $t->id();
            $t->string('nama')->unique();
            $t->timestamps();
        });

        Schema::create('pegawais', function (Blueprint $t) {
            $t->id();
            $t->string('nip')->unique();
            $t->string('nama')->index();
            $t->string('jabatan')->nullable();
            $t->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $t->boolean('is_admin')->default(false);
            $t->string('pin')->nullable();
            $t->unsignedTinyInteger('pin_gagal')->default(0);
            $t->timestamp('terkunci_sampai')->nullable();
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });

        Schema::create('absensis', function (Blueprint $t) {
            $t->id();
            $t->enum('tipe', ['pegawai', 'mahasiswa'])->index();
            $t->foreignId('pegawai_id')->nullable()->constrained('pegawais')->cascadeOnDelete();
            $t->date('tanggal')->index();

            // Data mahasiswa (diisi setiap kali absen masuk)
            $t->string('nama')->nullable();
            $t->string('nim')->nullable()->index();
            $t->string('no_hp')->nullable();
            $t->string('email')->nullable();
            $t->foreignId('prodi_id')->nullable()->constrained('prodis')->nullOnDelete();
            $t->foreignId('ut_daerah_id')->nullable()->constrained('ut_daerahs')->nullOnDelete();

            $t->time('jam_masuk')->nullable();
            $t->time('jam_pulang')->nullable();
            $t->enum('status', ['hadir', 'terlambat', 'tidak_hadir'])->default('hadir');

            $t->string('foto_masuk')->nullable();
            $t->string('foto_pulang')->nullable();
            $t->decimal('lat_masuk', 10, 7)->nullable();
            $t->decimal('lng_masuk', 10, 7)->nullable();
            $t->decimal('lat_pulang', 10, 7)->nullable();
            $t->decimal('lng_pulang', 10, 7)->nullable();
            $t->foreignId('lokasi_id')->nullable();

            $t->boolean('dikoreksi')->default(false);
            $t->text('catatan_koreksi')->nullable();
            $t->timestamps();

            $t->unique(['pegawai_id', 'tanggal']);
            $t->unique(['nim', 'tanggal']);
        });

        Schema::create('twibbons', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('file');
            $t->boolean('aktif')->default(false);
            $t->date('tampil_mulai')->nullable();
            $t->date('tampil_selesai')->nullable();
            $t->timestamps();
        });

        Schema::create('lokasis', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->decimal('lat', 10, 7);
            $t->decimal('lng', 10, 7);
            $t->unsignedInteger('radius')->default(100);
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });

        Schema::create('hari_liburs', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal')->unique();
            $t->string('keterangan');
            $t->timestamps();
        });

        Schema::create('pengaturans', function (Blueprint $t) {
            $t->string('kunci')->primary();
            $t->text('nilai')->nullable();
        });

        Schema::create('log_aktivitas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pegawai_id')->nullable()->constrained('pegawais')->nullOnDelete();
            $t->string('pelaku')->nullable();
            $t->string('aksi');
            $t->text('keterangan')->nullable();
            $t->json('data_lama')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['log_aktivitas', 'pengaturans', 'hari_liburs', 'lokasis', 'twibbons', 'absensis', 'pegawais', 'ut_daerahs', 'prodis', 'units'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
