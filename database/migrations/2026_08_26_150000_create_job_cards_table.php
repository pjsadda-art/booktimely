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
        if (!Schema::hasTable('job_cards')) {
            Schema::create('job_cards', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('appointment_id');
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
                $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('job_card_files')) {
            Schema::create('job_card_files', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('job_card_id');
                $table->string('file_path');
                $table->string('original_name')->nullable();
                $table->timestamps();

                $table->foreign('job_card_id')->references('id')->on('job_cards')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_card_files');
        Schema::dropIfExists('job_cards');
    }
};
