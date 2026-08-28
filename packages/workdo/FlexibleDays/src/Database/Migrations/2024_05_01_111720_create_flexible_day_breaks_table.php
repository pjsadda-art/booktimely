<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('flexible_day_breaks')) {
            Schema::create('flexible_day_breaks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('flexible_days_id');
                $table->string('break_hours')->nullable();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->foreign('flexible_days_id')->references('id')->on('flexible_days')->onDelete('cascade');
                $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flexible_day_breaks');
    }
};
