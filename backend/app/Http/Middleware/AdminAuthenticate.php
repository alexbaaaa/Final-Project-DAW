<?php

namespace App\Http\Middleware;

use App\Models\UserAdmin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $userAdminId = $request->session()->get('admin_user_id');

        if (!$userAdminId) {
            return redirect()->route('admin.login');
        }

        $userAdmin = UserAdmin::query()->find($userAdminId);

        if (!$userAdmin) {
            $request->session()->forget(['admin_user_id', 'admin_user_alias', 'admin_user_role']);

            return redirect()
                ->route('admin.login')
                ->with('status_error', 'Your admin session is no longer valid.');
        }

        if (!$userAdmin->is_enabled) {
            $request->session()->forget(['admin_user_id', 'admin_user_alias', 'admin_user_role']);

            return redirect()
                ->route('admin.login')
                ->with('status_error', 'Your credentials are valid, but this admin user is disabled.');
        }

        $request->attributes->set('currentAdminUser', $userAdmin);
        View::share('currentAdminUser', $userAdmin);

        if (
            $userAdmin->must_change_password
            && !$request->routeIs('admin.password.*')
            && !$request->routeIs('admin.logout')
        ) {
            return redirect()->route('admin.password.edit');
        }

        return $next($request);
    }
}
