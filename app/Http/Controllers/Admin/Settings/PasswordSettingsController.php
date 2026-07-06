<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Actions\Settings\UpdatePasswordSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdatePasswordSettingsRequest;
use App\Settings\PasswordSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordSettingsController extends Controller
{
    public function edit(): Response
    {
        $this->authorize('view', PasswordSettings::class);

        return Inertia::render('admin/settings/password/Edit', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    public function update(
        UpdatePasswordSettingsRequest $request,
        PasswordSettings $passwordSettings,
        UpdatePasswordSettings $updatePasswordSettings,
    ): RedirectResponse {
        $this->authorize('update', PasswordSettings::class);

        $updatePasswordSettings->handle($passwordSettings, $request->settingsData());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('admin.settings.password.status.saved'),
        ]);

        return to_route('admin.settings.password.edit');
    }
}
