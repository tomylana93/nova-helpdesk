<?php

namespace App\Http\Responses;

use App\Models\User;
use App\Settings\PasswordSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function __construct(
        private readonly PasswordSettings $passwordSettings,
    ) {}

    /**
     * @param  Request  $request
     * @return Response
     */
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        $user = $request->user();

        if (
            $user instanceof User
            && Hash::check($this->passwordSettings->default_user_password, $user->password)
        ) {
            Inertia::flash('toast', [
                'type' => 'warning',
                'message' => __('auth.login.message.default_password_warning'),
            ]);
        }

        return redirect()->intended(Fortify::redirects('login'));
    }
}
