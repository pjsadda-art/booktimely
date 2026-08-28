<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCardFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_card_id',
        'file_path',
        'original_name',
    ];

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class);
    }
}
