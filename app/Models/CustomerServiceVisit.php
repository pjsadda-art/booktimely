<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Service-mode loyalty: a per-customer, per-service visit counter. After
 * `loyalty_visits_required` visits to the configured service, the free-visit
 * flag is raised for redemption.
 */
class CustomerServiceVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'service_id',
        'visits',
        'free_visit_available',
        'business_id',
        'created_by',
    ];

    protected $casts = [
        'free_visit_available' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }
}
