<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        $roleName = $user->getRoleNames()->first();

        /** @var UserStatus $status */
        $status = $user->status;

        $attributes = parent::toArray($request);
        if ($attributes instanceof Arrayable) {
            $attributes = $attributes->toArray();
        }

        if ($attributes instanceof JsonSerializable) {
            $attributes = $attributes->jsonSerialize();
        }

        if (! is_array($attributes)) {
            $attributes = [];
        }

        return [
            ...$attributes,
            'role' => $roleName,
            'roleLabel' => $roleName !== null ? UserRole::tryFrom($roleName)?->label() : null,
            'statusLabel' => $status->label(),
            'branchName' => $user->relationLoaded('branch') && $user->branch instanceof Branch ? $user->branch->name : null,
            'departmentName' => $user->relationLoaded('department') && $user->department instanceof Department ? $user->department->name : null,
        ];
    }
}
