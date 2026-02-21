<?php

namespace App\Http\Resources\Master\Users;

use App\Enums\UserStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->status;
        $statusValue = $status instanceof UserStatusEnum
            ? $status->value
            : (string) $status;
        $resolvedStatus = $status instanceof UserStatusEnum
            ? $status
            : UserStatusEnum::tryFrom($statusValue);

        return [
            'id' => (string) $this->id,
            'name' => (string) $this->name,
            'email' => (string) $this->email,
            'status' => $resolvedStatus?->label() ?? $statusValue,
            'status_value' => $statusValue,
        ];
    }
}
