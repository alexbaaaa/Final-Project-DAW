<?php

namespace App\Http\Controllers;

use App\Models\PortalUser;
use App\Models\Swimmer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = PortalUser::query()
            ->orderBy('id')
            ->get(['id', 'first_name', 'last_name', 'user_type', 'password', 'swimmer_id']);

        return view('users.index', [
            'users' => $users,
            'swimmerOptions' => $this->swimmerOptions(),
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'guardianSwimmerOptions' => $this->guardianSwimmerOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        PortalUser::query()->create($this->validatedPayload($request, true));

        return redirect()
            ->route('admin.users.index')
            ->with('status_success', 'User created successfully in PostgreSQL.');
    }

    public function show(int $user): View
    {
        return view('users.show', [
            'user' => PortalUser::query()->findOrFail($user),
            'swimmerOptions' => $this->swimmerOptions(),
        ]);
    }

    public function edit(int $user): View
    {
        return view('users.edit', [
            'user' => PortalUser::query()->findOrFail($user),
            'guardianSwimmerOptions' => $this->guardianSwimmerOptions(),
        ]);
    }

    public function update(Request $request, int $user): RedirectResponse
    {
        $record = PortalUser::query()->findOrFail($user);
        $record->fill($this->validatedPayload($request, false));
        $record->save();

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status_success', 'User updated successfully in PostgreSQL.');
    }

    public function destroy(int $user): RedirectResponse
    {
        PortalUser::query()->findOrFail($user)->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status_success', "User #{$user} deleted successfully from PostgreSQL.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request, bool $passwordRequired): array
    {
        $passwordRules = ['nullable', 'string', 'min:8'];

        if ($passwordRequired) {
            $passwordRules[0] = 'required';
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'user_type' => ['required', 'in:legal_guardian,swimmer'],
            'password' => $passwordRules,
            'swimmer_id' => [
                'nullable',
                'required_if:user_type,legal_guardian',
                'integer',
                'min:1',
                Rule::in($this->guardianSwimmerIds()),
            ],
        ]);

        if ($validated['user_type'] === 'swimmer') {
            $validated['swimmer_id'] = null;
        }

        if (!$passwordRequired && empty($validated['password'])) {
            unset($validated['password']);
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
            ->get(['id', 'first_name', 'last_name'])
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
            ->where('age', '<', 16)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name'])
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
}
