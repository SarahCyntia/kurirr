<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeNoResiToStringInInput extends Migration
{
    public function up()
    {
        Schema::table('inputorder', function (Blueprint $table) {
            $table->string('no_resi', 50)->change(); // Ubah jadi string
        });
    }

    public function down()
    {
        Schema::table('inputorder', function (Blueprint $table) {
            $table->bigInteger('no_resi')->change(); // Balik ke integer jika perlu rollback
        });
    }
}
