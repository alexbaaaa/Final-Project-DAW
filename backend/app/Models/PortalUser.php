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
        'is_enabled',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_enabled' => 'boolean',
            'must_change_password' => 'boolean',
            'password' => 'hashed',
            'swimmer_id' => 'integer',
        ];
    }

    public function defaultPassword(): string
    {
        return (string) $this->alias;
    }
}
