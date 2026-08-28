<?php

namespace App\Models\Inventory;

use App\Models\Location;

/**
 * How much of a product is held at one place, right now.
 *
 * A cache the ledger can rebuild — never the record of truth. It exists so
 * "how much is here" is one indexed read instead of a sum over every movement
 * ever made.
 *
 * A "place" is polymorphic: either a `location` (a customer-facing branch) or a
 * `warehouse` (storage only), identified by the entity_type/entity_id pair.
 */
class ProductStock extends InventoryModel
{
    public const LOCATION = 'location';
    public const WAREHOUSE = 'warehouse';

    protected $table = 'inventory_v2_product_stocks';

    protected $fillable = [
        'product_id',
        'entity_type',
        'entity_id',
        'quantity',
        'business_id',
        'created_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * The two kinds of place stock can be held at.
     */
    public static function entityTypes(): array
    {
        return [
            self::LOCATION => __('Location'),
            self::WAREHOUSE => __('Warehouse'),
        ];
    }

    /**
     * Every place stock can be held at, as "type:id" => label.
     *
     * One flat list because the UI picks a *place*, not a type and then an id;
     * splitting it into two dropdowns invites a mismatched pair.
     */
    public static function placeOptions($businessId = null, $createdBy = null): array
    {
        $businessId = $businessId ?: getActiveBusiness();
        $createdBy = $createdBy ?: creatorId();

        $options = [];

        foreach (
            Location::where('business_id', $businessId)->where('created_by', $createdBy)->orderBy('name')->get()
            as $location
        ) {
            $options[self::LOCATION . ':' . $location->id] = __('Location') . ' — ' . $location->name;
        }

        foreach (
            Warehouse::forTenant($businessId, $createdBy)->where('is_active', 1)->orderBy('name')->get()
            as $warehouse
        ) {
            $options[self::WAREHOUSE . ':' . $warehouse->id] = __('Warehouse') . ' — ' . $warehouse->name;
        }

        return $options;
    }

    /**
     * Split a "type:id" place key, rejecting anything that is not a real place.
     *
     * @return array{0:string,1:int}|null
     */
    public static function parsePlace($key): ?array
    {
        if (empty($key) || !str_contains((string) $key, ':')) {
            return null;
        }

        [$type, $id] = explode(':', (string) $key, 2);

        if (!in_array($type, [self::LOCATION, self::WAREHOUSE], true) || !is_numeric($id)) {
            return null;
        }

        return [$type, (int) $id];
    }

    /**
     * A human label for one place.
     */
    public static function placeLabel(?string $type, $id): string
    {
        if (empty($type) || empty($id)) {
            return '—';
        }

        if ($type === self::WAREHOUSE) {
            return __('Warehouse') . ' — ' . (Warehouse::find($id)->name ?? '#' . $id);
        }

        return __('Location') . ' — ' . (Location::find($id)->name ?? '#' . $id);
    }

    public function placeName(): string
    {
        return self::placeLabel($this->entity_type, $this->entity_id);
    }
}
