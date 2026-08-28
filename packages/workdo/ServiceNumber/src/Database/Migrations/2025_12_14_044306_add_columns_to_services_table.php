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
        Schema::table('appointment_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointment_payments', 'coupon_amount')) {
                $table->integer('coupon_amount')->after('amount')->default(0);
            }
            if (!Schema::hasColumn('appointment_payments', 'final_amount')) {
                $table->integer('final_amount')->after('coupon_amount')->default(0);
            }
            if (!Schema::hasColumn('appointment_payments', 'promo_code_id')) {
                $table->text('promo_code_id')->nullable()->after('final_amount')->default(null);
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
        Schema::table('', function (Blueprint $table) {

        });
    }
};
