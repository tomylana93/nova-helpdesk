<?php

namespace App\Actions\Uploads;

use App\Models\TemporaryUpload;
use Illuminate\Support\Facades\Storage;

class DeleteTemporaryUpload
{
    public function handle(TemporaryUpload $temporaryUpload): void
    {
        Storage::disk($temporaryUpload->disk)->delete($temporaryUpload->path);
        $temporaryUpload->delete();
    }
}
