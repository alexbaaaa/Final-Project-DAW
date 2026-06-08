<?php

namespace App\Http\Controllers;

use App\Models\PortalUser;
use App\Models\Swimmer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = PortalUser::query()
            ->orderBy('id')
            ->get(['id', 'alias', 'first_name', 'last_name', 'birth_date', 'user_type', 'swimmer_id']);

        return view('users.index', [
            'users' => $users,
            'userSwimmerNames' => $this->userSwimmerNames($users),
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'guardianSwimmerOptions' => $this->guardianSwimmerOptions(),
            'selectedSwimmerIds' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatedPayload($request);
        $alias = $this->makeUniqueAlias($payload['first_name'], $payload['last_name'], $payload['birth_date']);

        $user = PortalUser::query()->create([
            'alias' => $alias,
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'birth_date' => $payload['birth_date'],
            'user_type' => $payload['user_type'],
            'password' => $alias,
            'swimmer_id' => null,
        ]);

        $this->syncGuardianSwimmers($user, $payload['user_type'], $payload['swimmer_ids']);

        return redirect()
            ->route('admin.users.index')
            ->with('status_success', "User created successfully. Initial password: {$alias}");
    }

    public function show(int $user): View
    {
        $record = PortalUser::query()->findOrFail($user);
        $userSwimmerNames = $this->userSwimmerNames(collect([$record]));

        return view('users.show', [
            'user' => $record,
            'userSwimmerNames' => $userSwimmerNames[(int) $record->id] ?? [],
        ]);
    }

    public function edit(int $user): View
    {
        $record = PortalUser::query()->findOrFail($user);

        return view('users.edit', [
            'user' => $record,
            'guardianSwimmerOptions' => $this->guardianSwimmerOptions(),
            'selectedSwimmerIds' => $this->selectedSwimmerIds($record),
        ]);
    }

    public function update(Request $request, int $user): RedirectResponse
    {
        $record = PortalUser::query()->findOrFail($user);
        $payload = $this->validatedPayload($request);
        $passwordMatchesCurrentAlias = Hash::check(
            (string) $record->alias,
            (string) $record->getAttribute('password')
        );
        $alias = $this->makeUniqueAlias(
            $payload['first_name'],
            $payload['last_name'],
            $payload['birth_date'],
            $record->id
        );

        $updatePayload = [
            'alias' => $alias,
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'birth_date' => $payload['birth_date'],
            'user_type' => $payload['user_type'],
            'swimmer_id' => null,
        ];

        if ($passwordMatchesCurrentAlias) {
            $updatePayload['password'] = $alias;
        }

        $record->fill($updatePayload);
        $record->save();

        $this->syncGuardianSwimmers($record, $payload['user_type'], $payload['swimmer_ids']);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status_success', 'User updated successfully.');
    }

    public function destroy(int $user): RedirectResponse
    {
        DB::connection('auth_pgsql')
            ->table('portal_user_swimmers')
            ->where('user_id', $user)
            ->delete();

        PortalUser::query()->findOrFail($user)->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status_success', "User #{$user} deleted successfully from PostgreSQL.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'user_type' => ['required', 'in:legal_guardian,swimmer'],
            'swimmer_ids' => ['nullable', 'array'],
            'swimmer_ids.*' => ['integer', Rule::in($this->guardianSwimmerIds())],
        ]);

        $validated['swimmer_ids'] = array_values(array_unique(array_map(
            'intval',
            $validated['swimmer_ids'] ?? []
        )));

        if ($validated['user_type'] === 'legal_guardian' && count($validated['swimmer_ids']) === 0) {
            throw ValidationException::withMessages([
                'swimmer_ids' => 'Select at least one under-16 swimmer for a legal guardian.',
            ]);
        }

        if ($validated['user_type'] === 'swimmer') {
            $validated['swimmer_ids'] = [];
        }

        return $validated;
    }

    /**
     * @return array<int, string>
     */
    private function guardianSwimmerOptions(): array
    {
        return Swimmer::query()
            ->whereDate('birth_date', '>', now()->subYears(16)->toDateString())
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'birth_date'])
            ->mapWithKeys(fn (Swimmer $swimmer): array => [
                (int) $swimmer->id => trim($swimmer->first_name . ' ' . $swimmer->last_name),
            ])
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function guardianSwimmerIds(): array
    {
        return array_map('intval', array_keys($this->guardianSwimmerOptions()));
    }

    private function makeUniqueAlias(string $firstName, string $lastName, string $birthDate, ?int $ignoreUserId = null): string
    {
        $nameInitial = Str::substr($this->normalizeAliasPart($firstName), 0, 1) ?: 'u';
        $surnameParts = preg_split('/\s+/', trim($lastName)) ?: [];
        $firstSurname = $this->normalizeAliasPart($surnameParts[0] ?? 'user');
        $secondSurname = $this->normalizeAliasPart($surnameParts[1] ?? '');
        $yearSuffix = substr((string) date('Y', strtotime($birthDate)), -2);
        $firstAlias = $nameInitial . $firstSurname . $yearSuffix;
        $secondAlias = $secondSurname !== '' ? $nameInitial . $secondSurname . $yearSuffix : $firstAlias;

        foreach ([$firstAlias, $secondAlias] as $candidate) {
            if (!$this->aliasExists($candidate, $ignoreUserId)) {
                return $candidate;
            }
        }

        $index = 2;

        do {
            $candidate = $secondAlias . $index;
            $index++;
        } while ($this->aliasExists($candidate, $ignoreUserId));

        return $candidate;
    }

    private function aliasExists(string $alias, ?int $ignoreUserId = null): bool
    {
        return PortalUser::query()
            ->where('alias', $alias)
            ->when($ignoreUserId !== null, fn ($query) => $query->where('id', '!=', $ignoreUserId))
            ->exists();
    }

    private function normalizeAliasPart(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii($value))) ?: '';
    }

    /**
     * @return array<int, int>
     */
    private function selectedSwimmerIds(PortalUser $user): array
    {
        return DB::connection('auth_pgsql')
            ->table('portal_user_swimmers')
            ->where('user_id', $user->id)
            ->pluck('swimmer_id')
            ->map(fn ($swimmerId): int => (int) $swimmerId)
            ->all();
    }

    /**
     * @param array<int, int> $swimmerIds
     */
    private function syncGuardianSwimmers(PortalUser $user, string $userType, array $swimmerIds): void
    {
        DB::connection('auth_pgsql')
            ->table('portal_user_swimmers')
            ->where('user_id', $user->id)
            ->delete();

        if ($userType !== 'legal_guardian') {
            return;
        }

        foreach ($swimmerIds as $swimmerId) {
            DB::connection('auth_pgsql')
                ->table('portal_user_swimmers')
                ->insert([
                    'user_id' => $user->id,
                    'swimmer_id' => $swimmerId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function userSwimmerNames($users): array
    {
        $userIds = $users->pluck('id')->map(fn ($userId): int => (int) $userId)->all();
        $links = DB::connection('auth_pgsql')
            ->table('portal_user_swimmers')
            ->whereIn('user_id', $userIds)
            ->get(['user_id', 'swimmer_id']);
        $swimmerIds = $links->pluck('swimmer_id')->map(fn ($swimmerId): int => (int) $swimmerId)->unique()->all();
        $swimmers = Swimmer::query()
            ->whereIn('id', $swimmerIds)
            ->get(['id', 'first_name', 'last_name'])
            ->mapWithKeys(fn (Swimmer $swimmer): array => [
                (int) $swimmer->id => trim($swimmer->first_name . ' ' . $swimmer->last_name),
            ]);
        $names = [];

        foreach ($links as $link) {
            $userId = (int) $link->user_id;
            $swimmerId = (int) $link->swimmer_id;

            if (isset($swimmers[$swimmerId])) {
                $names[$userId][] = $swimmers[$swimmerId];
            }
        }

        return $names;
    }
}
