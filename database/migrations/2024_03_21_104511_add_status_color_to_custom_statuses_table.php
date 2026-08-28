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
        if(!Schema::hasColumn('custom_statuses', 'status_color'))
        {
            Schema::table('custom_statuses', function (Blueprint $table) {
                if(!Schema::hasColumn('custom_statuses','status_color')){
                    $table->string('status_color')->nullable()->after('title');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_statuses', function (Blueprint $table) {
            $table->dropColumn('status_color');
        });
    }
};
