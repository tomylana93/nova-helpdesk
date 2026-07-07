<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\UpdateProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function update(ProfileUpdateRequest $request, UpdateProfile $updateProfile): RedirectResponse
    {
        $updateProfile->handle($request->user(), $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('profile.edit');
    }
}
