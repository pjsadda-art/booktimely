<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndustryChangeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'old_industry_id',
        'new_industry_id',
        'changed_by',
    ];
}
