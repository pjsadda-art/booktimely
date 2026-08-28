<?php

namespace App\Models\Inventory;

class Unit extends InventoryModel
{
    protected $table = 'inventory_v2_units';

    protected $fillable = ['name', 'short_name', 'business_id', 'created_by'];
}
