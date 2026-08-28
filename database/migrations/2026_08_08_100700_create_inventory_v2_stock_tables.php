<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inventory V2 — balances, the ledger, purchasing, and open units.
 *
 * Two ideas carry the module: stock is held per (product, place), where a place
 * is polymorphic (a customer-facing location or a storage warehouse); and every
 * movement is a ledger row, with the balance table a cache the ledger can
 * rebuild. Both are written inside one database transaction, always.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_v2_product_stocks')) {
            Schema::create('inventory_v2_product_stocks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('entity_type', 16);              // location | warehouse
                $table->unsignedBigInteger('entity_id');
                $table->decimal('quantity', 12, 4)->default(0); // fractional units; never below zero
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                // What makes the balance a safe upsert target.
                $table->unique(
                    ['product_id', 'entity_type', 'entity_id', 'business_id'],
                    'inv2_stock_place_unique'
                );
            });
        }

        if (!Schema::hasTable('inventory_v2_stock_transactions')) {
            Schema::create('inventory_v2_stock_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                // A string, not a database enum: the source system had to alter
                // its enum twice to add movement types, and each alter was a
                // schema migration for what is really application vocabulary.
                // Validated in StockService::TRANSACTION_TYPES instead.
                $table->string('transaction_type', 32);
                // Source and destination as separate polymorphic pairs, so a
                // transfer is one row rather than a matched out/in pair. That
                // makes transfers atomic by construction.
                $table->string('source_entity_type', 16)->nullable();
                $table->unsignedBigInteger('source_entity_id')->nullable();
                $table->string('destination_entity_type', 16)->nullable();
                $table->unsignedBigInteger('destination_entity_id')->nullable();
                $table->decimal('quantity', 12, 4);             // always positive; direction is source/destination
                $table->decimal('unit_cost', 12, 4)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('reference_type', 32)->nullable();
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('performed_by')->nullable();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'product_id'], 'inv2_tx_product_index');
                $table->index(['business_id', 'transaction_type'], 'inv2_tx_type_index');
                $table->index(['business_id', 'created_at'], 'inv2_tx_date_index');
            });
        }

        if (!Schema::hasTable('inventory_v2_purchases')) {
            Schema::create('inventory_v2_purchases', function (Blueprint $table) {
                $table->id();
                $table->string('purchase_number', 64)->nullable();
                $table->unsignedBigInteger('vendor_id')->nullable();
                // Where the goods land. Polymorphic like everything else.
                $table->string('entity_type', 16)->default('warehouse');
                $table->unsignedBigInteger('entity_id')->nullable();
                // draft (editable) -> confirmed (locked, awaiting delivery) ->
                // received (stock updated). Confirm and receive stay separate;
                // collapsing them is what makes partial deliveries impossible.
                $table->string('status', 16)->default('draft');
                $table->date('purchase_date')->nullable();
                $table->date('expected_date')->nullable();
                $table->decimal('total', 12, 2)->default(0);
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'status'], 'inv2_purchases_status_index');
            });
        }

        if (!Schema::hasTable('inventory_v2_purchase_items')) {
            Schema::create('inventory_v2_purchase_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_id');
                $table->unsignedBigInteger('product_id');
                $table->decimal('quantity', 12, 4)->default(0);
                // Tracked separately so a delivery can be received in parts.
                $table->decimal('received_quantity', 12, 4)->default(0);
                $table->decimal('unit_cost', 12, 4)->default(0);
                $table->timestamps();

                $table->index('purchase_id', 'inv2_purchase_items_purchase_index');
            });
        }

        if (!Schema::hasTable('inventory_v2_open_units')) {
            // A salon opens a bottle to use across many clients: it leaves
            // sellable stock the moment it is opened, but is not consumed until
            // it is empty. Two-step lifecycle, one log row per container.
            Schema::create('inventory_v2_open_units', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('entity_type', 16);
                $table->unsignedBigInteger('entity_id');
                $table->string('kind', 16)->default('open_unit');  // open_unit | sample
                $table->string('status', 16)->default('open');     // open | closed
                $table->decimal('quantity', 12, 4)->default(1);
                $table->timestamp('opened_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->unsignedBigInteger('opened_by')->nullable();
                $table->unsignedBigInteger('closed_by')->nullable();
                $table->unsignedBigInteger('stock_transaction_id')->nullable();
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('created_by')->default(0);
                $table->timestamps();

                $table->index(['business_id', 'kind', 'status'], 'inv2_open_units_state_index');
                $table->index(['business_id', 'product_id'], 'inv2_open_units_product_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_v2_open_units');
        Schema::dropIfExists('inventory_v2_purchase_items');
        Schema::dropIfExists('inventory_v2_purchases');
        Schema::dropIfExists('inventory_v2_stock_transactions');
        Schema::dropIfExists('inventory_v2_product_stocks');
    }
};
