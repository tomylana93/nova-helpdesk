<?php

namespace App\Http\Resources;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Ticket $ticket */
        $ticket = $this->resource;

        /** @var TicketStatus $status */
        $status = $ticket->status;

        /** @var TicketPriority $priority */
        $priority = $ticket->priority;

        /** @var TicketType $type */
        $type = $ticket->type;

        return [
            'id' => $ticket->getKey(),
            'ticket_number' => $ticket->ticket_number,
            'type' => $type->value,
            'typeLabel' => $type->label(),
            'subject' => $ticket->subject,
            'description' => $ticket->description,
            'status' => $status->value,
            'statusLabel' => $status->label(),
            'statusVariant' => $status->variant(),
            'priority' => $priority->value,
            'priorityLabel' => $priority->label(),
            'priorityVariant' => $priority->variant(),
            'branch_id' => $ticket->branch_id,
            'branchName' => $ticket->branch?->name,
            'department_id' => $ticket->department_id,
            'departmentName' => $ticket->department?->name,
            'requester_id' => $ticket->requester_id,
            'requesterName' => $ticket->requester->name,
            'requesterEmail' => $ticket->requester->email,
            'assigned_to' => $ticket->assigned_to,
            'assigneeName' => $ticket->assignee?->name,
            'category_id' => $ticket->category_id,
            'categoryName' => $ticket->category?->name,
            'submitted_at' => $ticket->submitted_at->toJSON(),
            'resolved_at' => $ticket->resolved_at?->toJSON(),
            'closed_at' => $ticket->closed_at?->toJSON(),
            'created_at' => $ticket->created_at?->toJSON(),
            'updated_at' => $ticket->updated_at?->toJSON(),
            'attachments' => $ticket->attachments->map(fn (TicketAttachment $attachment): array => [
                'id' => $attachment->id,
                'original_name' => $attachment->original_name,
                'size' => $attachment->size,
                'mime_type' => $attachment->mime_type,
                'url' => $attachment->url,
            ])->all(),
            'assets' => $this->whenLoaded('assets', fn () => AssetResource::collection($ticket->assets)->resolve()),
        ];
    }
}
