<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointment_payments', function (Blueprint $table) {
            if (Schema::hasColumn('appointment_payments', 'promo_code_id'))
            {
                $table->string('promo_code_id')->default(0)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_payments', function (Blueprint $table) {
            if (Schema::hasColumn('appointment_payments', 'promo_code_id'))
            {
                $table->string('promo_code_id')->default(0)->nullable()->change();
            }
        });
    }
};
