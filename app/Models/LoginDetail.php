<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip',
        'date',
        'details',
        'type',
        'business',
        'created_by'
    ];

}
