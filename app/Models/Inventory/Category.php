<?php

namespace App\Models\Inventory;

class Category extends InventoryModel
{
    protected $table = 'inventory_v2_categories';

    protected $fillable = ['name', 'description', 'business_id', 'created_by'];

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class, 'category_id', 'id');
    }
}
