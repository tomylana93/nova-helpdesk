<?php

namespace App\Tables\Master;

use App\Http\Resources\Master\Users\UserTableResource;
use App\Models\User;
use App\Tables\AbstractTable;
use Illuminate\Database\Eloquent\Builder;

class UserTable extends AbstractTable
{
    public function resource(): string
    {
        return UserTableResource::class;
    }

    public function query(): Builder
    {
        return User::query()->select([
            'id',
            'name',
            'email',
            'status',
            'created_at',
        ]);
    }

    public function sorts(): array
    {
        return [
            'name',
            'email',
            'status',
            'created_at',
        ];
    }

    public function filters(): array
    {
        return [
            'status',
        ];
    }

    public function searchColumns(): array
    {
        return [
            'name',
            'email',
        ];
    }
}
