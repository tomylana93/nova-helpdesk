<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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

        return [
            ...parent::toArray($request),
            'role' => $roleName,
            'roleLabel' => $roleName !== null ? UserRole::tryFrom($roleName)?->label() : null,
            'statusLabel' => $status->label(),
        ];
    }
}
