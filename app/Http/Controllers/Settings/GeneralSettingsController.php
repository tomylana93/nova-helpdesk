<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\DeleteGeneralSettingImageAction;
use App\Actions\Settings\General\UpdateGeneralSettingsAction;
use App\Actions\Settings\StoreGeneralSettingImageAction;
use App\Enums\HeaderStyleEnum;
use App\Enums\LanguageEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\General\StoreGeneralSettingImageRequest;
use App\Http\Requests\Settings\General\UpdateGeneralSettingsRequest;
use App\Settings\GeneralSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GeneralSettingsController extends Controller
{
    private const array IMAGE_FIELDS = [
        'app_logo',
        'app_icon',
        'app_favicon',
    ];

    public function edit(GeneralSettings $settings): Response
    {
        return Inertia::render('settings/general/Edit', [
            'generalSettings' => $settings,
            'languages' => LanguageEnum::options(),
            'headerStyles' => HeaderStyleEnum::options(),
        ]);
    }

    public function update(UpdateGeneralSettingsRequest $request, GeneralSettings $settings, UpdateGeneralSettingsAction $action): RedirectResponse
    {
        $action->handle($request->validated(), $settings);

        return to_route('settings.general.edit')->with('success', __('settings.general.messages.updated'));
    }

    public function storeImage(string $type, StoreGeneralSettingImageRequest $request, StoreGeneralSettingImageAction $action): JsonResponse
    {

        abort_unless(in_array($type, self::IMAGE_FIELDS, true), 404);

        $path = $action->handle($type, $request->file('file'));

        return new JsonResponse([
            'path' => $path,
        ]);
    }

    public function destroyImage(string $type, Request $request, DeleteGeneralSettingImageAction $action): JsonResponse
    {
        abort_unless(in_array($type, self::IMAGE_FIELDS, true), 404);

        $action->handle($request->input('path'));

        return new JsonResponse([]);
    }
}
