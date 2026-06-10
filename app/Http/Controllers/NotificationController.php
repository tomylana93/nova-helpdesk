<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request): Response
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate(20)
            ->through(fn ($n): array => [
                'id' => $n->id,
                'type' => $n->data['type'] ?? 'info',
                'ticket_id' => $n->data['ticket_id'] ?? null,
                'ticket_number' => $n->data['ticket_number'] ?? null,
                'subject' => $n->data['subject'] ?? null,
                'message' => $n->data['message'] ?? '',
                'read_at' => $n->read_at?->toJSON(),
                'created_at' => $n->created_at?->toJSON(),
            ]);

        return Inertia::render('Notifications', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return back();
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()
            ->unreadNotifications
            ->markAsRead();

        return back();
    }
}
