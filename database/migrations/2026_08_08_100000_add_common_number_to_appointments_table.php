<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Booking groups.
 *
 * A customer booking three services produces three appointment rows sharing one
 * `common_number`. Money and status apply to the group, so every grouped write
 * has to be able to find its siblings in one indexed read.
 *
 * Existing rows are backfilled with their own id as a group of one rather than
 * left null: a null group is the trap where "where common_number = null" gets
 * rewritten to "is null" and mass-updates every ungrouped row.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('appointments')) {
            return;
        }

        if (!Schema::hasColumn('appointments', 'common_number')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('common_number', 64)->nullable()->after('id');
                $table->index(['business_id', 'common_number'], 'appointments_group_index');
            });
        }

        // Backfill: every pre-existing appointment becomes its own group.
        DB::table('appointments')
            ->whereNull('common_number')
            ->update(['common_number' => DB::raw("CONCAT('APT-', id)")]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('appointments', 'common_number')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropIndex('appointments_group_index');
                $table->dropColumn('common_number');
            });
        }
    }
};
