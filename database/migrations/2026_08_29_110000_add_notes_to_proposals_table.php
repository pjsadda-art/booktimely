<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Same staff-note field as invoices (2026_08_29_000000_add_notes_to_invoices_table.php),
 * added here for the modern quotation create/edit form.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('proposals')) {
            return;
        }

        Schema::table('proposals', function (Blueprint $table) {
            if (!Schema::hasColumn('proposals', 'notes')) {
                $table->text('notes')->nullable()->after('proposal_template');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('proposals')) {
            return;
        }

        Schema::table('proposals', function (Blueprint $table) {
            if (Schema::hasColumn('proposals', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
