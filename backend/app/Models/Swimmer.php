<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Swimmer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'age',
        'category',
        'gender',
    ];
}
