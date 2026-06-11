<?php

use App\Enums\TicketStatus;

test('exposes the simplified status set', function (): void {
    expect(array_map(fn (TicketStatus $s): string => $s->value, TicketStatus::cases()))
        ->toBe([
            'open',
            'pending_approval',
            'in_progress',
            'waiting_for_requester',
            'resolved',
            'closed',
            'reopened',
        ]);
});

test('open and closed states drive isOpen correctly', function (TicketStatus $status, bool $isOpen): void {
    expect($status->isOpen())->toBe($isOpen);
})->with([
    'open' => [TicketStatus::Open, true],
    'pending approval' => [TicketStatus::PendingApproval, true],
    'in progress' => [TicketStatus::InProgress, true],
    'waiting for requester' => [TicketStatus::WaitingForRequester, true],
    'reopened' => [TicketStatus::Reopened, true],
    'resolved' => [TicketStatus::Resolved, false],
    'closed' => [TicketStatus::Closed, false],
]);

test('allows only legal transitions', function (TicketStatus $from, TicketStatus $to): void {
    expect($from->canTransitionTo($to))->toBeTrue();
})->with([
    'open -> in progress' => [TicketStatus::Open, TicketStatus::InProgress],
    'open -> waiting requester' => [TicketStatus::Open, TicketStatus::WaitingForRequester],
    'open -> closed' => [TicketStatus::Open, TicketStatus::Closed],
    'pending -> in progress (approve)' => [TicketStatus::PendingApproval, TicketStatus::InProgress],
    'pending -> closed (reject)' => [TicketStatus::PendingApproval, TicketStatus::Closed],
    'in progress -> resolved' => [TicketStatus::InProgress, TicketStatus::Resolved],
    'in progress -> waiting requester' => [TicketStatus::InProgress, TicketStatus::WaitingForRequester],
    'waiting requester -> in progress' => [TicketStatus::WaitingForRequester, TicketStatus::InProgress],
    'resolved -> closed' => [TicketStatus::Resolved, TicketStatus::Closed],
    'resolved -> reopened' => [TicketStatus::Resolved, TicketStatus::Reopened],
    'closed -> reopened' => [TicketStatus::Closed, TicketStatus::Reopened],
    'reopened -> in progress' => [TicketStatus::Reopened, TicketStatus::InProgress],
]);

test('rejects illegal transitions', function (TicketStatus $from, TicketStatus $to): void {
    expect($from->canTransitionTo($to))->toBeFalse();
})->with([
    'open -> resolved (skip work)' => [TicketStatus::Open, TicketStatus::Resolved],
    'open -> pending approval' => [TicketStatus::Open, TicketStatus::PendingApproval],
    'closed -> in progress' => [TicketStatus::Closed, TicketStatus::InProgress],
    'resolved -> in progress' => [TicketStatus::Resolved, TicketStatus::InProgress],
    'pending -> resolved' => [TicketStatus::PendingApproval, TicketStatus::Resolved],
    'same state' => [TicketStatus::InProgress, TicketStatus::InProgress],
]);

test('activeCases excludes resolved and closed', function (): void {
    $active = TicketStatus::activeCases();

    expect($active)->not->toContain(TicketStatus::Resolved)
        ->and($active)->not->toContain(TicketStatus::Closed)
        ->and($active)->toContain(TicketStatus::Open)
        ->and($active)->toContain(TicketStatus::InProgress);
});
