<?php

namespace App\Http\Controllers\Helpdesk;

use App\Actions\Helpdesk\AddTicketComment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Helpdesk\StoreTicketCommentRequest;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class TicketCommentController extends Controller
{
    public function store(StoreTicketCommentRequest $request, Ticket $ticket, AddTicketComment $addComment): RedirectResponse
    {
        $this->authorize('view', $ticket);

        $addComment->handle(
            $ticket,
            $request->user(),
            $request->validated('body'),
            $request->validated('visibility'),
            $request->validated('attachment_upload_ids', []),
        );

        Inertia::flash('success', trans('helpdesk.comment.message.created.success'));

        return to_route('tickets.show', $ticket);
    }
}
