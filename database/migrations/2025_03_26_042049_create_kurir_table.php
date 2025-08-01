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
    Schema::create('kurir', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->string('jenis_kendaraan')->nullable();
        $table->text('penilaian')->nullable();
        $table->text('alamat')->nullable();
        $table->enum('status', ['aktif', 'nonaktif'])->default('nonaktif');
        $table->timestamps();
        $table->foreignId('kurir_id')->nullable()->constrained('kurir')->nullOnDelete();
    });

}
    // public function up(): void
    // {
    //     Schema::create('kurir', function (Blueprint $table) {
    //         $table->id();
    //         $table->timestamps();
    //     });
    // }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kurir');
    }
};
