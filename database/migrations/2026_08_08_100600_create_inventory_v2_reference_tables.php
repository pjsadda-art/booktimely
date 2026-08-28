<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inventory V2 — reference data.
 *
 * Conventional lookups, all tenancy-scoped. Products live here rather than in
 * the `services` catalogue on purpose: a bottle of shampoo is stock, a haircut
 * is a bookable service, and conflating them makes both harder to reason about.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_v2_categories')) {
            Schema::create('inventory_v2_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'created_by'], 'inv2_categories_scope_index');
            });
        }

        if (!Schema::hasTable('inventory_v2_sub_categories')) {
            Schema::create('inventory_v2_sub_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id');
                $table->string('name');
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'category_id'], 'inv2_sub_categories_scope_index');
            });
        }

        if (!Schema::hasTable('inventory_v2_brands')) {
            Schema::create('inventory_v2_brands', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'created_by'], 'inv2_brands_scope_index');
            });
        }

        if (!Schema::hasTable('inventory_v2_sub_brands')) {
            Schema::create('inventory_v2_sub_brands', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('brand_id');
                $table->string('name');
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'brand_id'], 'inv2_sub_brands_scope_index');
            });
        }

        if (!Schema::hasTable('inventory_v2_units')) {
            Schema::create('inventory_v2_units', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('short_name', 16)->nullable();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'created_by'], 'inv2_units_scope_index');
            });
        }

        if (!Schema::hasTable('inventory_v2_warehouses')) {
            Schema::create('inventory_v2_warehouses', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('address')->nullable();
                $table->string('city')->nullable();
                $table->string('postcode', 32)->nullable();
                $table->boolean('is_active')->default(1);
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'created_by'], 'inv2_warehouses_scope_index');
            });
        }

        if (!Schema::hasTable('inventory_v2_vendors')) {
            Schema::create('inventory_v2_vendors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('contact_name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone', 32)->nullable();
                $table->text('address')->nullable();
                $table->boolean('is_active')->default(1);
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'created_by'], 'inv2_vendors_scope_index');
            });
        }

        if (!Schema::hasTable('inventory_v2_products')) {
            Schema::create('inventory_v2_products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('sku', 64)->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->unsignedBigInteger('sub_category_id')->nullable();
                $table->unsignedBigInteger('brand_id')->nullable();
                $table->unsignedBigInteger('sub_brand_id')->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->decimal('cost_price', 12, 4)->default(0);   // drives the valuation report
                $table->decimal('sale_price', 12, 4)->default(0);
                $table->decimal('reorder_level', 12, 4)->default(0);
                $table->boolean('is_openable')->default(0);         // can be decanted; see open units
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(1);
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'created_by'], 'inv2_products_scope_index');
                $table->index(['business_id', 'sku'], 'inv2_products_sku_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_v2_products');
        Schema::dropIfExists('inventory_v2_vendors');
        Schema::dropIfExists('inventory_v2_warehouses');
        Schema::dropIfExists('inventory_v2_units');
        Schema::dropIfExists('inventory_v2_sub_brands');
        Schema::dropIfExists('inventory_v2_brands');
        Schema::dropIfExists('inventory_v2_sub_categories');
        Schema::dropIfExists('inventory_v2_categories');
    }
};
