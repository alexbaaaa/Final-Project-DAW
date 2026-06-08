<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::connection('auth_pgsql')->table('users', function (Blueprint $table) {
            if (!Schema::connection('auth_pgsql')->hasColumn('users', 'alias')) {
                $table->string('alias', 120)->nullable()->unique();
            }

            if (!Schema::connection('auth_pgsql')->hasColumn('users', 'birth_date')) {
                $table->date('birth_date')->nullable();
            }
        });

        if (!Schema::connection('auth_pgsql')->hasTable('portal_user_swimmers')) {
            Schema::connection('auth_pgsql')->create('portal_user_swimmers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('swimmer_id');
                $table->timestamps();

                $table->unique(['user_id', 'swimmer_id']);
                $table->index('swimmer_id');
            });
        }

        $this->backfillUsers();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('auth_pgsql')->dropIfExists('portal_user_swimmers');

        Schema::connection('auth_pgsql')->table('users', function (Blueprint $table) {
            if (Schema::connection('auth_pgsql')->hasColumn('users', 'alias')) {
                $table->dropColumn('alias');
            }

            if (Schema::connection('auth_pgsql')->hasColumn('users', 'birth_date')) {
                $table->dropColumn('birth_date');
            }
        });
    }

    private function backfillUsers(): void
    {
        $users = DB::connection('auth_pgsql')
            ->table('users')
            ->orderBy('id')
            ->get(['id', 'alias', 'first_name', 'last_name', 'birth_date', 'swimmer_id']);
        $usedAliases = [];

        foreach ($users as $user) {
            $birthDate = $user->birth_date;

            if (!$birthDate && $user->swimmer_id) {
                $birthDate = DB::connection('mariadb')
                    ->table('swimmers')
                    ->where('id', $user->swimmer_id)
                    ->value('birth_date');
            }

            $updatePayload = [];

            if ($user->alias) {
                $usedAliases[(string) $user->alias] = true;
            } else {
                $updatePayload['alias'] = $this->makeUniqueAlias(
                    (string) $user->first_name,
                    (string) $user->last_name,
                    $birthDate,
                    $usedAliases
                );
            }

            if (!$user->birth_date && $birthDate) {
                $updatePayload['birth_date'] = $birthDate;
            }

            if ($updatePayload !== []) {
                DB::connection('auth_pgsql')
                    ->table('users')
                    ->where('id', $user->id)
                    ->update($updatePayload);
            }

            if ($user->swimmer_id) {
                DB::connection('auth_pgsql')
                    ->table('portal_user_swimmers')
                    ->insertOrIgnore([
                        'user_id' => $user->id,
                        'swimmer_id' => $user->swimmer_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * @param array<string, bool> $usedAliases
     */
    private function makeUniqueAlias(string $firstName, string $lastName, ?string $birthDate, array &$usedAliases): string
    {
        $nameInitial = Str::substr($this->normalizeAliasPart($firstName), 0, 1) ?: 'u';
        $surnameParts = preg_split('/\s+/', trim($lastName)) ?: [];
        $firstSurname = $this->normalizeAliasPart($surnameParts[0] ?? 'user');
        $secondSurname = $this->normalizeAliasPart($surnameParts[1] ?? '');
        $yearSuffix = $birthDate ? substr((string) date('Y', strtotime($birthDate)), -2) : '00';
        $firstAlias = $nameInitial . $firstSurname . $yearSuffix;
        $secondAlias = $secondSurname !== '' ? $nameInitial . $secondSurname . $yearSuffix : $firstAlias;

        foreach ([$firstAlias, $secondAlias] as $candidate) {
            if (!$this->aliasExists($candidate, $usedAliases)) {
                $usedAliases[$candidate] = true;

                return $candidate;
            }
        }

        $index = 2;

        do {
            $candidate = $secondAlias . $index;
            $index++;
        } while ($this->aliasExists($candidate, $usedAliases));

        $usedAliases[$candidate] = true;

        return $candidate;
    }

    private function aliasExists(string $alias, array $usedAliases): bool
    {
        return isset($usedAliases[$alias])
            || DB::connection('auth_pgsql')->table('users')->where('alias', $alias)->exists();
    }

    private function normalizeAliasPart(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii($value))) ?: '';
    }
};
