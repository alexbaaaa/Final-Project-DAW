<?php

namespace App\Http\Controllers;

use App\Models\PortalUser;
use App\Models\PortalUserAlias;
use App\Models\Swimmer;
use App\Models\UserAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $userTypeFilter = $request->query('user_type', 'all');
        $userTypeFilter = in_array($userTypeFilter, ['all', 'swimmer', 'legal_guardian', 'other'], true)
            ? $userTypeFilter
            : 'all';
        $users = PortalUser::query()
            ->when($userTypeFilter !== 'all', fn ($query) => $query->where('user_type', $userTypeFilter))
            ->orderBy('id')
            ->get(['id', 'alias', 'first_name', 'last_name', 'birth_date', 'user_type', 'swimmer_id', 'is_enabled', 'must_change_password']);

        $viewData = [
            'activeUserTypeFilter' => $userTypeFilter,
            'users' => $users,
            'userSwimmerNames' => $this->userSwimmerNames($users),
            'currentAdminUser' => $request->attributes->get('currentAdminUser'),
        ];

        if ($request->ajax()) {
            return view('users.partials.table', $viewData);
        }

        return view('users.index', $viewData);
    }

    public function create(): View
    {
        return view('users.create', [
            'swimmerOptions' => $this->swimmerOptions(),
            'guardianSwimmerOptions' => $this->guardianSwimmerOptions(),
            'selectedSwimmerIds' => [],
            'selectedSwimmerId' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatedPayload($request, null);
        $alias = PortalUserAlias::makeUnique($payload['first_name'], $payload['last_name'], $payload['birth_date']);

        $user = PortalUser::query()->create([
            'alias' => $alias,
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'birth_date' => $payload['birth_date'],
            'user_type' => $payload['user_type'],
            'password' => $alias,
            'swimmer_id' => $payload['user_type'] === 'swimmer' ? $payload['swimmer_id'] : null,
            'is_enabled' => true,
            'must_change_password' => true,
        ]);

        $this->syncAssociatedSwimmers($user, $payload['user_type'], $payload['swimmer_ids']);

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
            'swimmerOptions' => $this->swimmerOptions(),
            'guardianSwimmerOptions' => $this->guardianSwimmerOptions(),
            'selectedSwimmerIds' => $this->selectedSwimmerIds($record),
            'selectedSwimmerId' => $this->selectedSwimmerId($record),
        ]);
    }

    public function update(Request $request, int $user): RedirectResponse
    {
        $record = PortalUser::query()->findOrFail($user);
        $payload = $this->validatedPayload($request, $record);
        $passwordMatchesCurrentAlias = Hash::check(
            (string) $record->alias,
            (string) $record->getAttribute('password')
        );
        $alias = PortalUserAlias::makeUnique(
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
            'swimmer_id' => $payload['user_type'] === 'swimmer' ? $payload['swimmer_id'] : null,
        ];

        if ($passwordMatchesCurrentAlias) {
            $updatePayload['password'] = $alias;
            $updatePayload['must_change_password'] = true;
        }

        $record->fill($updatePayload);
        $record->save();

        $this->syncAssociatedSwimmers($record, $payload['user_type'], $payload['swimmer_ids']);

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

    public function resetPassword(Request $request, int $user): RedirectResponse
    {
        $currentAdminUser = $request->attributes->get('currentAdminUser');

        if (!$currentAdminUser instanceof UserAdmin || !$currentAdminUser->isRoot()) {
            abort(403, 'Only root can reset app user passwords.');
        }

        $record = PortalUser::query()->findOrFail($user);
        $record->forceFill([
            'password' => $record->defaultPassword(),
            'must_change_password' => true,
        ])->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status_success', "Password for {$record->alias} was reset to the default value.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request, ?PortalUser $record): array
    {
        $swimmerIds = $this->swimmerIds();
        $guardianSwimmerIds = $this->guardianSwimmerIds();
        $allowedUserTypes = ['legal_guardian', 'other'];

        if ($record?->user_type === 'swimmer') {
            $allowedUserTypes[] = 'swimmer';
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'user_type' => ['required', Rule::in($allowedUserTypes)],
            'swimmer_id' => ['nullable', 'integer', Rule::in($swimmerIds)],
            'swimmer_ids' => ['nullable', 'array'],
            'swimmer_ids.*' => ['nullable', 'integer', Rule::in($guardianSwimmerIds)],
        ]);

        $validated['birth_date'] = $validated['birth_date'] ?? $record?->birth_date?->toDateString();
        $validated['swimmer_id'] = isset($validated['swimmer_id']) && $validated['swimmer_id'] !== ''
            ? (int) $validated['swimmer_id']
            : null;
        $validated['swimmer_ids'] = collect($validated['swimmer_ids'] ?? [])
            ->filter(fn ($swimmerId): bool => $swimmerId !== null && $swimmerId !== '')
            ->map(fn ($swimmerId): int => (int) $swimmerId)
            ->unique()
            ->values()
            ->all();

        if ($validated['user_type'] === 'swimmer' && $validated['swimmer_id'] === null) {
            throw ValidationException::withMessages([
                'swimmer_id' => 'Select one swimmer for this user.',
            ]);
        }

        if ($validated['user_type'] === 'swimmer' && !$validated['birth_date']) {
            throw ValidationException::withMessages([
                'birth_date' => 'Birth date is required for swimmer users.',
            ]);
        }

        if ($validated['user_type'] === 'legal_guardian' && count($validated['swimmer_ids']) === 0) {
            throw ValidationException::withMessages([
                'swimmer_ids' => 'Select at least one under-16 swimmer for a legal guardian.',
            ]);
        }

        if ($validated['user_type'] === 'swimmer') {
            $validated['swimmer_ids'] = [$validated['swimmer_id']];
        }

        if ($validated['user_type'] !== 'swimmer') {
            $validated['swimmer_id'] = null;
            $validated['birth_date'] = null;
        }

        if ($validated['user_type'] === 'other') {
            $validated['swimmer_ids'] = [];
        }

        return $validated;
    }

    /**
     * @return array<int, string>
     */
    private function swimmerOptions(): array
    {
        return Swimmer::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'birth_date'])
            ->mapWithKeys(fn (Swimmer $swimmer): array => [
                (int) $swimmer->id => trim($swimmer->first_name . ' ' . $swimmer->last_name),
            ])
            ->all();
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
    private function swimmerIds(): array
    {
        return array_map('intval', array_keys($this->swimmerOptions()));
    }

    /**
     * @return array<int, int>
     */
    private function guardianSwimmerIds(): array
    {
        return array_map('intval', array_keys($this->guardianSwimmerOptions()));
    }

    /**
     * @return array<int, int>
     */
    private function selectedSwimmerIds(PortalUser $user): array
    {
        $swimmerIds = DB::connection('auth_pgsql')
            ->table('portal_user_swimmers')
            ->where('user_id', $user->id)
            ->pluck('swimmer_id')
            ->map(fn ($swimmerId): int => (int) $swimmerId)
            ->all();

        if ($user->swimmer_id !== null) {
            $swimmerIds[] = (int) $user->swimmer_id;
        }

        return array_values(array_unique($swimmerIds));
    }

    private function selectedSwimmerId(PortalUser $user): ?int
    {
        $swimmerIds = $this->selectedSwimmerIds($user);

        return $swimmerIds[0] ?? null;
    }

    /**
     * @param array<int, int> $swimmerIds
     */
    private function syncAssociatedSwimmers(PortalUser $user, string $userType, array $swimmerIds): void
    {
        DB::connection('auth_pgsql')
            ->table('portal_user_swimmers')
            ->where('user_id', $user->id)
            ->delete();

        if (!in_array($userType, ['legal_guardian', 'swimmer'], true)) {
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
