<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Route names reachable while a forced password change is pending.
     *
     * @var list<string>
     */
    private const array ALLOWLIST = [
        'password.force.edit',
        'password.force.update',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->must_change_password) {
            return $next($request);
        }

        if (in_array($request->route()?->getName(), self::ALLOWLIST, true)) {
            return $next($request);
        }

        return to_route('password.force.edit');
    }
}
