<?php

namespace Workdo\PromoCodes\Entities;

use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_percentage',
        'flat_rate',
        'discount_type',
        'once_per_customer',
        'services',
        'use_limit',
        'promo_used',
        'start_date',
        'end_date',
        'customers',
        'business_id',
        'created_by'
    ];

    public static function getPrice($promo_code_id, $service_id){
        if($promo_code_id != 0){

            $promo_code = PromoCode::where('id', $promo_code_id)->first();

            $service = Service::find($service_id);

            $price = $promo_code->discount_type == 1 ? ($service->price * $promo_code->discount_percentage / 100) : ($service->price - $promo_code->flat_rate);


            return $price;
        }
    }
}
