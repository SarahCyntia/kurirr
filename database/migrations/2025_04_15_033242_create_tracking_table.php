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
        Schema::create('tracking', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('status'); // Status terkini dari pengiriman
            $table->string('location'); // Lokasi terkini dari pengiriman
            $table->timestamp('timestamp'); // Waktu saat status diperbarui
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tracking');
    }
};
