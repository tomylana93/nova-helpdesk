<?php

namespace App\Http\Resources;

use App\Models\SlaPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SlaPolicyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SlaPolicy $policy */
        $policy = $this->resource;

        return [
            'id' => $policy->getKey(),
            'name' => $policy->name,
            'ticket_type' => $policy->ticket_type?->value,
            'ticketTypeLabel' => $policy->ticket_type?->label(),
            'priority' => $policy->priority->value,
            'priorityLabel' => $policy->priority->label(),
            'first_response_target_minutes' => $policy->first_response_target_minutes,
            'resolution_target_minutes' => $policy->resolution_target_minutes,
            'is_active' => $policy->is_active,
            'created_at' => $policy->created_at?->toJSON(),
            'updated_at' => $policy->updated_at?->toJSON(),
        ];
    }
}
