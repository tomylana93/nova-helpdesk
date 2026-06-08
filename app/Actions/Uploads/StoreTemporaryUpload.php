<?php

namespace App\Actions\Uploads;

use App\Models\TemporaryUpload;
use Illuminate\Http\UploadedFile;

class StoreTemporaryUpload
{
    public function handle(UploadedFile $file, string $userId): TemporaryUpload
    {
        $path = $file->store('temporary-uploads', 'public');

        return TemporaryUpload::query()->create([
            'user_id' => $userId,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }
}
