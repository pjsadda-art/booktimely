<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A fourth visibility flag alongside show_in_appointment/show_in_quotation/
 * show_in_invoice, so a field can be shown to staff on the internal
 * appointment form without necessarily being exposed on the public online
 * booking widget, or vice versa. Defaults true so existing fields keep
 * behaving the way the widget already treated them (every field showed,
 * unconditionally, before this flag existed).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('custom_fields')) {
            return;
        }

        Schema::table('custom_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_fields', 'show_on_online_widget')) {
                $table->boolean('show_on_online_widget')->default(true)->after('show_in_invoice');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('custom_fields')) {
            return;
        }

        Schema::table('custom_fields', function (Blueprint $table) {
            if (Schema::hasColumn('custom_fields', 'show_on_online_widget')) {
                $table->dropColumn('show_on_online_widget');
            }
        });
    }
};
