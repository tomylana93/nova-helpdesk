<?php

namespace App\Actions\Uploads;

use App\Models\TemporaryUpload;
use Illuminate\Support\Facades\Storage;

class PromoteTemporaryUpload
{
    public function handle(TemporaryUpload $temporaryUpload, string $targetPrefix): string
    {
        $extension = pathinfo($temporaryUpload->path, PATHINFO_EXTENSION);

        if ($extension === '') {
            $extension = pathinfo($temporaryUpload->original_name, PATHINFO_EXTENSION);
        }

        $targetPath = "{$targetPrefix}-{$temporaryUpload->id}";

        if ($extension !== '') {
            $targetPath .= ".{$extension}";
        }

        Storage::disk($temporaryUpload->disk)->move($temporaryUpload->path, $targetPath);
        $temporaryUpload->delete();

        return $targetPath;
    }
}
