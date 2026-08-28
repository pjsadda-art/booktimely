<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The salon-specific half of a customer record.
 *
 * Identity and contact details stay on the `users` row; everything here is
 * profile state the front desk owns.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'wallet_balance')) {
                // Cached running total. The ledger is the truth; see WalletTransaction.
                $table->decimal('wallet_balance', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('customers', 'loyalty_enabled')) {
                $table->boolean('loyalty_enabled')->default(1);
            }
            if (!Schema::hasColumn('customers', 'loyalty_points')) {
                $table->integer('loyalty_points')->default(0);
            }
            if (!Schema::hasColumn('customers', 'is_high_risk')) {
                // Manual staff flag. Rule 1 of the deposit engine, and it always wins.
                $table->boolean('is_high_risk')->default(0);
            }
            if (!Schema::hasColumn('customers', 'is_walkin')) {
                // Suppresses reminders entirely.
                $table->boolean('is_walkin')->default(0);
            }
            if (!Schema::hasColumn('customers', 'notification_preference')) {
                // sms | email | both — gates outbound messaging.
                $table->string('notification_preference', 16)->default('both');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            foreach (
                [
                    'wallet_balance',
                    'loyalty_enabled',
                    'loyalty_points',
                    'is_high_risk',
                    'is_walkin',
                    'notification_preference',
                ] as $column
            ) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
