<?php

namespace App\Http\Controllers;

use App\Models\PortalUser;
use App\Models\PortalUserAlias;
use App\Models\Swimmer;
use App\Models\Time;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SwimmerController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const CATEGORIES = [
        'Prebenjamin',
        'Benjamin',
        'Alevin',
        'Infantil',
        'Junior',
        'Absoluto Joven',
        'Absoluto',
        'Master',
    ];

    /**
     * @var array<int, string>
     */
    private const GENDERS = ['Male', 'Female', 'Other'];

    public function index(): View
    {
        $swimmers = Swimmer::query()
            ->orderBy('id')
            ->get(['id', 'first_name', 'last_name', 'birth_date', 'category', 'gender']);

        return view('swimmers.index', [
            'swimmers' => $swimmers,
        ]);
    }

    public function create(): View
    {
        return view('swimmers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatedPayload($request);

        DB::transaction(function () use ($payload): void {
            $swimmer = Swimmer::query()->create($payload);
            $this->createPortalUserForSwimmer($swimmer);
        });

        return redirect()
            ->route('admin.swimmers.index')
            ->with('status_success', 'Swimmer created successfully.');
    }

    public function show(int $swimmer): View
    {
        $record = Swimmer::query()->findOrFail($swimmer);
        $times = Time::query()
            ->where('swimmer_id', $record->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get(['id', 'test_type', 'time', 'date', 'location']);

        return view('swimmers.show', [
            'swimmer' => $record,
            'times' => $times,
        ]);
    }

    public function edit(int $swimmer): View
    {
        return view('swimmers.edit', [
            'swimmer' => Swimmer::query()->findOrFail($swimmer),
        ]);
    }

    public function update(Request $request, int $swimmer): RedirectResponse
    {
        $record = Swimmer::query()->findOrFail($swimmer);
        $record->fill($this->validatedPayload($request));
        $record->save();
        $this->syncPortalUserAvailabilityForSwimmer($record);

        return redirect()
            ->route('admin.swimmers.edit', $swimmer)
            ->with('status_success', 'Swimmer updated successfully.');
    }

    public function destroy(int $swimmer): RedirectResponse
    {
        Swimmer::query()->findOrFail($swimmer)->delete();

        return redirect()
            ->route('admin.swimmers.index')
            ->with('status_success', "Swimmer #{$swimmer} deleted successfully.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['required', 'string', Rule::in(self::CATEGORIES)],
            'gender' => ['required', 'string', Rule::in(self::GENDERS)],
        ]);
    }

    private function createPortalUserForSwimmer(Swimmer $swimmer): void
    {
        DB::connection('auth_pgsql')->transaction(function () use ($swimmer): void {
            $birthDate = $swimmer->birth_date?->toDateString();

            if ($birthDate === null) {
                throw new \RuntimeException('Swimmer birth date is required to create an app user.');
            }

            $alias = PortalUserAlias::makeUnique($swimmer->first_name, $swimmer->last_name, $birthDate);

            $user = PortalUser::query()->create([
                'alias' => $alias,
                'first_name' => $swimmer->first_name,
                'last_name' => $swimmer->last_name,
                'birth_date' => $birthDate,
                'user_type' => 'swimmer',
                'password' => $alias,
                'swimmer_id' => $swimmer->id,
                'is_enabled' => $this->isSwimmerOldEnough($swimmer),
                'must_change_password' => true,
            ]);

            DB::connection('auth_pgsql')
                ->table('portal_user_swimmers')
                ->insert([
                    'user_id' => $user->id,
                    'swimmer_id' => $swimmer->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        });
    }

    private function syncPortalUserAvailabilityForSwimmer(Swimmer $swimmer): void
    {
        DB::connection('auth_pgsql')->transaction(function () use ($swimmer): void {
            $linkedUserIds = DB::connection('auth_pgsql')
                ->table('portal_user_swimmers')
                ->where('swimmer_id', $swimmer->id)
                ->pluck('user_id')
                ->map(fn ($userId): int => (int) $userId)
                ->all();

            PortalUser::query()
                ->where('user_type', 'swimmer')
                ->where(function ($query) use ($swimmer, $linkedUserIds): void {
                    $query->where('swimmer_id', $swimmer->id);

                    if ($linkedUserIds !== []) {
                        $query->orWhereIn('id', $linkedUserIds);
                    }
                })
                ->update([
                    'birth_date' => $swimmer->birth_date?->toDateString(),
                    'is_enabled' => $this->isSwimmerOldEnough($swimmer),
                ]);
        });
    }

    private function isSwimmerOldEnough(Swimmer $swimmer): bool
    {
        return $swimmer->birth_date !== null && $swimmer->birth_date->age >= 16;
    }
}
