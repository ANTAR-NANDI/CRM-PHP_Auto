<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Require every listed permission. Separate permissions with the middleware
     * parameter syntax, for example: permission:products.manage.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active || (! $user->isAdministrator() && ! $user->hasAllPermissions($permissions))) {
            abort(403, 'You do not have permission to access this module.');
        }

        return $next($request);
    }
}
