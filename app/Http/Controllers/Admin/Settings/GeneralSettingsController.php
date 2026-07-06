<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Actions\Settings\UpdateGeneralSettings;
use App\Enums\SiteLocale;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateGeneralSettingsRequest;
use App\Settings\GeneralSettings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GeneralSettingsController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GeneralSettings $generalSettings): Response
    {
        $this->authorize('view', GeneralSettings::class);

        return Inertia::render('admin/settings/general/Edit', [
            'generalSettings' => $generalSettings,
            'localeOptions' => SiteLocale::options(['icon' => 'flag']),
        ]);
    }

    /**
     * Update the general settings.
     */
    public function update(
        UpdateGeneralSettingsRequest $request,
        GeneralSettings $generalSettings,
        UpdateGeneralSettings $updateGeneralSettings,
    ): RedirectResponse {
        $this->authorize('update', GeneralSettings::class);

        $updateGeneralSettings->handle($generalSettings, $request->settingsData());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('admin.settings.general.status.saved'),
        ]);

        return to_route('admin.settings.general.edit');
    }
}
