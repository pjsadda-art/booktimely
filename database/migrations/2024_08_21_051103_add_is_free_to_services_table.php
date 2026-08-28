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
        if(!Schema::hasColumn('services', 'is_free'))
        {
            Schema::table('services', function (Blueprint $table) {
                if(!Schema::hasColumn('services','is_free')){
                    $table->string('is_free')->nullable()->after('price')->comment('0 => paid, 1 => free');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('is_free');
        });
    }
};
