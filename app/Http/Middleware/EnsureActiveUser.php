<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        /** @var UserStatus $status */
        $status = $user->status;

        if ($status !== UserStatus::Active) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return to_route('login')->withErrors([
                Fortify::username() => $status->message(),
            ]);
        }

        return $next($request);
    }
}
