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
            $table->enum('metode_pengiriman',['pick-up', 'drop-off']);
            // $table->enum('metode_pengiriman'['pick-up', 'drop-off']);
            $table->string('biaya_pengiriman');
            $table->string('berat_paket');
            $table->date('tanggal_order');
            $table->date('tanggal_dikemas');
            $table->date('taggal_pengambilan');
            $table->date('tanggal_dikirim');
            $table->date('tanggal_penerimaan');
            $table->enum('status',['dikirim','selesai','dalam proses','dibatalkan', 'menunggu'])->default('menunggu');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inputorder');
    }
};
