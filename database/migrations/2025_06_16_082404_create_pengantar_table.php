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
    Schema::create('pengantar', function (Blueprint $table) {
        $table->id_riwayat();
        $table->unsignedBigInteger('id');
        $table->unsignedBigInteger('id_kurir');
        $table->text('deskripsi');
        $table->timestamps();

        // Optional: relasi ke tabel orders
        $table->foreign('id_order')->references('id')->on('input')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengantar');
    }
};
