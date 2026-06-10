<?php

namespace App\Http\Resources;

use App\Enums\GeneralStatus;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Branch $branch */
        $branch = $this->resource;

        /** @var GeneralStatus $status */
        $status = $branch->status;

        return [
            ...parent::toArray($request),
            'statusLabel' => $status->label(),
        ];
    }
}
