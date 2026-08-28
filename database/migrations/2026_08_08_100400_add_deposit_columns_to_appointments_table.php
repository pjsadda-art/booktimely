<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deposits live on the appointment, written to every row of the booking group.
 *
 * There is no deposit table: a booking can only ever carry one deposit, and
 * re-raising overwrites the previous attempt while `appointment_deposit_logs`
 * keeps the history. If instalments are ever needed this has to become its own
 * table — retrofitting that is the expensive path, so it is called out here.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('appointments')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'deposit_required')) {
                $table->boolean('deposit_required')->default(0);
            }
            if (!Schema::hasColumn('appointments', 'deposit_percentage')) {
                // Null when a fixed amount was used instead.
                $table->decimal('deposit_percentage', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('appointments', 'deposit_amount')) {
                $table->decimal('deposit_amount', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('appointments', 'deposit_status')) {
                // none -> pending -> paid | forfeited | refunded. A string, not
                // an enum, so a new terminal state needs no schema migration.
                $table->string('deposit_status', 16)->default('none');
            }
            if (!Schema::hasColumn('appointments', 'deposit_invoice_id')) {
                $table->unsignedBigInteger('deposit_invoice_id')->nullable();
            }
            if (!Schema::hasColumn('appointments', 'deposit_confirm_status_id')) {
                // Resolved and stored at request time so payment never re-infers it.
                $table->unsignedBigInteger('deposit_confirm_status_id')->nullable();
            }
            if (!Schema::hasColumn('appointments', 'deposit_requested_by')) {
                // Null means the automatic rule engine raised it. That null is
                // meaningful — it selects the message template later.
                $table->unsignedBigInteger('deposit_requested_by')->nullable();
            }
            if (!Schema::hasColumn('appointments', 'deposit_message')) {
                $table->text('deposit_message')->nullable();
            }
            if (!Schema::hasColumn('appointments', 'deposit_method')) {
                $table->string('deposit_method', 32)->nullable();
            }
            if (!Schema::hasColumn('appointments', 'deposit_payment_notes')) {
                $table->text('deposit_payment_notes')->nullable();
            }
            if (!Schema::hasColumn('appointments', 'deposit_paid_at')) {
                $table->timestamp('deposit_paid_at')->nullable();
            }
            if (!Schema::hasColumn('appointments', 'deposit_forfeit_reason')) {
                $table->text('deposit_forfeit_reason')->nullable();
            }
            if (!Schema::hasColumn('appointments', 'deposit_refund_reference')) {
                $table->string('deposit_refund_reference')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('appointments')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            foreach (
                [
                    'deposit_required',
                    'deposit_percentage',
                    'deposit_amount',
                    'deposit_status',
                    'deposit_invoice_id',
                    'deposit_confirm_status_id',
                    'deposit_requested_by',
                    'deposit_message',
                    'deposit_method',
                    'deposit_payment_notes',
                    'deposit_paid_at',
                    'deposit_forfeit_reason',
                    'deposit_refund_reference',
                ] as $column
            ) {
                if (Schema::hasColumn('appointments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
