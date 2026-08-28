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
        if (!Schema::hasColumn('appointment_payments', 'appointment_ids')) {
            Schema::table('appointment_payments', function (Blueprint $table) {
                $table->string('appointment_ids')->after('amount')->nullable();
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
        Schema::table('appointment_payments', function (Blueprint $table) {

        });
    }
};
