<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'note',
        'staff_id',
        'business_id',
        'created_by',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id', 'id');
    }
}
