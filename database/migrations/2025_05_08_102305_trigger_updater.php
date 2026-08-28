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
        if(Schema::hasTable('business_holidays')){
            Schema::table('business_holidays', function (Blueprint $table) {
                // Empty migration to trigger updater
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_holidays', function (Blueprint $table) {
            // No rollback needed
        });
    }
};
