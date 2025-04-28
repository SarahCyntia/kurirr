<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('inputorder', function (Blueprint $table) {
            $table->id(); // No Order
            $table->string('nama_barang');
            // $table->foreignId('id_pelanggan')->constrained('pelanggan')->onDelete('cascade');
            $table->text('alamat_asal');
            $table->text('alamat_tujuan');
            $table->string('penerima');
            $table->string('biaya_pengiriman');
            $table->enum('status',['dikirim','selesai','dalam proses','dibatalkan'])->default('dalam proses');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inputorder');
    }
};
