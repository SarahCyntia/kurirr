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
        Schema::create('pengiriman', function (Blueprint $table) {
            $table->id();
            $table->string('kode_resi'); // Kode Resi
            $table->unsignedBigInteger('id_pengirim');
            $table->unsignedBigInteger('id_kurir');
            $table->string('alamat_asal'); // Lokasi asal
            $table->string('alamat_tujuan'); // Lokasi tujuan
            $table->float('kapasitas')->default(0); // Kapasitas dalam kg
            $table->enum('status_pengiriman', ['dijadwalkan', 'dalam_perjalanan', 'terkirim'])->default('dijadwalkan');
            $table->date('tanggal_kirim'); // Waktu pengirirman
            $table->date('tanggal_terima'); // penerima
            $table->timestamps(); // created_at & updated_at

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengiriman');
    }
};