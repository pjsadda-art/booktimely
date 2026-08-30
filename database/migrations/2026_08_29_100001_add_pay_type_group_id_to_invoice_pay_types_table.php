<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoice_pay_types')) {
            return;
        }

        Schema::table('invoice_pay_types', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_pay_types', 'pay_type_group_id')) {
                $table->unsignedBigInteger('pay_type_group_id')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('invoice_pay_types')) {
            return;
        }

        Schema::table('invoice_pay_types', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_pay_types', 'pay_type_group_id')) {
                $table->dropColumn('pay_type_group_id');
            }
        });
    }
};
