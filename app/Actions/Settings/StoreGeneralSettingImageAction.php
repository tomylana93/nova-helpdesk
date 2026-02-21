<?php

namespace App\Actions\Settings;

use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;

class StoreGeneralSettingImageAction
{
    public function __construct(private readonly FilesystemManager $filesystemManager) {}

    public function handle(string $type, UploadedFile $file): string
    {
        $path = $file->store("settings/{$type}", 'public');

        return $this->filesystemManager->url($path);
    }
}
