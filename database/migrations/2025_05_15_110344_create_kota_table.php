<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKotaTable extends Migration
{
    /**
     * Menjalankan migrasi - membuat tabel kotas
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kota', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('provinsi');
            $table->string('kode_pos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Membalikkan migrasi - menghapus tabel kotas
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kota');
    }
}

