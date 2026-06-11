<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::connection('auth_pgsql')->table('users', function (Blueprint $table): void {
            if (!Schema::connection('auth_pgsql')->hasColumn('users', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true);
            }
        });

        $this->backfillSwimmerUsers();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('auth_pgsql')->hasColumn('users', 'is_enabled')) {
            Schema::connection('auth_pgsql')->table('users', function (Blueprint $table): void {
                $table->dropColumn('is_enabled');
            });
        }
    }

    private function backfillSwimmerUsers(): void
    {
        DB::connection('auth_pgsql')
            ->table('users')
            ->where('user_type', 'swimmer')
            ->orderBy('id')
            ->get(['id', 'birth_date', 'swimmer_id'])
            ->each(function ($user): void {
                $birthDate = $this->resolveBirthDate($user);

                DB::connection('auth_pgsql')
                    ->table('users')
                    ->where('id', $user->id)
                    ->update([
                        'is_enabled' => $birthDate ? Carbon::parse($birthDate)->age >= 16 : true,
                    ]);
            });
    }

    private function resolveBirthDate(object $user): ?string
    {
        if ($user->swimmer_id) {
            return DB::connection('mariadb')
                ->table('swimmers')
                ->where('id', $user->swimmer_id)
                ->value('birth_date') ?: $user->birth_date;
        }

        $linkedSwimmerId = DB::connection('auth_pgsql')
            ->table('portal_user_swimmers')
            ->where('user_id', $user->id)
            ->value('swimmer_id');

        if (!$linkedSwimmerId) {
            return $user->birth_date;
        }

        return DB::connection('mariadb')
            ->table('swimmers')
            ->where('id', $linkedSwimmerId)
            ->value('birth_date') ?: $user->birth_date;
    }
};
