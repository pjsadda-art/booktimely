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
        if(!Schema::hasColumn('businesses', 'theme_color'))
        {
            Schema::table('businesses', function (Blueprint $table) {
                if (!Schema::hasColumn('businesses', 'theme_color')) {
                    $table->string('theme_color')->nullable()->after('layouts');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('theme_color');
        });
    }
};
