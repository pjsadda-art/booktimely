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
            if (!Schema::hasColumn('appointment_payments', 'tax_amount')) {
                // adjust precision/scale as needed
                $table->decimal('tax_amount', 10, 2)->default(0)->after('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_payments', function (Blueprint $table) {
            if (Schema::hasColumn('appointment_payments', 'tax_amount')) {
                $table->dropColumn('tax_amount');
            }
        });
    }
};
