<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The real accounting Invoice (Workdo\Invoice\Entities\Invoice) a deposit was
 * mirrored into, when the Invoice module is active.
 *
 * The local deposit_invoices row stays the source of truth for the
 * customer-facing pay link and the profile's Deposits tab; this column just
 * remembers where the same money was recorded for financial reporting, so a
 * second on-the-spot payment on the same booking never creates a duplicate.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('deposit_invoices')) {
            return;
        }

        Schema::table('deposit_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('deposit_invoices', 'synced_invoice_id')) {
                $table->unsignedBigInteger('synced_invoice_id')->nullable()->after('appointment_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('deposit_invoices')) {
            return;
        }

        Schema::table('deposit_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('deposit_invoices', 'synced_invoice_id')) {
                $table->dropColumn('synced_invoice_id');
            }
        });
    }
};
