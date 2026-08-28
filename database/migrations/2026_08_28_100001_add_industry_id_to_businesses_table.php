<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('businesses', 'industry_id')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->unsignedBigInteger('industry_id')->default(1)->after('id');
            });
        }

        // Backfill existing tenants onto the default "General Booking" industry.
        DB::table('businesses')->whereNull('industry_id')->orWhere('industry_id', 0)->update(['industry_id' => 1]);

        if (Schema::hasTable('industries')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->foreign('industry_id')->references('id')->on('industries')->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['industry_id']);
            $table->dropColumn('industry_id');
        });
    }
};
