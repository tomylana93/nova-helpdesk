<?php

namespace App\Http\Resources;

use App\Enums\GeneralStatus;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Queue $queue */
        $queue = $this->resource;

        /** @var GeneralStatus $status */
        $status = $queue->status;

        return [
            ...parent::toArray($request),
            'statusLabel' => $status->label(),
        ];
    }
}
