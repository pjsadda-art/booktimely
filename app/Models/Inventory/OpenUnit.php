<?php

namespace App\Models\Inventory;

/**
 * A container that has been opened, or a unit given away as a sample.
 *
 * The one genuinely domain-specific piece of this module. A salon opens a bottle
 * to use across many clients: it leaves sellable stock the moment it is opened,
 * but is not *consumed* until it is empty. So the lifecycle has two steps —
 * open (deduct one unit, log it) and close (mark it finished) — and the stock
 * deduction happens at the first, not the second.
 *
 * Samples share the table and differ only in `kind`, because they behave
 * identically: stock leaves immediately, and the log row is the audit trail.
 */
class OpenUnit extends InventoryModel
{
    public const OPEN_UNIT = 'open_unit';
    public const SAMPLE = 'sample';

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    protected $table = 'inventory_v2_open_units';

    protected $fillable = [
        'product_id',
        'entity_type',
        'entity_id',
        'kind',
        'status',
        'quantity',
        'opened_at',
        'closed_at',
        'opened_by',
        'closed_by',
        'stock_transaction_id',
        'remarks',
        'business_id',
        'created_by',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function openedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'opened_by', 'id');
    }

    public function placeName(): string
    {
        return ProductStock::placeLabel($this->entity_type, $this->entity_id);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }
}
