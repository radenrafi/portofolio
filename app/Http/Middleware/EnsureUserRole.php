<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  array<int, string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (!$user || $user->status !== User::STATUS_ACTIVE) {
            throw new AccessDeniedHttpException('Account is inactive.');
        }

        if (!in_array($user->role, $roles, true)) {
            throw new AccessDeniedHttpException('You are not authorized to access this resource.');
        }

        return $next($request);
    }
}

