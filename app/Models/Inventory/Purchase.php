<?php

namespace App\Models\Inventory;

/**
 * A purchase order, in three states.
 *
 *   draft     — editable, no effect on stock
 *   confirmed — locked, awaiting delivery, still no effect on stock
 *   received  — stock updated, possibly over several partial receipts
 *
 * Confirm and receive stay separate deliberately: collapsing them into one step
 * is what makes partial deliveries impossible to represent.
 */
class Purchase extends InventoryModel
{
    public const DRAFT = 'draft';
    public const CONFIRMED = 'confirmed';
    public const RECEIVED = 'received';

    protected $table = 'inventory_v2_purchases';

    protected $fillable = [
        'purchase_number',
        'vendor_id',
        'entity_type',
        'entity_id',
        'status',
        'purchase_date',
        'expected_date',
        'total',
        'remarks',
        'business_id',
        'created_by',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    public function isEditable(): bool
    {
        return $this->status === self::DRAFT;
    }

    public function destinationName(): string
    {
        return ProductStock::placeLabel($this->entity_type, $this->entity_id);
    }

    /**
     * Whether anything is still outstanding on this order.
     */
    public function hasOutstanding(): bool
    {
        foreach ($this->items as $item) {
            if ((float) $item->received_quantity < (float) $item->quantity) {
                return true;
            }
        }

        return false;
    }

    public function recalculateTotal(): void
    {
        $this->total = round(
            (float) $this->items()->selectRaw('SUM(quantity * unit_cost) as total')->value('total'),
            2
        );
        $this->save();
    }

    public static function statuses(): array
    {
        return [
            self::DRAFT => __('Draft'),
            self::CONFIRMED => __('Confirmed'),
            self::RECEIVED => __('Received'),
        ];
    }
}
