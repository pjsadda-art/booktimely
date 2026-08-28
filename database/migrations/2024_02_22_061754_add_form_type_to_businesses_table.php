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
        if(!Schema::hasColumn('businesses', 'form_type','layouts'))
        {
            Schema::table('businesses', function (Blueprint $table) {
                if (!Schema::hasColumn('businesses', 'form_type')) {
                    $table->string('form_type')->nullable()->after('is_disable');
                }

                if (!Schema::hasColumn('businesses', 'layouts')) {
                    $table->string('layouts')->nullable()->after('form_type');
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
            $table->dropColumn('form_type');
            $table->dropColumn('layouts');
        });
    }
};
