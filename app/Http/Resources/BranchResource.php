<?php

namespace App\Http\Resources;

use App\Enums\GeneralStatus;
use App\Models\Branch;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

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
