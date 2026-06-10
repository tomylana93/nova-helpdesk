<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Actions\MasterData\Users\CreateUser;
use App\Actions\MasterData\Users\GetUserFormOptions;
use App\Actions\MasterData\Users\UpdateUser;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MasterData\StoreUserRequest;
use App\Http\Requests\Admin\MasterData\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Tables\MasterData\UserTable;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(UserTable $table): Response
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('admin/master-data/users/Index', [
            'table' => Inertia::defer(fn (): array => $table->toPayload()),
        ]);
    }

    public function create(GetUserFormOptions $formOptions): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('admin/master-data/users/Create', [
            'userRoleOptions' => UserRole::options(),
            ...$formOptions->handle(),
        ]);
    }

    public function store(StoreUserRequest $request, CreateUser $createUser): RedirectResponse
    {
        $createUser->handle($request->validated());

        Inertia::flash('success', trans('admin.master_data.user.message.created.success'));

        return to_route('admin.master-data.users.index');
    }

    public function show(User $user): Response
    {
        $this->authorize('view', $user);

        return Inertia::render('admin/master-data/users/Show', [
            'user' => UserResource::make($user->load(['branch', 'department']))->resolve(),
        ]);
    }

    public function edit(User $user, GetUserFormOptions $formOptions): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('admin/master-data/users/Edit', [
            'user' => UserResource::make($user)->resolve(),
            'userRoleOptions' => UserRole::options(),
            'userStatusOptions' => UserStatus::options(),
            ...$formOptions->handle(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUser $updateUser): RedirectResponse
    {
        $this->authorize('update', $user);

        $updateUser->handle($user, $request->validated());

        Inertia::flash('success', trans('admin.master_data.user.message.updated.success'));

        return to_route('admin.master-data.users.index');
    }
}
