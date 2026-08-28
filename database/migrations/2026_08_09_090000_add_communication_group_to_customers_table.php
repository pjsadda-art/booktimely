<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Communication Group: per-customer SMS/email opt-in, validated against
 * the contact details on the linked `users` row.
 *
 * Defaults to enabled (1), not the disabled default a bare opt-in field would
 * normally get. Every existing customer already receives deposit SMS/email
 * whenever a mobile/email is on file (see Customer::reachableChannels()); a
 * 0 default here would silently stop that for the entire existing customer
 * base until each one was opted back in by hand.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'communication_sms')) {
                $table->boolean('communication_sms')->default(1);
            }
            if (!Schema::hasColumn('customers', 'communication_email')) {
                $table->boolean('communication_email')->default(1);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            foreach (['communication_sms', 'communication_email'] as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
