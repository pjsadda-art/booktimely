<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoice_pay_types')) {
            Schema::create('invoice_pay_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->text('description')->nullable();
                $table->tinyInteger('is_active')->default(1);
                $table->integer('created_by')->default(0);
                $table->integer('business_id')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_pay_types');
    }
};
