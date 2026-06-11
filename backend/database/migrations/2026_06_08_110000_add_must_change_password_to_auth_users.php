<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     */
    protected $connection = 'auth_pgsql';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['user_admins', 'users'] as $tableName) {
            Schema::connection('auth_pgsql')->table($tableName, function (Blueprint $table) use ($tableName): void {
                if (!Schema::connection('auth_pgsql')->hasColumn($tableName, 'must_change_password')) {
                    $table->boolean('must_change_password')->default(true);
                }
            });
        }

        $this->backfillUserAdmins();
        $this->backfillPortalUsers();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['user_admins', 'users'] as $tableName) {
            if (Schema::connection('auth_pgsql')->hasColumn($tableName, 'must_change_password')) {
                Schema::connection('auth_pgsql')->table($tableName, function (Blueprint $table): void {
                    $table->dropColumn('must_change_password');
                });
            }
        }
    }

    private function backfillUserAdmins(): void
    {
        DB::connection('auth_pgsql')
            ->table('user_admins')
            ->orderBy('id')
            ->get(['id', 'alias', 'role', 'password'])
            ->each(function ($userAdmin): void {
                $defaultPassword = $userAdmin->role === 'root' ? '1234' : (string) $userAdmin->alias;

                DB::connection('auth_pgsql')
                    ->table('user_admins')
                    ->where('id', $userAdmin->id)
                    ->update([
                        'must_change_password' => Hash::check($defaultPassword, (string) $userAdmin->password),
                    ]);
            });
    }

    private function backfillPortalUsers(): void
    {
        DB::connection('auth_pgsql')
            ->table('users')
            ->orderBy('id')
            ->get(['id', 'alias', 'password'])
            ->each(function ($user): void {
                $mustChangePassword = $user->alias
                    ? Hash::check((string) $user->alias, (string) $user->password)
                    : true;

                DB::connection('auth_pgsql')
                    ->table('users')
                    ->where('id', $user->id)
                    ->update([
                        'must_change_password' => $mustChangePassword,
                    ]);
            });
    }
};
