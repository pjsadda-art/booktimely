<?php

namespace App\Models\Inventory;

/**
 * One movement of stock. The record of truth for the whole module.
 *
 * Source and destination are separate polymorphic pairs, which is what lets a
 * transfer be a *single* row rather than a matched pair of out/in rows: the
 * transfer is atomic by construction, and the stock ledger report is a plain
 * select rather than a reconciliation.
 *
 *   inbound   — source null, destination set
 *   outbound  — source set, destination null
 *   transfer  — both set
 *
 * `quantity` is always positive; direction comes from which ends are filled in.
 */
class StockTransaction extends InventoryModel
{
    protected $table = 'inventory_v2_stock_transactions';

    protected $fillable = [
        'product_id',
        'transaction_type',
        'source_entity_type',
        'source_entity_id',
        'destination_entity_type',
        'destination_entity_id',
        'quantity',
        'unit_cost',
        'reference_id',
        'reference_type',
        'remarks',
        'performed_by',
        'business_id',
        'created_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function performer()
    {
        return $this->belongsTo(\App\Models\User::class, 'performed_by', 'id');
    }

    public function sourceName(): string
    {
        return ProductStock::placeLabel($this->source_entity_type, $this->source_entity_id);
    }

    public function destinationName(): string
    {
        return ProductStock::placeLabel($this->destination_entity_type, $this->destination_entity_id);
    }

    public function typeLabel(): string
    {
        $labels = \App\Services\Inventory\StockService::TRANSACTION_TYPES;

        return $labels[$this->transaction_type] ?? ucfirst(str_replace('_', ' ', (string) $this->transaction_type));
    }

    /**
     * Signed change this row represents at one place, for a running balance.
     */
    public function signedQuantityFor(string $entityType, $entityId): float
    {
        $quantity = (float) $this->quantity;
        $delta = 0.0;

        if ($this->destination_entity_type === $entityType && (int) $this->destination_entity_id === (int) $entityId) {
            $delta += $quantity;
        }

        if ($this->source_entity_type === $entityType && (int) $this->source_entity_id === (int) $entityId) {
            $delta -= $quantity;
        }

        return $delta;
    }
}
