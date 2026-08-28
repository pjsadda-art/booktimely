<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Status identity and the four per-business switches.
 *
 * Titles are editable, so nothing may key off them. `slug` is the stable
 * identity every feature resolves a status by; `is_standard` marks the rows in
 * the fixed spine so a tenant cannot delete the status a deposit depends on.
 *
 * The four switches (calendar, kanban, SMS, email) already existed in part;
 * this fills in the two that did not.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('custom_statuses')) {
            return;
        }

        Schema::table('custom_statuses', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_statuses', 'slug')) {
                $table->string('slug', 64)->nullable()->after('title');
            }
            if (!Schema::hasColumn('custom_statuses', 'is_standard')) {
                $table->boolean('is_standard')->default(0)->after('slug');
            }
            if (!Schema::hasColumn('custom_statuses', 'show_on_appointment_calendar')) {
                $table->boolean('show_on_appointment_calendar')->default(1);
            }
            if (!Schema::hasColumn('custom_statuses', 'show_in_kanban')) {
                $table->boolean('show_in_kanban')->default(1);
            }
            if (!Schema::hasColumn('custom_statuses', 'send_sms')) {
                $table->boolean('send_sms')->default(0);
            }
            if (!Schema::hasColumn('custom_statuses', 'send_email')) {
                $table->boolean('send_email')->default(0);
            }
        });

        // Backfill slugs from existing titles so already-configured tenants
        // resolve by slug from the first request after deploy.
        foreach (DB::table('custom_statuses')->whereNull('slug')->get() as $status) {
            DB::table('custom_statuses')
                ->where('id', $status->id)
                ->update(['slug' => Str::slug($status->title ?: ('status-' . $status->id))]);
        }

        if (!$this->indexExists('custom_statuses', 'custom_statuses_slug_index')) {
            Schema::table('custom_statuses', function (Blueprint $table) {
                $table->index(['business_id', 'created_by', 'slug'], 'custom_statuses_slug_index');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('custom_statuses')) {
            return;
        }

        Schema::table('custom_statuses', function (Blueprint $table) {
            if ($this->indexExists('custom_statuses', 'custom_statuses_slug_index')) {
                $table->dropIndex('custom_statuses_slug_index');
            }
            foreach (['slug', 'is_standard', 'show_on_appointment_calendar'] as $column) {
                if (Schema::hasColumn('custom_statuses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Schema::hasIndex() does not exist on this Laravel version, so ask the
     * connection directly rather than assuming.
     */
    protected function indexExists(string $table, string $index): bool
    {
        try {
            return count(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index])) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};
