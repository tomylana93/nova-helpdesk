<?php

namespace App\Http\Resources;

use App\Enums\GeneralStatus;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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

        return [
            ...parent::toArray($request),
            'statusLabel' => $status->label(),
            'parentName' => $ticketCategory->relationLoaded('parent') && $ticketCategory->parent instanceof TicketCategory ? $ticketCategory->parent->name : null,
        ];
    }
}
