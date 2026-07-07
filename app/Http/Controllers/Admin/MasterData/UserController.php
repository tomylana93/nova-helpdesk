<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Actions\MasterData\Users\CreateUser;
use App\Actions\MasterData\Users\DeleteUser;
use App\Actions\MasterData\Users\GetUserFormOptions;
use App\Actions\MasterData\Users\RestoreUser;
use App\Actions\MasterData\Users\UpdateUser;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MasterData\ExportUserRequest;
use App\Http\Requests\Admin\MasterData\StoreUserRequest;
use App\Http\Requests\Admin\MasterData\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Tables\MasterData\UserTable;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function export(ExportUserRequest $request): BinaryFileResponse
    {
        $query = User::query()->latest();
        $ids = $request->ids();

        if ($ids !== null) {
            $query->whereIn('id', $ids);
        }

        return Excel::download(new UsersExport($query, $request->exportColumns()), 'users.xlsx');
    }

    public function store(StoreUserRequest $request, CreateUser $createUser): RedirectResponse
    {
        $createUser->handle($request->userData());

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

        $updateUser->handle($user, $request->userData());

        Inertia::flash('success', trans('admin.master_data.user.message.updated.success'));

        return to_route('admin.master-data.users.index');
    }

    public function destroy(User $user, DeleteUser $deleteUser): RedirectResponse
    {
        $this->authorize('delete', $user);

        $deleteUser->handle($user);

        Inertia::flash('success', trans('admin.master_data.user.message.deleted.success'));

        return to_route('admin.master-data.users.index');
    }

    public function restore(User $user, RestoreUser $restoreUser): RedirectResponse
    {
        $this->authorize('restore', $user);

        $restoreUser->handle($user);

        Inertia::flash('success', trans('admin.master_data.user.message.restored.success'));

        return to_route('admin.master-data.users.index');
    }
}
