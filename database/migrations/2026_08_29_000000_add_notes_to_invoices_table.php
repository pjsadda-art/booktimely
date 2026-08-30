<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A free-text staff note on the invoice, entered on the modern invoice
 * create/edit form — separate from the customer-profile notes feature,
 * this is just a single field on the invoice itself.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'notes')) {
                $table->text('notes')->nullable()->after('shipping_display');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
