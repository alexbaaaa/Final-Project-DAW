<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAdmin extends Model
{
    protected $connection = 'auth_pgsql';

    protected $table = 'user_admins';

    protected $fillable = [
        'first_name',
        'last_name',
        'password',
        'swimmer_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'swimmer_id' => 'integer',
        ];
    }
}

