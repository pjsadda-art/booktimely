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
        if (!Schema::hasTable('flexible_staff_hours')) {
            Schema::create('flexible_staff_hours', function (Blueprint $table) {
                $table->id();
                $table->string('day_name');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('day_off')->default('off');
                $table->string('break_hours')->nullable();
                $table->unsignedBigInteger('staff_id');
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();
                $table->foreign('staff_id')->references('user_id')->on('staff')->onDelete('cascade');
                $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('flexible_staff_hours');
    }
};
