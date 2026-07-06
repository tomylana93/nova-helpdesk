<?php

namespace App\Http\Resources;

use App\Enums\GeneralStatus;
use App\Models\TicketCategory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class TicketCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var TicketCategory $ticketCategory */
        $ticketCategory = $this->resource;

        /** @var GeneralStatus $status */
        $status = $ticketCategory->status;

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
            'parentName' => $ticketCategory->relationLoaded('parent') && $ticketCategory->parent instanceof TicketCategory ? $ticketCategory->parent->name : null,
        ];
    }
}
