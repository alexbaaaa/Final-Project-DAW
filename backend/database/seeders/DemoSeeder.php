<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\PortalUser;
use App\Models\PortalUserAlias;
use App\Models\Swimmer;
use App\Models\Time;
use App\Models\TrainingGroup;
use App\Models\UserAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Datos ficticios para demos y capturas de pantalla.
 * NO ejecutar en produccion.
 *
 *   php artisan db:seed --class=DemoSeeder
 *
 * Credenciales de demo (solo entorno local):
 *   Admin  (panel /admin): alias "demo"      / password "Demo1234!"
 *   App    (React /login): alias "lgarcia10" / password "Demo1234!" (nadadora)
 *   App    (React /login): alias "mgarcia80" / password "Demo1234!" (tutora legal de 2 nadadores)
 */
class DemoSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'Demo1234!';

    public function run(): void
    {
        $today = now()->startOfDay();

        // --- Grupos de entrenamiento -------------------------------------
        TrainingGroup::query()->create([
            'name' => 'Grupo Tecnificacion',
            'categories' => 'Infantil, Junior',
            'schedule' => [
                ['days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'], 'from' => '17:30', 'to' => '19:30'],
                ['days' => ['saturday'], 'from' => '09:00', 'to' => '11:00'],
            ],
        ]);
        TrainingGroup::query()->create([
            'name' => 'Grupo Escuela',
            'categories' => 'Prebenjamin, Benjamin, Alevin',
            'schedule' => [
                ['days' => ['monday', 'wednesday', 'friday'], 'from' => '16:30', 'to' => '17:30'],
            ],
        ]);
        TrainingGroup::query()->create([
            'name' => 'Grupo Master',
            'categories' => 'Absoluto, Master',
            'schedule' => [
                ['days' => ['tuesday', 'thursday'], 'from' => '20:00', 'to' => '21:30'],
            ],
        ]);

        // --- Nadadores -----------------------------------------------------
        $swimmers = collect([
            ['Lucia', 'Garcia Ruiz', '2010-03-14', 'Junior', 'Female'],
            ['Pablo', 'Garcia Ruiz', '2014-07-02', 'Alevin', 'Male'],
            ['Marta', 'Lopez Sanz', '2011-11-21', 'Infantil', 'Female'],
            ['Diego', 'Fernandez Mora', '2009-01-30', 'Junior', 'Male'],
            ['Sara', 'Martin Gil', '2016-05-09', 'Benjamin', 'Female'],
            ['Javier', 'Navarro Diaz', '1988-09-17', 'Master', 'Male'],
        ])->map(fn (array $row): Swimmer => Swimmer::query()->create([
            'first_name' => $row[0],
            'last_name' => $row[1],
            'birth_date' => $row[2],
            'category' => $row[3],
            'gender' => $row[4],
        ]));

        [$lucia, $pablo, $marta, $diego] = $swimmers->all();

        // --- Marcas --------------------------------------------------------
        $times = [
            [$lucia, '100m Libres', '1:02.45', -120, 'Piscina Municipal Valencia'],
            [$lucia, '100m Libres', '1:01.10', -40, 'CN Sant Andreu'],
            [$lucia, '200m Estilos', '2:31.88', -60, 'Piscina Municipal Valencia'],
            [$lucia, '50m Mariposa', '0:30.12', -15, 'Complejo Deportivo Nazaret'],
            [$lucia, '100m Espalda', '1:09.73', -90, 'CN Sant Andreu'],
            [$pablo, '50m Libres', '0:33.90', -30, 'Piscina Municipal Valencia'],
            [$pablo, '100m Braza', '1:28.04', -70, 'Complejo Deportivo Nazaret'],
            [$marta, '200m Libres', '2:18.50', -25, 'CN Sant Andreu'],
            [$diego, '400m Libres', '4:21.67', -50, 'Piscina Municipal Valencia'],
        ];

        foreach ($times as [$swimmer, $testType, $time, $offsetDays, $location]) {
            Time::query()->create([
                'swimmer_id' => $swimmer->id,
                'test_type' => $testType,
                'time' => $time,
                'date' => $today->copy()->addDays($offsetDays)->toDateString(),
                'location' => $location,
            ]);
        }

        // --- Eventos -------------------------------------------------------
        $trofeo = Event::query()->create([
            'event_name' => 'Trofeo de Otono',
            'description' => 'Competicion amistosa entre clubes de la provincia.',
            'event_date' => $today->copy()->addDays(9)->toDateString(),
            'event_start_date' => $today->copy()->addDays(9)->toDateString(),
            'event_end_date' => $today->copy()->addDays(9)->toDateString(),
            'event_type' => 'trophy',
            'categories' => 'Alevin, Infantil, Junior',
            'day_scope' => 'morning',
        ]);
        Event::query()->create([
            'event_name' => 'Campeonato Autonomico Junior',
            'description' => 'Campeonato de dos jornadas. Sesiones de manana y tarde.',
            'event_date' => $today->copy()->addDays(22)->toDateString(),
            'event_start_date' => $today->copy()->addDays(22)->toDateString(),
            'event_end_date' => $today->copy()->addDays(23)->toDateString(),
            'event_type' => 'championship',
            'categories' => 'Junior',
            'day_scope' => 'full_day',
        ]);
        Event::query()->create([
            'event_name' => 'Jornada de puertas abiertas',
            'description' => 'Presentacion de la temporada a familias.',
            'event_date' => $today->copy()->addDays(3)->toDateString(),
            'event_start_date' => $today->copy()->addDays(3)->toDateString(),
            'event_end_date' => $today->copy()->addDays(3)->toDateString(),
            'event_type' => 'event',
            'categories' => 'All',
            'day_scope' => 'afternoon',
        ]);
        Event::query()->create([
            'event_name' => 'Control de marcas',
            'description' => 'Control interno de tiempos.',
            'event_date' => $today->copy()->subDays(6)->toDateString(),
            'event_start_date' => $today->copy()->subDays(6)->toDateString(),
            'event_end_date' => $today->copy()->subDays(6)->toDateString(),
            'event_type' => 'competition',
            'categories' => 'All',
            'day_scope' => 'afternoon',
        ]);

        // --- Calendario ----------------------------------------------------
        Calendar::query()->create([
            'date' => $today->copy()->addDays(1)->toDateString(),
            'day_type' => 'registration_deadline',
            'event_id' => $trofeo->id,
            'categories' => 'Alevin, Infantil, Junior',
            'day_scope' => 'full_day',
            'title' => 'Fin de inscripciones Trofeo de Otono',
            'description' => null,
        ]);
        Calendar::query()->create([
            'date' => $today->copy()->addDays(6)->toDateString(),
            'day_type' => 'training_suspended',
            'categories' => 'All',
            'day_scope' => 'afternoon',
            'title' => 'Mantenimiento de la piscina',
            'description' => 'Sin entrenamiento por tareas de mantenimiento.',
        ]);
        Calendar::query()->create([
            'date' => $today->copy()->addDays(14)->toDateString(),
            'day_type' => 'holiday_training',
            'categories' => 'All',
            'day_scope' => 'morning',
            'title' => 'Festivo local',
            'description' => 'Entrenamiento solo por la manana.',
        ]);

        // --- Usuarios de la app (PostgreSQL) ------------------------------
        $luciaUser = $this->createPortalUser('Lucia', 'Garcia Ruiz', '2010-03-14', 'swimmer', $lucia->id);
        $this->linkSwimmers($luciaUser, [$lucia->id]);

        $guardian = $this->createPortalUser('Maria', 'Garcia Lopez', '1980-02-11', 'legal_guardian', null);
        $this->linkSwimmers($guardian, [$lucia->id, $pablo->id]);

        // --- Admin de demo -------------------------------------------------
        UserAdmin::query()->updateOrCreate(['alias' => 'demo'], [
            'role' => UserAdmin::ROLE_MASTER,
            'password' => self::DEMO_PASSWORD,
            'is_enabled' => true,
            'must_change_password' => false,
        ]);
    }

    private function createPortalUser(string $firstName, string $lastName, string $birthDate, string $type, ?int $swimmerId): PortalUser
    {
        return PortalUser::query()->create([
            'alias' => PortalUserAlias::makeUnique($firstName, $lastName, $birthDate),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'birth_date' => $birthDate,
            'user_type' => $type,
            'password' => self::DEMO_PASSWORD,
            'swimmer_id' => $swimmerId,
            'is_enabled' => true,
            'must_change_password' => false,
        ]);
    }

    /**
     * @param array<int, int> $swimmerIds
     */
    private function linkSwimmers(PortalUser $user, array $swimmerIds): void
    {
        foreach ($swimmerIds as $swimmerId) {
            DB::connection('auth_pgsql')->table('portal_user_swimmers')->insertOrIgnore([
                'user_id' => $user->id,
                'swimmer_id' => $swimmerId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
