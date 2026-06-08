<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Swimmer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'category',
        'gender',
    ];

    protected $appends = [
        'age',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }
}
