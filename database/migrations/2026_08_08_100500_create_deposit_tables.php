<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The deposit audit trail and the invoice a deposit is collected against.
 *
 * The invoice is created eagerly when a deposit is raised, so the customer-facing
 * checkout page only ever looks an invoice up and never creates one. Both the
 * gateway path and the on-the-spot path settle the same invoice, which is what
 * keeps a deposit visible to financial reporting however it was collected.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('appointment_deposit_logs')) {
            Schema::create('appointment_deposit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('appointment_id')->nullable();
                $table->string('common_number', 64)->nullable();
                // requested_auto, requested_manual, paid, forfeited, refunded,
                // link_resent, link_resend_failed
                $table->string('event_type', 32);
                $table->decimal('amount', 12, 2)->nullable();
                $table->unsignedBigInteger('old_status_id')->nullable();
                $table->unsignedBigInteger('new_status_id')->nullable();
                $table->unsignedBigInteger('staff_id')->nullable();
                $table->text('reason')->nullable();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'common_number'], 'deposit_logs_group_index');
                $table->index(['business_id', 'appointment_id'], 'deposit_logs_appointment_index');
            });
        }

        if (!Schema::hasTable('deposit_invoices')) {
            Schema::create('deposit_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number', 64)->nullable();
                $table->string('common_number', 64)->nullable();
                $table->unsignedBigInteger('appointment_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();   // users.id
                // 'deposit' for the up-front payment, 'appointment' for the
                // booking's own bill. The lockout rule scopes strictly to the
                // second: matching any invoice would make a paid deposit
                // instantly lock every deposit action.
                $table->string('invoice_type', 16)->default('deposit');
                $table->string('status', 16)->default('unpaid');         // unpaid | partial | paid | cancelled
                $table->decimal('total', 12, 2)->default(0);
                $table->decimal('paid_total', 12, 2)->default(0);
                $table->string('token', 64)->nullable();                 // customer-facing pay link
                $table->date('issue_date')->nullable();
                $table->date('due_date')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->unique('token');
                $table->index(['business_id', 'common_number'], 'deposit_invoices_group_index');
                $table->index(['business_id', 'customer_id'], 'deposit_invoices_customer_index');
            });
        }

        if (!Schema::hasTable('deposit_invoice_items')) {
            Schema::create('deposit_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('deposit_invoice_id');
                $table->string('description');
                $table->decimal('quantity', 10, 2)->default(1);
                $table->decimal('price', 12, 2)->default(0);
                $table->timestamps();

                $table->index('deposit_invoice_id', 'deposit_invoice_items_invoice_index');
            });
        }

        if (!Schema::hasTable('deposit_invoice_payments')) {
            Schema::create('deposit_invoice_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('deposit_invoice_id');
                $table->decimal('amount', 12, 2)->default(0);
                // cash, payid, bank_transfer, eftpos, manual_card, or a gateway name
                $table->string('method', 32)->nullable();
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->date('payment_date')->nullable();
                $table->unsignedBigInteger('received_by')->nullable();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index('deposit_invoice_id', 'deposit_invoice_payments_invoice_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_invoice_payments');
        Schema::dropIfExists('deposit_invoice_items');
        Schema::dropIfExists('deposit_invoices');
        Schema::dropIfExists('appointment_deposit_logs');
    }
};
