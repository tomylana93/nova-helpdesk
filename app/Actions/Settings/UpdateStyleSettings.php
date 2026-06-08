<?php

namespace App\Actions\Settings;

use App\Actions\Uploads\PromoteTemporaryUpload;
use App\Models\TemporaryUpload;
use App\Settings\StyleSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateStyleSettings
{
    public function __construct(
        private readonly PromoteTemporaryUpload $promoteTemporaryUpload,
    ) {}

    /**
     * Persist the style settings.
     *
     * @param  array{
     *     site_logo_style: string,
     *     site_auth_layout: string,
     *     site_layout: string,
     *     site_theme: string,
     *     site_font: string,
     *     site_icon_upload_id?: string|null,
     *     site_icon_alt_upload_id?: string|null,
     *     site_logo_upload_id?: string|null,
     *     site_logo_alt_upload_id?: string|null,
     *     site_favicon_upload_id?: string|null,
     *     site_icon_remove?: bool,
     *     site_icon_alt_remove?: bool,
     *     site_logo_remove?: bool,
     *     site_logo_alt_remove?: bool,
     *     site_favicon_remove?: bool
     * }  $data
     */
    public function handle(StyleSettings $styleSettings, array $data): void
    {
        DB::transaction(function () use ($styleSettings, $data): void {
            $styleSettings->site_logo_style = $data['site_logo_style'];
            $styleSettings->site_auth_layout = $data['site_auth_layout'];
            $styleSettings->site_layout = $data['site_layout'];
            $styleSettings->site_theme = $data['site_theme'];
            $styleSettings->site_font = $data['site_font'];

            $this->syncAsset(
                $styleSettings,
                'site_icon_path',
                $data['site_icon_upload_id'] ?? null,
                (bool) ($data['site_icon_remove'] ?? false),
                'settings/branding/icon',
            );
            $this->syncAsset(
                $styleSettings,
                'site_icon_alt_path',
                $data['site_icon_alt_upload_id'] ?? null,
                (bool) ($data['site_icon_alt_remove'] ?? false),
                'settings/branding/icon-alt',
            );
            $this->syncAsset(
                $styleSettings,
                'site_logo_path',
                $data['site_logo_upload_id'] ?? null,
                (bool) ($data['site_logo_remove'] ?? false),
                'settings/branding/logo',
            );
            $this->syncAsset(
                $styleSettings,
                'site_logo_alt_path',
                $data['site_logo_alt_upload_id'] ?? null,
                (bool) ($data['site_logo_alt_remove'] ?? false),
                'settings/branding/logo-alt',
            );
            $this->syncAsset(
                $styleSettings,
                'site_favicon_path',
                $data['site_favicon_upload_id'] ?? null,
                (bool) ($data['site_favicon_remove'] ?? false),
                'settings/branding/favicon',
            );

            $styleSettings->save();
        });
    }

    private function syncAsset(
        StyleSettings $styleSettings,
        string $attribute,
        ?string $temporaryUploadId,
        bool $remove,
        string $targetPrefix,
    ): void {
        $currentPath = $styleSettings->{$attribute};

        if (is_string($temporaryUploadId) && $temporaryUploadId !== '') {
            /** @var TemporaryUpload $temporaryUpload */
            $temporaryUpload = TemporaryUpload::query()->findOrFail($temporaryUploadId);
            $newPath = $this->promoteTemporaryUpload->handle($temporaryUpload, $targetPrefix);

            if (is_string($currentPath) && $currentPath !== '' && $currentPath !== $newPath) {
                Storage::disk('public')->delete($currentPath);
            }

            $styleSettings->{$attribute} = $newPath;

            return;
        }

        if ($remove && is_string($currentPath) && $currentPath !== '') {
            Storage::disk('public')->delete($currentPath);
            $styleSettings->{$attribute} = '';
        }
    }
}
