<?php

namespace App\Http\Middleware;

use App\Models\UserAdmin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthorize
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $userAdmin = $request->attributes->get('currentAdminUser');

        if (!$userAdmin instanceof UserAdmin || !$userAdmin->canAccess($permission)) {
            abort(403, 'You do not have permission to access this admin section.');
        }

        return $next($request);
    }
}
