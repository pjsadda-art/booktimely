<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if(!Schema::hasColumn('custom_fields', 'option'))
        {
            Schema::table('custom_fields', function (Blueprint $table) {
                if (!Schema::hasColumn('custom_fields', 'option')) {
                    $table->longText('option')->nullable()->after('type');
                }
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
        Schema::table('custom_fields', function (Blueprint $table) {

        });
    }
};
