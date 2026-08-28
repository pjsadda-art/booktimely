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
        if(!Schema::hasTable('promo_codes'))
        {
            Schema::create('promo_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->integer('discount_percentage');
                $table->integer('flat_rate');
                $table->boolean('discount_type')->default(true);
                $table->boolean('once_per_customer')->default(false);
                $table->longText('services');
                $table->integer('use_limit');
                $table->integer('promo_used')->default(0);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->longText('customers');
                $table->integer('business_id');
                $table->integer('created_by');
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
        Schema::dropIfExists('promo_codes');
    }
};
