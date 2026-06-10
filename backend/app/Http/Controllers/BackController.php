<?php

namespace App\Http\Controllers;

use App\Models\UserAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class BackController extends Controller
{
    public function index(): View
    {
        return view('admin.index');
    }

    public function login(Request $request): RedirectResponse|View
    {
        if ($request->session()->has('admin_user_id')) {
            return redirect()->route('admin.home');
        }

        $showError = $request->boolean('invalid');

        return view('admin.login', compact('showError'));
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'alias' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $userAdmin = UserAdmin::query()
            ->where('alias', $credentials['alias'])
            ->first();

        if (!$userAdmin || !Hash::check($credentials['password'], (string) $userAdmin->getAttribute('password'))) {
            return redirect()
                ->route('admin.login', ['invalid' => 1])
                ->with('status_error', 'Password and alias do not match.')
                ->withInput($request->only('alias'));
        }

        if (!$userAdmin->is_enabled) {
            return redirect()
                ->route('admin.login')
                ->with('status_error', 'Your credentials are correct, but this admin user is disabled. Contact a root administrator.')
                ->withInput($request->only('alias'));
        }

        $request->session()->regenerate();
        $request->session()->put([
            'admin_user_id' => $userAdmin->id,
            'admin_user_alias' => $userAdmin->alias,
            'admin_user_role' => $userAdmin->role,
        ]);

        if ($userAdmin->must_change_password) {
            return redirect()->route('admin.password.edit');
        }

        return redirect()->route('admin.home');
    }

    public function home(): View
    {
        return view('admin.home');
    }

    public function editPassword(Request $request): RedirectResponse|View
    {
        $userAdmin = $this->currentAdminUser($request);

        if (!$userAdmin->must_change_password) {
            return redirect()->route('admin.home');
        }

        return view('admin.change-password', [
            'userAdmin' => $userAdmin,
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $userAdmin = $this->currentAdminUser($request);

        $payload = $request->validate([
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ]);

        $userAdmin->forceFill([
            'password' => $payload['password'],
            'must_change_password' => false,
        ])->save();

        return redirect()
            ->route('admin.home')
            ->with('status_success', 'Password updated successfully.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['admin_user_id', 'admin_user_alias', 'admin_user_role']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function currentAdminUser(Request $request): UserAdmin
    {
        $userAdmin = $request->attributes->get('currentAdminUser');

        if (!$userAdmin instanceof UserAdmin) {
            abort(403, 'No admin user is authenticated.');
        }

        return $userAdmin;
    }
}
