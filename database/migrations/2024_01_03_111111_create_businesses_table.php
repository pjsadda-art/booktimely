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
        if (!Schema::hasTable('businesses')) {
            Schema::create('businesses', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('status')->default('active');
                $table->string('slug')->nullable();
                $table->integer('is_disable')->default(1);
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
