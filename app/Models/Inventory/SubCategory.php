<?php

namespace App\Models\Inventory;

class SubCategory extends InventoryModel
{
    protected $table = 'inventory_v2_sub_categories';

    protected $fillable = ['category_id', 'name', 'business_id', 'created_by'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
