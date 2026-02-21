<?php

namespace App\Actions\Settings;

use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Str;

class DeleteGeneralSettingImageAction
{
    public function __construct(private readonly FilesystemManager $filesystemManager) {}

    public function handle(string $path): void
    {
        $relativePath = Str::after($path, '/storage/');
        $this->filesystemManager->disk('public')->delete($relativePath);
    }
}
