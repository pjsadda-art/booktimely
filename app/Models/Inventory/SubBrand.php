<?php

namespace App\Models\Inventory;

class SubBrand extends InventoryModel
{
    protected $table = 'inventory_v2_sub_brands';

    protected $fillable = ['brand_id', 'name', 'business_id', 'created_by'];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
}
