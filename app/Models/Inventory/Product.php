<?php

namespace App\Models\Inventory;

class Product extends InventoryModel
{
    protected $table = 'inventory_v2_products';

    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'sub_category_id',
        'brand_id',
        'sub_brand_id',
        'unit_id',
        'cost_price',
        'sale_price',
        'reorder_level',
        'is_openable',
        'description',
        'is_active',
        'business_id',
        'created_by',
    ];

    protected $casts = [
        'is_openable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }

    public function subBrand()
    {
        return $this->belongsTo(SubBrand::class, 'sub_brand_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class, 'product_id', 'id');
    }

    /**
     * Total held across every place. Read from the balance cache rather than
     * summed from the ledger — that is what the cache is for.
     */
    public function totalQuantity(): float
    {
        return round((float) $this->stocks()->sum('quantity'), 4);
    }

    public function isBelowReorderLevel(): bool
    {
        return (float) $this->reorder_level > 0
            && $this->totalQuantity() <= (float) $this->reorder_level;
    }
}
