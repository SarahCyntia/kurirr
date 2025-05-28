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
        Schema::table('inputorder', function (Blueprint $table) {
            $table->string('ekspedisi'); // jne / tiki / pos
            $table->integer('nilai')->nullable(); // rating 1-5
            $table->text('ulasan')->nullable();
            $table->string('riwayat_pengiriman')->nullable();

            // Relasi lokasi
            $table->foreignId('asal_provinsi_id')->constrained('provinces');
            $table->foreignId('asal_kota_id')->constrained('cities');
            $table->foreignId('tujuan_provinsi_id')->constrained('provinces');
            $table->foreignId('tujuan_kota_id')->constrained('cities');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inputorder', function (Blueprint $table) {
            Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
        });
    }
};
