<?php

namespace App\Http\Resources;

use App\Enums\GeneralStatus;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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

        return [
            ...parent::toArray($request),
            'statusLabel' => $status->label(),
        ];
    }
}
