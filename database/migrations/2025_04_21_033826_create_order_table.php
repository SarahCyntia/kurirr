<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pelanggan');
            $table->string('nama_toko');
            $table->string('produk');
            $table->string('total_harga');
            $table->string('pengirim');
            $table->string('penerima');
            $table->string('alamat');
            $table->enum('status', ['dikirim', 'selesai', 'dalam proses', 'dibatalkan'])->default('dikirim');
            $table->timestamps(); // created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('order');
    }
}
