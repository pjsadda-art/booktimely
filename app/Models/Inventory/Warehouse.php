<?php

namespace App\Models\Inventory;

/**
 * A storage-only place. Stock can sit here, but no customer is served here —
 * that is what distinguishes it from a Location.
 */
class Warehouse extends InventoryModel
{
    protected $table = 'inventory_v2_warehouses';

    protected $fillable = [
        'name', 'address', 'city', 'postcode', 'is_active', 'business_id', 'created_by',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
