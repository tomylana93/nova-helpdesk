<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ChangeUserPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForcePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ForcedPasswordController extends Controller
{
    /**
     * Show the forced password change page.
     */
    public function edit(): Response
    {
        return Inertia::render('auth/ForcePasswordChange', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    /**
     * Update the password and lift the forced-change requirement.
     */
    public function update(ForcePasswordRequest $request, ChangeUserPassword $changeUserPassword): RedirectResponse
    {
        $changeUserPassword->handle($request->user(), $request->string('password')->toString());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('auth.force_password.message.success')]);

        return to_route('dashboard');
    }
}
