<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Time extends Model
{
    protected $fillable = [
        'swimmer_id',
        'test_type',
        'time',
        'date',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
