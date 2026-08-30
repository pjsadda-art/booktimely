<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Same storage shape as appointments.custom_field — a JSON label => value
 * map — so the modern invoice/quotation create forms can collect the
 * business's custom fields the same way the booking panel already does.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'custom_field')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->longText('custom_field')->nullable()->after('notes');
            });
        }

        if (Schema::hasTable('proposals') && !Schema::hasColumn('proposals', 'custom_field')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->longText('custom_field')->nullable()->after('notes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'custom_field')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('custom_field');
            });
        }

        if (Schema::hasTable('proposals') && Schema::hasColumn('proposals', 'custom_field')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->dropColumn('custom_field');
            });
        }
    }
};
