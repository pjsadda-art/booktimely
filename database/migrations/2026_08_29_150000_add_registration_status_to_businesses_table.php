<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('businesses', 'registration_status')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->string('registration_status')->default('approved')->after('industry_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('registration_status');
        });
    }
};
