<?php

namespace App\Models\Inventory;

class Vendor extends InventoryModel
{
    protected $table = 'inventory_v2_vendors';

    protected $fillable = [
        'name', 'contact_name', 'email', 'phone', 'address', 'is_active', 'business_id', 'created_by',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
