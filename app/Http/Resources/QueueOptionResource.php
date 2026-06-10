<?php

namespace App\Http\Resources;

use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueOptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Queue $queue */
        $queue = $this->resource;

        return [
            'value' => $queue->id,
            'label' => $queue->name,
        ];
    }
}
