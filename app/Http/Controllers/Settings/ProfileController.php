<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\TemporaryUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $avatarMedia = $user->getFirstMedia('avatar');

        return Inertia::render('settings/Profile', [
            'status' => $request->session()->get('status'),
            'avatarFile' => $avatarMedia ? [
                [
                    'id' => 'avatar',
                    'source' => $avatarMedia->getUrl(),
                    'name' => $avatarMedia->file_name,
                    'size' => (int) $avatarMedia->size,
                    'type' => $avatarMedia->mime_type,
                    'poster' => $avatarMedia->getUrl(),
                ],
            ] : [],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $request): void {
            $user->fill($request->validated());
            $user->save();

            $avatarUploadId = $request->input('avatar_upload_id');
            $avatarRemove = $request->boolean('avatar_remove');

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

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('profile.edit');
    }
}
