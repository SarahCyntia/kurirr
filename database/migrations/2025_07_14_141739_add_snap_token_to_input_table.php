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
    Schema::table('inputorder', function (Blueprint $table) {
        $table->string('snap_token')->nullable()->after('status_pembayaran');
    });
}

public function down()
{
    Schema::table('inputorder', function (Blueprint $table) {
        $table->dropColumn('snap_token');
    });
}

    /**
     * Reverse the migrations.
     */
    // public function down(): void
    // {
    //     Schema::table('input', function (Blueprint $table) {
    //         //
    //     });
    // }
};
