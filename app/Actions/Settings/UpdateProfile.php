<?php

namespace App\Actions\Settings;

use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateProfile
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data): void {
            $user->fill(Arr::only($data, ['name', 'email', 'phone']));
            $user->save();

            $avatarUploadId = $data['avatar_upload_id'] ?? null;
            $avatarRemove = (bool) ($data['avatar_remove'] ?? false);

            if (is_string($avatarUploadId) && $avatarUploadId !== '') {
                $temporaryUpload = TemporaryUpload::query()->findOrFail($avatarUploadId);

                $user->addMedia(
                    Storage::disk($temporaryUpload->disk)->path($temporaryUpload->path),
                )->toMediaCollection('avatar');

                $temporaryUpload->delete();
            } elseif ($avatarRemove) {
                $user->clearMediaCollection('avatar');
            }
        });
    }
}
