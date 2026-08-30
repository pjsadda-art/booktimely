<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two more per-field visibility flags alongside the existing
 * show_in_appointment, so a business's custom fields (REGO, Make & Model, …)
 * can also appear on the modern invoice/quotation create forms.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('custom_fields')) {
            return;
        }

        Schema::table('custom_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_fields', 'show_in_quotation')) {
                $table->boolean('show_in_quotation')->default(true)->after('show_in_appointment');
            }
            if (!Schema::hasColumn('custom_fields', 'show_in_invoice')) {
                $table->boolean('show_in_invoice')->default(true)->after('show_in_quotation');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('custom_fields')) {
            return;
        }

        Schema::table('custom_fields', function (Blueprint $table) {
            if (Schema::hasColumn('custom_fields', 'show_in_quotation')) {
                $table->dropColumn('show_in_quotation');
            }
            if (Schema::hasColumn('custom_fields', 'show_in_invoice')) {
                $table->dropColumn('show_in_invoice');
            }
        });
    }
};
