<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared base for the Inventory V2 tables.
 *
 * Exists for one reason: every row in this module is scoped by the tenancy pair,
 * and a query that filters on only one half of it is the single most common way
 * a tenant sees another tenant's data. Having one scope means no screen has to
 * remember both column names.
 */
abstract class InventoryModel extends Model
{
    use HasFactory;

    /**
     * Scope to the acting tenant. Reads and writes both use it.
     */
    public function scopeForTenant($query, $businessId = null, $createdBy = null)
    {
        return $query
            ->where($this->getTable() . '.business_id', $businessId ?: getActiveBusiness())
            ->where($this->getTable() . '.created_by', $createdBy ?: creatorId());
    }

    /**
     * Stamp the tenancy pair on a new row.
     */
    public function applyTenant($businessId = null, $createdBy = null): static
    {
        $this->business_id = $businessId ?: getActiveBusiness();
        $this->created_by = $createdBy ?: creatorId();

        return $this;
    }
}
