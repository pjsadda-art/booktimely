<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplateLang extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'lang',
        'subject',
        'content',
        'module_name',
        // Was "variables\n        " — a stray newline inside the string meant it
        // never matched the column, so every mass-assigned `variables` value was
        // silently dropped and template editors showed an empty token list.
        'variables',
    ];
}
