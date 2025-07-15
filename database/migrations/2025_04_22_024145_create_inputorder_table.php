<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inputorder', function (Blueprint $table) {
            $table->id(); 
            $table->id_riwayat(); 
            $table->string('nama_pengirim');
            $table->text('alamat_pengirim');
            $table->string('no_telp_pengirim');
            $table->string('nama_penerima');
            $table->string('alamat_penerima');
            $table->string('no_telp_penerima');
            $table->string('jenis_barang');
            $table->string('jenis_layanan');
            $table->string('riwayat');
            $table->integer('berat_barang');
            $table->integer('biaya');
            $table->enum('status', ['menunggu', 'dalam proses', 'dikirim', 'selesai'])->default('dikirim');
            $table->enum('status_pembayaran', ['belum dibayar', 'settlement', 'pending', 'expire', 'cancel', 'deny', 'failure', 'refund'])->default('belum dibayar');
            $table->string('no_resi');
            $table->timestamps();
        });
    }
    // public function up()
    // {
    //     Schema::create('inputorder', function (Blueprint $table) {   
    //         $table->id(); 
    //         $table->string('nama_barang');
    //         $table->text('alamat_asal');
    //         $table->text('alamat_tujuan');
    //         $table->string('penerima');
    //         $table->enum('metode_pengiriman',['pick-up', 'drop-off']);
    //         $table->enum('metode_pengiriman'['pick-up', 'drop-off']);
    //         $table->string('biaya_pengiriman');
    //         $table->string('berat_paket');
    //         $table->date('tanggal_order');
    //         $table->date('tanggal_dikemas');
    //         $table->date('taggal_pengambilan');
    //         $table->date('tanggal_dikirim');
    //         $table->date('tanggal_penerimaan');
    //         $table->enum('status',['dikirim','selesai','dalam proses','dibatalkan', 'menunggu'])->default('menunggu');
    //         $table->timestamps();
    //     });
    // }

    public function down()
    {
        Schema::dropIfExists('inputorder');
    }
};
