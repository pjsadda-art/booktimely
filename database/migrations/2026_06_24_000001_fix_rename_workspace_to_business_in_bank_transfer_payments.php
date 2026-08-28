<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bank_transfer_payments')) {
            Schema::table('bank_transfer_payments', function (Blueprint $table) {
                if (Schema::hasColumn('bank_transfer_payments', 'workspace') && !Schema::hasColumn('bank_transfer_payments', 'business')) {
                    $table->renameColumn('workspace', 'business');
                } elseif (!Schema::hasColumn('bank_transfer_payments', 'business')) {
                    $table->integer('business')->default(0)->after('created_by');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bank_transfer_payments')) {
            Schema::table('bank_transfer_payments', function (Blueprint $table) {
                if (Schema::hasColumn('bank_transfer_payments', 'business') && !Schema::hasColumn('bank_transfer_payments', 'workspace')) {
                    $table->renameColumn('business', 'workspace');
                }
            });
        }
    }
};
