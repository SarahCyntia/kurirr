<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('nama_pelanggan');
            $table->text('produk');
            $table->decimal('total_harga', 10, 2);
            $table->date('tanggal_pesan')->default(now());
            $table->string('pengirim');
            $table->string('penerima');
            $table->string('alamat_pengiriman');
            $table->enum('status', ['Dikemas', 'Dikirim', 'Selesai', 'Dibatalkan'])->default('Dikirim');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};