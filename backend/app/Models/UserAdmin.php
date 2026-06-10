<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAdmin extends Model
{
    public const ROLE_ROOT = 'root';

    public const ROLE_MASTER = 'master';

    public const ROLE_ADMIN = 'admin';

    protected $connection = 'auth_pgsql';

    protected $table = 'user_admins';

    protected $fillable = [
        'alias',
        'role',
        'password',
        'is_enabled',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'must_change_password' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function creatableRoles(): array
    {
        return [
            self::ROLE_MASTER,
            self::ROLE_ADMIN,
        ];
    }

    public function isRoot(): bool
    {
        return $this->role === self::ROLE_ROOT;
    }

    public function isMaster(): bool
    {
        return $this->role === self::ROLE_MASTER;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function canAccess(string $permission): bool
    {
        if ($this->isRoot()) {
            return true;
        }

        if ($this->isMaster()) {
            return in_array($permission, [
                'home',
                'manage-swimmers',
                'manage-events',
                'manage-calendar',
                'manage-training-groups',
                'manage-times',
                'manage-app-users',
                'view-admin-users',
            ], true);
        }

        if ($this->isAdmin()) {
            return in_array($permission, [
                'home',
                'manage-events',
                'manage-calendar',
                'manage-training-groups',
                'manage-times',
            ], true);
        }

        return false;
    }

    public function canDeleteAdminUser(UserAdmin $target): bool
    {
        if (!$this->isRoot() || $target->isRoot() || (int) $target->id === (int) $this->id) {
            return false;
        }

        return true;
    }

    public function canToggleAdminUser(UserAdmin $target): bool
    {
        return $this->isRoot() && ($target->isMaster() || $target->isAdmin());
    }

    public function defaultPassword(): string
    {
        return $this->isRoot() ? '1234' : (string) $this->alias;
    }
}
