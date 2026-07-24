<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;

        // Convert string roles to enum values
        $allowedRoles = array_map(fn (string $role) => UserRole::from($role), $roles);

        if (!in_array($userRole, $allowedRoles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
