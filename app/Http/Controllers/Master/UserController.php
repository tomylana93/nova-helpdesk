<?php

namespace App\Http\Controllers\Master;

use App\Actions\Master\Users\CreateUserAction;
use App\Actions\Master\Users\UpdateUserAction;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\Users\StoreUserRequest;
use App\Http\Requests\Master\Users\UpdateUserRequest;
use App\Http\Resources\Master\Users\UserResource;
use App\Models\User;
use App\Tables\Master\UserTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request, UserTable $userTable): Response
    {
        return Inertia::render('master/users/Index', [
            'users' => $userTable->request($request),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('master/users/Create');
    }

    public function store(
        StoreUserRequest $request,
        CreateUserAction $action
    ): RedirectResponse {
        $action->handle($request->validated());

        return to_route('master.users.index');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('master/users/Edit', [
            'user' => UserResource::make($user)->resolve(),
            'statusOptions' => UserStatusEnum::options(),
        ]);
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        UpdateUserAction $action
    ): RedirectResponse {
        $action->handle($user, $request->validated());

        return to_route('master.users.index');
    }
}
