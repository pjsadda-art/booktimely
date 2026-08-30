<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * The credit-note "status" (Pending/Partially Used/Fully Used) has always
 * been a plain dropdown a staff member sets by hand — nothing in the app
 * ever actually deducted from a credit note when it was used, because
 * AccountUtility::updateCreditnoteBalance() writes to customers.credit_note_balance,
 * a column that was never created (confirmed: not in the customers table).
 * That call would throw "Unknown column" if it ever ran.
 *
 * remaining_amount is the real, reliable balance this app now tracks per
 * credit note — decremented by InvoiceController::createPayment() when a
 * credit note is redeemed as a payment method. Backfilled to the full
 * `amount` for every existing row, since no note has ever actually been
 * redeemed before this feature existed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_credit_notes')) {
            return;
        }

        Schema::table('customer_credit_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_credit_notes', 'remaining_amount')) {
                $table->decimal('remaining_amount', 15, 2)->nullable()->after('amount');
            }
        });

        DB::table('customer_credit_notes')
            ->whereNull('remaining_amount')
            ->update(['remaining_amount' => DB::raw('amount')]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('customer_credit_notes')) {
            return;
        }

        Schema::table('customer_credit_notes', function (Blueprint $table) {
            if (Schema::hasColumn('customer_credit_notes', 'remaining_amount')) {
                $table->dropColumn('remaining_amount');
            }
        });
    }
};
