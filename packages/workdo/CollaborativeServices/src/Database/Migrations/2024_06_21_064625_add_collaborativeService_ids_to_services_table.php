<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'service_type')) {
                $table->string('service_type')->nullable();
            }
            if (!Schema::hasColumn('services', 'collaborative_service_id')) {
                $table->string('collaborative_service_id')->nullable();
            }
            if (!Schema::hasColumn('services', 'duration')) {
                $table->string('duration')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {

        });
    }
};
