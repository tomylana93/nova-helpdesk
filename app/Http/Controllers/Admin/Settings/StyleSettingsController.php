<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Actions\Settings\UpdateStyleSettings;
use App\Enums\SiteAuthLayout;
use App\Enums\SiteFont;
use App\Enums\SiteLayout;
use App\Enums\SiteLogoStyle;
use App\Enums\SiteTheme;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateStyleSettingsRequest;
use App\Settings\StyleSettings;
use App\Support\Branding\BrandingAssetResolver;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StyleSettingsController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(
        StyleSettings $styleSettings,
        BrandingAssetResolver $brandingAssetResolver,
    ): Response {
        $this->authorize('view', StyleSettings::class);

        $icon = $brandingAssetResolver->existingFile($styleSettings->site_icon_path, 'icon');
        $iconAlt = $brandingAssetResolver->existingFile($styleSettings->site_icon_alt_path, 'icon_alt');
        $logo = $brandingAssetResolver->existingFile($styleSettings->site_logo_path, 'logo');
        $logoAlt = $brandingAssetResolver->existingFile($styleSettings->site_logo_alt_path, 'logo_alt');
        $favicon = $brandingAssetResolver->existingFile($styleSettings->site_favicon_path, 'favicon');

        return Inertia::render('admin/settings/style/Edit', [
            'styleSettings' => $styleSettings,
            'logoStyleOptions' => SiteLogoStyle::options(),
            'authLayoutOptions' => SiteAuthLayout::options(),
            'layoutOptions' => SiteLayout::options(),
            'themeOptions' => SiteTheme::options(),
            'fontOptions' => SiteFont::options([
                'heading' => 'heading',
                'body' => 'body',
            ]),
            'brandingFiles' => [
                'icon' => $icon ? [$icon] : [],
                'icon_alt' => $iconAlt ? [$iconAlt] : [],
                'logo' => $logo ? [$logo] : [],
                'logo_alt' => $logoAlt ? [$logoAlt] : [],
                'favicon' => $favicon ? [$favicon] : [],
            ],
        ]);
    }

    /**
     * Update the style settings.
     */
    public function update(
        UpdateStyleSettingsRequest $request,
        StyleSettings $styleSettings,
        UpdateStyleSettings $updateStyleSettings,
    ): RedirectResponse {
        $this->authorize('update', StyleSettings::class);

        $updateStyleSettings->handle($styleSettings, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('admin.settings.style.status.saved'),
        ]);

        return to_route('admin.settings.style.edit');
    }
}
