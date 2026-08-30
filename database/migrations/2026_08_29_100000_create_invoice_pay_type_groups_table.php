<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Super Admin-managed master list a tenant's own pay types (Cash, Debit
 * Card, AMEX, ...) roll up into, so "how much came in as EFTPOS vs. Cash"
 * is answerable without guessing from free-text pay type names — same
 * protected-defaults pattern as industries (2026_08_28_100000_create_
 * industries_table.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoice_pay_type_groups')) {
            Schema::create('invoice_pay_type_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(1);
                $table->boolean('is_system_default')->default(0);
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();
            });
        }

        $defaults = [
            ['id' => 1, 'name' => 'Cash', 'description' => 'Physical cash payments'],
            ['id' => 2, 'name' => 'EFTPOS', 'description' => 'Debit, credit and other card-present/card-not-present payments'],
            ['id' => 3, 'name' => 'Bank', 'description' => 'Direct bank transfer / deposit'],
            ['id' => 4, 'name' => 'Other', 'description' => 'Anything that does not fit the above groups'],
        ];

        foreach ($defaults as $default) {
            $existing = DB::table('invoice_pay_type_groups')->where('name', $default['name'])->first();

            if ($existing) {
                DB::table('invoice_pay_type_groups')->where('id', $existing->id)->update([
                    'is_system_default' => 1,
                    'is_active' => 1,
                    'updated_at' => now(),
                ]);
                continue;
            }

            DB::table('invoice_pay_type_groups')->insert([
                'id' => $default['id'],
                'name' => $default['name'],
                'description' => $default['description'],
                'is_active' => 1,
                'is_system_default' => 1,
                'created_by' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_pay_type_groups');
    }
};
