<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ProposalController::convert() has always created a new Invoice from an
 * accepted quotation, but never recorded which quotation it came from on
 * the invoice side — only the reverse (proposals.converted_invoice_id).
 * This closes that gap so the invoice screen can show a "Converted from
 * Quotation #X" back-link.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'proposal_id')) {
                $table->unsignedBigInteger('proposal_id')->nullable()->after('appointment_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'proposal_id')) {
                $table->dropColumn('proposal_id');
            }
        });
    }
};
