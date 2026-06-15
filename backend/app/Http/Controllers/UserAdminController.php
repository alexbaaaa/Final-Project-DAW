<?php

namespace App\Http\Controllers;

use App\Models\UserAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserAdminController extends Controller
{
    public function index(Request $request): View
    {
        $usersAdmin = UserAdmin::query()
            ->orderBy('id')
            ->get(['id', 'alias', 'role', 'is_enabled', 'must_change_password']);

        return view('usersAdmin.index', [
            'usersAdmin' => $usersAdmin,
            'currentAdminUser' => $this->currentAdminUser($request),
        ]);
    }

    public function create(): View
    {
        return view('usersAdmin.create', [
            'roles' => UserAdmin::creatableRoles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatedPayload($request);

        UserAdmin::query()->create([
            'alias' => $payload['alias'],
            'role' => $payload['role'],
            'password' => $payload['alias'],
            'is_enabled' => true,
            'must_change_password' => true,
        ]);

        return redirect()
            ->route('admin.users_admin.index')
            ->with('status_success', "Admin user created successfully. Initial password: {$payload['alias']}");
    }

    public function edit(int $userAdmin): View
    {
        $record = UserAdmin::query()->findOrFail($userAdmin);

        if ($record->isRoot()) {
            abort(403, 'The root user cannot be edited.');
        }

        return view('usersAdmin.edit', [
            'userAdmin' => $record,
            'roles' => UserAdmin::creatableRoles(),
        ]);
    }

    public function update(Request $request, int $userAdmin): RedirectResponse
    {
        $record = UserAdmin::query()->findOrFail($userAdmin);

        if ($record->isRoot()) {
            abort(403, 'The root user cannot be edited.');
        }

        $payload = $this->validatedPayload($request, $record->id);
        $updatePayload = [
            'alias' => $payload['alias'],
            'role' => $payload['role'],
        ];

        if ($payload['alias'] !== $record->alias) {
            $updatePayload['password'] = $payload['alias'];
            $updatePayload['must_change_password'] = true;
        }

        $record->fill($updatePayload);
        $record->save();

        return redirect()
            ->route('admin.users_admin.edit', $userAdmin)
            ->with('status_success', 'Admin user updated successfully.');
    }

    public function destroy(Request $request, int $userAdmin): RedirectResponse
    {
        $currentAdminUser = $this->currentAdminUser($request);
        $record = UserAdmin::query()->findOrFail($userAdmin);

        if (!$currentAdminUser->canDeleteAdminUser($record)) {
            abort(403, 'You do not have permission to delete this admin user.');
        }

        $record->delete();

        return redirect()
            ->route('admin.users_admin.index')
            ->with('status_success', "Admin user #{$userAdmin} deleted successfully from PostgreSQL.");
    }

    public function toggle(Request $request, int $userAdmin): RedirectResponse
    {
        $currentAdminUser = $this->currentAdminUser($request);
        $record = UserAdmin::query()->findOrFail($userAdmin);

        if (!$currentAdminUser->canToggleAdminUser($record)) {
            abort(403, 'Only root can enable or disable admin users.');
        }

        $record->forceFill([
            'is_enabled' => !$record->is_enabled,
        ])->save();

        return redirect()
            ->route('admin.users_admin.index')
            ->with('status_success', "Admin user {$record->alias} updated successfully.");
    }

    public function resetPassword(Request $request, int $userAdmin): RedirectResponse
    {
        $currentAdminUser = $this->currentAdminUser($request);

        if (!$currentAdminUser->isRoot()) {
            abort(403, 'Only root can reset admin user passwords.');
        }

        $record = UserAdmin::query()->findOrFail($userAdmin);
        $record->forceFill([
            'password' => $record->defaultPassword(),
            'must_change_password' => true,
        ])->save();

        return redirect()
            ->route('admin.users_admin.index')
            ->with('status_success', "Password for {$record->alias} was reset to the default value.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPayload(Request $request, ?int $ignoreUserAdminId = null): array
    {
        return $request->validate([
            'alias' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('auth_pgsql.user_admins', 'alias')->ignore($ignoreUserAdminId),
            ],
            'role' => ['required', Rule::in(UserAdmin::creatableRoles())],
        ]);
    }

    private function currentAdminUser(Request $request): UserAdmin
    {
        $currentAdminUser = $request->attributes->get('currentAdminUser');

        if (!$currentAdminUser instanceof UserAdmin) {
            abort(403, 'No admin user is authenticated.');
        }

        return $currentAdminUser;
    }
}
