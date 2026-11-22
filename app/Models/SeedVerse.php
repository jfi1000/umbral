<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeedVerse extends Model
{
    protected $fillable = [
        'book',
        'chapter',
        'verse',
        'reference',
        'text',
    ];
}
