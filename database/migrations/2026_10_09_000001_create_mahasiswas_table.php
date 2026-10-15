<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique();
            $table->string('nama')->index();
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('prodi_id')->nullable()->constrained('prodis')->nullOnDelete();
            $table->foreignId('ut_daerah_id')->nullable()->constrained('ut_daerahs')->nullOnDelete();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::table('absensis', function (Blueprint $table) {
            $table->foreignId('mahasiswa_id')->nullable()->after('pegawai_id')->constrained('mahasiswas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->dropColumn('mahasiswa_id');
        });

        Schema::dropIfExists('mahasiswas');
    }
};
