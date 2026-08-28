<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'is_standard',
        'business_id',
        'created_by',
        'status_color',
        'send_sms',
        'send_email',
        'show_in_kanban',
        'show_on_appointment_calendar',
    ];

    /**
     * The fixed spine. Titles are editable by the business, so nothing may key
     * off them — every feature resolves a status by slug instead.
     *
     * @var array<string,array{title:string,color:string}>
     */
    public const STANDARD = [
        'pending' => ['title' => 'Pending', 'color' => 'f6c436'],
        'confirmed' => ['title' => 'Confirmed', 'color' => '21c9b0'],
        'reconfirmed' => ['title' => 'Reconfirmed', 'color' => '0080b6'],
        'arrived' => ['title' => 'Arrived', 'color' => '5c6bc0'],
        'completed' => ['title' => 'Completed', 'color' => '27a93d'],
        'cancelled' => ['title' => 'Cancelled', 'color' => 'f04c43'],
        'no-show' => ['title' => 'No Show', 'color' => 'a969ba'],
        'deleted' => ['title' => 'Deleted', 'color' => '9e9e9e'],
        'sms-replied-no' => ['title' => 'SMS Replied No', 'color' => 'df3e9d'],
        'deposit-pending' => ['title' => 'Deposit Pending', 'color' => 'fa9c30'],
    ];

    /**
     * Statuses the business must not delete, because other features resolve
     * them by slug and would have nowhere to move an appointment to.
     */
    public const PROTECTED_SLUGS = ['deposit-pending', 'confirmed', 'cancelled', 'no-show'];

    public function scopeForTenant($query, $businessId, $createdBy)
    {
        return $query->where('business_id', $businessId)->where('created_by', $createdBy);
    }

    /**
     * Resolve one standard status by slug, creating it if this tenant has never
     * had it. Callers get a row back or null only when the tenant is unknown.
     */
    public static function findBySlug(string $slug, $businessId, $createdBy): ?CustomStatus
    {
        if (empty($businessId)) {
            return null;
        }

        $status = self::forTenant($businessId, $createdBy)->where('slug', $slug)->first();

        if (!empty($status)) {
            return $status;
        }

        if (!isset(self::STANDARD[$slug])) {
            return null;
        }

        // A tenant configured before this slug existed still needs the row: match
        // an existing same-titled status rather than creating a duplicate.
        $byTitle = self::forTenant($businessId, $createdBy)
            ->whereNull('slug')
            ->where('title', self::STANDARD[$slug]['title'])
            ->first();

        if (!empty($byTitle)) {
            $byTitle->slug = $slug;
            $byTitle->is_standard = 1;
            $byTitle->save();

            return $byTitle;
        }

        return self::create([
            'title' => self::STANDARD[$slug]['title'],
            'slug' => $slug,
            'is_standard' => 1,
            'status_color' => self::STANDARD[$slug]['color'],
            'send_sms' => 0,
            'send_email' => 0,
            'show_in_kanban' => 1,
            'show_on_appointment_calendar' => 1,
            'business_id' => $businessId,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Create any missing standard statuses for a tenant. Idempotent, so it is
     * safe to call on business setup and again from a settings screen.
     */
    public static function ensureStandard($businessId, $createdBy): void
    {
        foreach (array_keys(self::STANDARD) as $slug) {
            self::findBySlug($slug, $businessId, $createdBy);
        }
    }

    /**
     * A unique slug for a business-authored status title.
     */
    public static function uniqueSlug(string $title, $businessId, $createdBy, $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'status';
        $slug = $base;
        $suffix = 2;

        while (
            self::forTenant($businessId, $createdBy)
                ->where('slug', $slug)
                ->when(!empty($ignoreId), function ($query) use ($ignoreId) {
                    $query->where('id', '!=', $ignoreId);
                })
                ->exists()
        ) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    public static function icon()
    {
        $icon = [
            'ti ti-loader',
            'ti ti-shield-check',
            'ti ti-check',
            'ti ti-circle-x	',
            'ti ti-star',
            'ti ti-home',
            "ti ti-alert-circle",
            "ti ti-calendar-event",
            "ti ti-check",
            "ti ti-truck-delivery",
            "ti ti-ban",
            "ti ti-thumb-up",
        ];

        return $icon;
    }
}
