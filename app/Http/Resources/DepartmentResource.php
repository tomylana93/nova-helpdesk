<?php

namespace App\Http\Resources;

use App\Enums\GeneralStatus;
use App\Models\Department;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Department $department */
        $department = $this->resource;

        /** @var GeneralStatus $status */
        $status = $department->status;

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
            'statusLabel' => $status->label(),
        ];
    }
}
