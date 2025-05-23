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
    Schema::create('riwayat', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('id_order');
        $table->text('deskripsi');
        $table->timestamps();

        // Optional: relasi ke tabel orders
        // $table->foreign('id_order')->references('id')->on('input')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat');
    }
};
