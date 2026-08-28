<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if(!Schema::hasColumn('appointments', 'notes'))
        {
            Schema::table('appointments', function (Blueprint $table) {
                $table->longText('notes')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('appointments', function (Blueprint $table) {});
    }
};
