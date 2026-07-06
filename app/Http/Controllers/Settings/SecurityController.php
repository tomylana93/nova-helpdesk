<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Auth\ChangeUserPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class SecurityController extends Controller
{
    /**
     * Show the user's security settings page.
     */
    public function edit(): Response
    {
        $props = [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ];

        return Inertia::render('settings/Security', $props);
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request, ChangeUserPassword $changeUserPassword): RedirectResponse
    {
        $changeUserPassword->handle($request->user(), $request->string('password')->toString());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('settings.security.message.updated')]);

        return back();
    }
}
