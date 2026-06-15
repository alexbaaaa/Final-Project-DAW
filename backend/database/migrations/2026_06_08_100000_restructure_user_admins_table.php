<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
        Schema::connection('auth_pgsql')->table('user_admins', function (Blueprint $table) {
            if (!Schema::connection('auth_pgsql')->hasColumn('user_admins', 'alias')) {
                $table->string('alias', 100)->nullable();
            }

            if (!Schema::connection('auth_pgsql')->hasColumn('user_admins', 'role')) {
                $table->string('role', 20)->nullable();
            }

            if (!Schema::connection('auth_pgsql')->hasColumn('user_admins', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true);
            }
        });

        $this->backfillAdminUsers();
        $this->createRootUser();

        foreach (['first_name', 'last_name', 'swimmer_id'] as $column) {
            if (Schema::connection('auth_pgsql')->hasColumn('user_admins', $column)) {
                Schema::connection('auth_pgsql')->table('user_admins', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }

        DB::connection('auth_pgsql')->statement("ALTER TABLE user_admins ALTER COLUMN alias SET NOT NULL");
        DB::connection('auth_pgsql')->statement("ALTER TABLE user_admins ALTER COLUMN role SET NOT NULL");
        DB::connection('auth_pgsql')->statement("CREATE UNIQUE INDEX IF NOT EXISTS user_admins_alias_unique ON user_admins (alias)");
        DB::connection('auth_pgsql')->statement(
            "CREATE UNIQUE INDEX IF NOT EXISTS user_admins_single_root_unique ON user_admins ((role)) WHERE role = 'root'"
        );
        DB::connection('auth_pgsql')->statement(
            "DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM pg_constraint WHERE conname = 'user_admins_role_check'
                ) THEN
                    ALTER TABLE user_admins
                    ADD CONSTRAINT user_admins_role_check CHECK (role IN ('root', 'master', 'admin'));
                END IF;
            END
            $$;"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('auth_pgsql')->statement('ALTER TABLE user_admins DROP CONSTRAINT IF EXISTS user_admins_role_check');
        DB::connection('auth_pgsql')->statement('DROP INDEX IF EXISTS user_admins_single_root_unique');
        DB::connection('auth_pgsql')->statement('DROP INDEX IF EXISTS user_admins_alias_unique');

        Schema::connection('auth_pgsql')->table('user_admins', function (Blueprint $table) {
            if (!Schema::connection('auth_pgsql')->hasColumn('user_admins', 'first_name')) {
                $table->string('first_name', 100)->nullable();
            }

            if (!Schema::connection('auth_pgsql')->hasColumn('user_admins', 'last_name')) {
                $table->string('last_name', 100)->nullable();
            }

            if (!Schema::connection('auth_pgsql')->hasColumn('user_admins', 'swimmer_id')) {
                $table->unsignedBigInteger('swimmer_id')->nullable();
                $table->index('swimmer_id');
            }
        });

        DB::connection('auth_pgsql')
            ->table('user_admins')
            ->orderBy('id')
            ->get()
            ->each(function ($userAdmin): void {
            DB::connection('auth_pgsql')
                ->table('user_admins')
                ->where('id', $userAdmin->id)
                ->update([
                    'first_name' => $userAdmin->alias,
                    'last_name' => '',
                    'swimmer_id' => null,
                ]);
            });

        Schema::connection('auth_pgsql')->table('user_admins', function (Blueprint $table) {
            foreach (['alias', 'role', 'is_enabled'] as $column) {
                if (Schema::connection('auth_pgsql')->hasColumn('user_admins', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function backfillAdminUsers(): void
    {
        $usersAdmin = DB::connection('auth_pgsql')
            ->table('user_admins')
            ->orderBy('id')
            ->get();
        $usedAliases = [];

        foreach ($usersAdmin as $userAdmin) {
            $alias = $userAdmin->alias ?? null;

            if (!$alias) {
                $name = trim(($userAdmin->first_name ?? '') . ' ' . ($userAdmin->last_name ?? ''));
                $alias = $this->makeUniqueAlias($name !== '' ? $name : 'admin' . $userAdmin->id, $usedAliases);
            }

            $usedAliases[$alias] = true;

            DB::connection('auth_pgsql')
                ->table('user_admins')
                ->where('id', $userAdmin->id)
                ->update([
                    'alias' => $alias,
                    'role' => $userAdmin->role ?? 'admin',
                    'is_enabled' => $userAdmin->is_enabled ?? true,
                ]);
        }
    }

    private function createRootUser(): void
    {
        $rootPayload = [
            'role' => 'root',
            'password' => Hash::make('1234'),
            'is_enabled' => true,
            'updated_at' => now(),
            'created_at' => now(),
        ];

        if (Schema::connection('auth_pgsql')->hasColumn('user_admins', 'first_name')) {
            $rootPayload['first_name'] = 'root';
        }

        if (Schema::connection('auth_pgsql')->hasColumn('user_admins', 'last_name')) {
            $rootPayload['last_name'] = '';
        }

        if (Schema::connection('auth_pgsql')->hasColumn('user_admins', 'swimmer_id')) {
            $rootPayload['swimmer_id'] = null;
        }

        DB::connection('auth_pgsql')
            ->table('user_admins')
            ->where('role', 'root')
            ->where('alias', '!=', 'root')
            ->update(['role' => 'master']);

        DB::connection('auth_pgsql')
            ->table('user_admins')
            ->updateOrInsert(
                ['alias' => 'root'],
                $rootPayload
            );
    }

    /**
     * @param array<string, bool> $usedAliases
     */
    private function makeUniqueAlias(string $value, array $usedAliases): string
    {
        $baseAlias = preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii($value))) ?: 'admin';
        $alias = $baseAlias;
        $index = 2;

        while (
            isset($usedAliases[$alias])
            || DB::connection('auth_pgsql')->table('user_admins')->where('alias', $alias)->exists()
        ) {
            $alias = $baseAlias . $index;
            $index++;
        }

        return $alias;
    }
};
