<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('custom_fields')) {
            return;
        }

        Schema::table('custom_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_fields', 'show_in_appointment')) {
                // Existing custom fields already appear on the appointment form
                // today, so default them all to on rather than hiding them
                // the moment this column exists.
                $table->boolean('show_in_appointment')->default(1)->after('option');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('custom_fields')) {
            return;
        }

        Schema::table('custom_fields', function (Blueprint $table) {
            if (Schema::hasColumn('custom_fields', 'show_in_appointment')) {
                $table->dropColumn('show_in_appointment');
            }
        });
    }
};
