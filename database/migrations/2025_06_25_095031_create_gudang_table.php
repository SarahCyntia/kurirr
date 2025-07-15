<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('gudang', function (Blueprint $table) {
        $table->id();

        // No resi wajib untuk identifikasi
        $table->string('no_resi')->unique();

        // Status pengolahan di gudang
        $table->string('status')->default('menunggu');

        // Lokasi teks (nama gudang atau deskripsi)
        $table->string('lokasi_sekarang')->nullable();
        $table->string('gudang_asal')->nullable();
        $table->string('gudang_tujuan')->nullable();

        // Relasi ke kurir (yang mengambil dari gudang)
        $table->foreignId('kurir_id')->nullable()->constrained('users')->nullOnDelete();

        // Timestamps penting
        $table->timestamp('waktu_diterima_gudang')->nullable();
        $table->timestamp('waktu_diambil_kurir')->nullable();

        // Koordinat lokasi real-time (jika diaktifkan)
        $table->decimal('latitude', 10, 7)->nullable();
        $table->decimal('longitude', 10, 7)->nullable();

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gudang');
    }
};
