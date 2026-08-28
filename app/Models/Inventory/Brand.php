<?php

namespace App\Models\Inventory;

class Brand extends InventoryModel
{
    protected $table = 'inventory_v2_brands';

    protected $fillable = ['name', 'business_id', 'created_by'];

    public function subBrands()
    {
        return $this->hasMany(SubBrand::class, 'brand_id', 'id');
    }
}
