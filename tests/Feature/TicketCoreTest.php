<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Queue;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use App\Models\User;

test('it can create a ticket with relationships and casts', function (): void {
    $branch = Branch::factory()->create();
    $department = Department::factory()->create(['branch_id' => $branch->id]);
    $requester = User::factory()->create();
    $assignee = User::factory()->create();
    $queue = Queue::factory()->create();
    $category = TicketCategory::factory()->create();

    $ticket = Ticket::factory()->create([
        'type' => TicketType::Incident,
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'requester_id' => $requester->id,
        'assigned_to' => $assignee->id,
        'queue_id' => $queue->id,
        'category_id' => $category->id,
        'priority' => TicketPriority::High,
        'status' => TicketStatus::New,
        'subject' => 'Unable to connect to printer',
        'description' => 'I cannot print from the office wifi',
    ]);

    expect($ticket->ticket_number)->toBe('INC-00001')
        ->and($ticket->type)->toBe(TicketType::Incident)
        ->and($ticket->priority)->toBe(TicketPriority::High)
        ->and($ticket->status)->toBe(TicketStatus::New)
        ->and($ticket->branch->id)->toBe($branch->id)
        ->and($ticket->department->id)->toBe($department->id)
        ->and($ticket->requester->id)->toBe($requester->id)
        ->and($ticket->assignee->id)->toBe($assignee->id)
        ->and($ticket->queue->id)->toBe($queue->id)
        ->and($ticket->category->id)->toBe($category->id);
});

test('it generates sequential ticket numbers based on ticket type', function (): void {
    $requester = User::factory()->create();

    // Create first incident
    $incident1 = Ticket::factory()->create([
        'type' => TicketType::Incident,
        'requester_id' => $requester->id,
    ]);
    expect($incident1->ticket_number)->toBe('INC-00001');

    // Create first request
    $request1 = Ticket::factory()->create([
        'type' => TicketType::ServiceRequest,
        'requester_id' => $requester->id,
    ]);
    expect($request1->ticket_number)->toBe('REQ-00001');

    // Create second incident
    $incident2 = Ticket::factory()->create([
        'type' => TicketType::Incident,
        'requester_id' => $requester->id,
    ]);
    expect($incident2->ticket_number)->toBe('INC-00002');

    // Create second request
    $request2 = Ticket::factory()->create([
        'type' => TicketType::ServiceRequest,
        'requester_id' => $requester->id,
    ]);
    expect($request2->ticket_number)->toBe('REQ-00002');
});

test('it can add comments to a ticket and filter public comments', function (): void {
    $ticket = Ticket::factory()->create();
    $user = User::factory()->create();

    $publicComment = TicketComment::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'visibility' => 'public',
        'body' => 'This is a public comment',
    ]);

    $internalComment = TicketComment::factory()->create([
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'visibility' => 'internal',
        'body' => 'This is an internal note',
    ]);

    expect($ticket->comments)->toHaveCount(2);

    $publicComments = $ticket->comments()->public()->get();
    expect($publicComments)->toHaveCount(1)
        ->and($publicComments->first()->id)->toBe($publicComment->id)
        ->and($publicComments->first()->body)->toBe('This is a public comment');
});

test('it can associate attachments with a ticket', function (): void {
    $ticket = Ticket::factory()->create();

    $attachment = TicketAttachment::factory()->create([
        'ticket_id' => $ticket->id,
        'file_path' => 'attachments/test.pdf',
        'original_name' => 'test.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024,
    ]);

    expect($ticket->attachments)->toHaveCount(1)
        ->and($ticket->attachments->first()->id)->toBe($attachment->id)
        ->and($ticket->attachments->first()->url)->toContain('attachments/test.pdf');
});
