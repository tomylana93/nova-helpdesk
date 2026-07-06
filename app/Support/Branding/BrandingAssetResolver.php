<?php

namespace App\Support\Branding;

use App\Settings\StyleSettings;
use Illuminate\Support\Facades\Storage;

class BrandingAssetResolver
{
    public function __construct(
        private readonly StyleSettings $styleSettings,
    ) {}

    /**
     * @return array{
     *     icon: string,
     *     icon_alt: string,
     *     logo: string,
     *     logo_alt: string,
     *     favicon: string,
     *     favicon_any: string,
     *     favicon_svg: string,
     *     apple_touch_icon: string
     * }
     */
    public function resolve(): array
    {
        $faviconUrl = $this->uploadedAssetUrl($this->styleSettings->site_favicon_path);

        return [
            'icon' => $this->assetUrl($this->styleSettings->site_icon_path, '/assets/images/icon.png'),
            'icon_alt' => $this->assetUrl($this->styleSettings->site_icon_alt_path, '/assets/images/icon_alt.png'),
            'logo' => $this->assetUrl($this->styleSettings->site_logo_path, '/assets/images/logo.png'),
            'logo_alt' => $this->assetUrl($this->styleSettings->site_logo_alt_path, '/assets/images/logo_alt.png'),
            'favicon' => $faviconUrl ?? '/assets/images/favicon.ico',
            'favicon_any' => $faviconUrl ?? '/assets/images/favicon.ico',
            'favicon_svg' => $faviconUrl ?? '/assets/images/favicon.ico',
            'apple_touch_icon' => $faviconUrl ?? '/assets/images/apple-touch-icon.png',
        ];
    }

    /**
     * @return array{id: string, source: string, name: string, size: int, type: string|null, poster: string|null}|null
     */
    public function existingFile(?string $path, string $id): ?array
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        return [
            'id' => $id,
            'source' => $disk->url($path),
            'name' => basename($path),
            'size' => (int) $disk->size($path),
            'type' => $disk->mimeType($path) ?: null,
            'poster' => $disk->url($path),
        ];
    }

    private function uploadedAssetUrl(?string $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    private function assetUrl(?string $path, string $fallback): string
    {
        return $this->uploadedAssetUrl($path) ?? $fallback;
    }
}
