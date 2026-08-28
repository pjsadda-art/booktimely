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
        if (!Schema::hasTable('staff_shifts')) {
            Schema::create('staff_shifts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('staff_id');
                $table->unsignedBigInteger('location_id');
                $table->unsignedTinyInteger('weekday'); // 0 (Sun) - 6 (Sat)
                $table->enum('shift_type', ['continuous', 'end_dated', 'casual']);
                $table->time('start_time');
                $table->time('end_time');
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->date('specific_date')->nullable();
                $table->enum('status', ['active', 'superseded', 'cancelled'])->default('active');
                $table->unsignedBigInteger('replaces_shift_id')->nullable();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
                $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
                $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
                $table->foreign('replaces_shift_id')->references('id')->on('staff_shifts')->onDelete('set null');

                $table->index(['staff_id', 'weekday', 'status']);
                $table->index(['staff_id', 'specific_date']);
                $table->index(['location_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_shifts');
    }
};
