<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'event_name',
        'description',
        'event_date',
        'event_start_date',
        'event_end_date',
        'event_type',
        'categories',
        'day_scope',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'event_start_date' => 'date',
            'event_end_date' => 'date',
        ];
    }
}
