<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_v2_purchase_items';

    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'received_quantity',
        'unit_cost',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /** Still to be delivered on this line. */
    public function outstanding(): float
    {
        return max(0, round((float) $this->quantity - (float) $this->received_quantity, 4));
    }
}
