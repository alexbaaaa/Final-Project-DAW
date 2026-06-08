<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalUser extends Model
{
    protected $connection = 'auth_pgsql';

    protected $table = 'users';

    protected $fillable = [
        'alias',
        'first_name',
        'last_name',
        'birth_date',
        'user_type',
        'password',
        'swimmer_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'password' => 'hashed',
            'swimmer_id' => 'integer',
        ];
    }
}
