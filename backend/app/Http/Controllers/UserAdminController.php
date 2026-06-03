<?php

namespace App\Http\Controllers;

use App\Models\Swimmer;
use App\Models\UserAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserAdminController extends Controller
{
    public function index(): View
    {
        $usersAdmin = UserAdmin::query()
            ->orderBy('id')
            ->get(['id', 'first_name', 'last_name', 'password', 'swimmer_id']);

        return view('usersAdmin.index', [
            'usersAdmin' => $usersAdmin,
            'swimmerOptions' => $this->swimmerOptions(),
        ]);
    }

    public function create(): View
    {
        return view('usersAdmin.create', [
            'swimmerOptions' => $this->swimmerOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        UserAdmin::query()->create($this->validatedPayload($request, true));

        return redirect()
            ->route('admin.users_admin.index')
            ->with('status_success', 'Admin user created successfully in PostgreSQL.');
    }

    public function show(int $userAdmin): View
    {
        return view('usersAdmin.show', [
            'userAdmin' => UserAdmin::query()->findOrFail($userAdmin),
            'swimmerOptions' => $this->swimmerOptions(),
        ]);
    }

    public function edit(int $userAdmin): View
    {
        return view('usersAdmin.edit', [
            'userAdmin' => UserAdmin::query()->findOrFail($userAdmin),
            'swimmerOptions' => $this->swimmerOptions(),
        ]);
    }

    public function update(Request $request, int $userAdmin): RedirectResponse
    {
        $record = UserAdmin::query()->findOrFail($userAdmin);
        $record->fill($this->validatedPayload($request, false));
        $record->save();

        return redirect()
            ->route('admin.users_admin.edit', $userAdmin)
            ->with('status_success', 'Admin user updated successfully in PostgreSQL.');
    }

    public function destroy(int $userAdmin): RedirectResponse
    {
        UserAdmin::query()->findOrFail($userAdmin)->delete();

        return redirect()
            ->route('admin.users_admin.index')
            ->with('status_success', "Admin user #{$userAdmin} deleted successfully from PostgreSQL.");
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
            'password' => $passwordRules,
            'swimmer_id' => ['nullable', 'integer', 'min:1', 'exists:swimmers,id'],
        ]);

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
}
