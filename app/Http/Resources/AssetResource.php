<?php

namespace App\Http\Resources;

use App\Enums\AssetCategory;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Asset $asset */
        $asset = $this->resource;

        /** @var AssetCategory $category */
        $category = $asset->category;

        /** @var AssetStatus $status */
        $status = $asset->status;

        return [
            ...parent::toArray($request),
            'categoryLabel' => $category->label(),
            'statusLabel' => $status->label(),
            'statusVariant' => $status->variant(),
            'branch' => BranchResource::make($this->whenLoaded('branch')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'tickets' => $this->whenLoaded('tickets', fn () => $asset->tickets
                ->map(fn (Ticket $ticket): array => [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'status' => $ticket->status->value,
                    'statusLabel' => $ticket->status->label(),
                    'statusVariant' => $ticket->status->variant(),
                    'priority' => $ticket->priority->value,
                    'priorityLabel' => $ticket->priority->label(),
                    'priorityVariant' => $ticket->priority->variant(),
                    'created_at' => $ticket->created_at?->toJSON(),
                ])
                ->all()),
        ];
    }
}
