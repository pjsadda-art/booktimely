<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Wallet, loyalty, notes and the outbound/inbound message log.
 *
 * All four are append-only by design: the customer profile is the page staff
 * open when something has already gone wrong, and a ledger you can replay is
 * worth more there than a cached number you have to trust.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id');           // customers.id
                $table->string('type', 16);                          // credit | debit
                $table->decimal('amount', 12, 2);
                $table->decimal('balance_after', 12, 2)->default(0);
                $table->string('reference')->nullable();             // invoice, refund, top-up
                $table->string('description')->nullable();
                $table->unsignedBigInteger('created_by_user')->nullable();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'customer_id'], 'wallet_tx_scope_index');
            });
        }

        if (!Schema::hasTable('loyalty_transactions')) {
            Schema::create('loyalty_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id');
                $table->string('type', 16);                          // earn | redeem | adjust
                $table->integer('points')->default(0);
                $table->integer('balance_after')->default(0);
                $table->string('description')->nullable();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'customer_id'], 'loyalty_tx_scope_index');
            });
        }

        if (!Schema::hasTable('customer_service_visits')) {
            // Service mode: one counter per customer per configured service.
            Schema::create('customer_service_visits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('service_id');
                $table->unsignedInteger('visits')->default(0);
                $table->boolean('free_visit_available')->default(0);
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->unique(
                    ['customer_id', 'service_id', 'business_id'],
                    'customer_service_visits_unique'
                );
            });
        }

        if (!Schema::hasTable('customer_notes')) {
            Schema::create('customer_notes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id');
                $table->longText('note');
                $table->unsignedBigInteger('staff_id')->nullable();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'customer_id'], 'customer_notes_scope_index');
            });
        }

        if (!Schema::hasTable('sms_logs')) {
            // Every message to and from a customer's mobile. The deposit resend
            // action reads this back to report whether the SMS actually left,
            // rather than reporting an optimistic success.
            Schema::create('sms_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();   // users.id of the customer
                $table->string('mobile_no', 32)->nullable();
                $table->string('direction', 8)->default('out');      // out | in
                $table->longText('message')->nullable();
                $table->string('event', 64)->nullable();             // deposit_request, reminder, …
                $table->string('status', 16)->default('queued');     // sent | failed | queued | received
                $table->string('provider', 32)->nullable();
                $table->string('provider_message_id')->nullable();
                $table->text('error')->nullable();
                $table->unsignedBigInteger('appointment_id')->nullable();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'user_id'], 'sms_logs_scope_index');
                $table->index(['business_id', 'mobile_no'], 'sms_logs_mobile_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('customer_notes');
        Schema::dropIfExists('customer_service_visits');
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('wallet_transactions');
    }
};
