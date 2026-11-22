<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyVerse extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'visitor_hash',
        'verse_reference',
        'verse_text',
        'verse_book',
        'verse_chapter',
        'verse_number',
        'shown_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'shown_at' => 'date',
            'created_at' => 'datetime',
        ];
    }
}
