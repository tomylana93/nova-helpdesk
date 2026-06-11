<?php

return [
    'ticket' => [
        'index' => [
            'title' => 'Tickets',
            'heading' => 'Ticket Management',
            'description' => 'View and manage all helpdesk tickets.',
        ],
        'create' => [
            'title' => 'Create Ticket',
            'heading' => 'Submit a New Ticket',
            'description' => 'Report an incident or submit a service request.',
        ],
        'show' => [
            'title' => 'Ticket Detail',
            'heading' => 'Ticket Detail',
            'description' => 'View ticket information and history.',
        ],
        'edit' => [
            'title' => 'Edit Ticket',
            'heading' => 'Edit Ticket',
            'description' => 'Update ticket details, status, and assignment.',
        ],
        'label' => [
            'ticket_number' => 'Ticket #',
            'type' => 'Type',
            'subject' => 'Subject',
            'description' => 'Description',
            'status' => 'Status',
            'priority' => 'Priority',
            'branch' => 'Branch',
            'department' => 'Department',
            'category' => 'Category',
            'requester' => 'Requester',
            'assignee' => 'Assigned To',
            'submitted_at' => 'Submitted At',
            'resolved_at' => 'Resolved At',
            'all_types' => 'All Types',
            'all_priorities' => 'All Priorities',
            'view' => 'View',
            'actions' => 'Actions',
        ],
        'view' => [
            'all' => 'All tickets',
            'mine' => 'Assigned to me',
            'unassigned' => 'Unassigned',
            'overdue' => 'Overdue',
        ],
        'transition' => [
            'in_progress' => 'Start Work',
            'waiting_for_requester' => 'Set Waiting',
            'resolved' => 'Resolve',
            'closed' => 'Close',
            'reopened' => 'Reopen',
        ],
        'placeholder' => [
            'type' => 'Select ticket type',
            'subject' => 'Enter a brief summary of the issue',
            'description' => 'Describe the issue in detail...',
            'status' => 'Select status',
            'priority' => 'Select priority',
            'branch' => 'Select branch (optional)',
            'department' => 'Select department (optional)',
            'category' => 'Select category',
            'assignee' => 'Select assignee (optional)',
        ],
        'action' => [
            'create' => 'Submit Ticket',
            'update' => 'Update Ticket',
            'edit' => 'Edit',
            'back' => 'Back to Tickets',
            'reset' => 'Reset',
            'reply' => 'Reply',
            'reopen' => 'Reopen',
            'confirm_resolved' => 'Confirm Resolved',
        ],
        'message' => [
            'created' => [
                'success' => 'Ticket submitted successfully.',
            ],
            'updated' => [
                'success' => 'Ticket updated successfully.',
            ],
            'reopened' => [
                'success' => 'Ticket reopened.',
            ],
            'confirmed' => [
                'success' => 'Ticket confirmed resolved and closed.',
            ],
        ],
    ],
    'approval' => [
        'message' => [
            'approved' => 'Ticket approved and moved to In Progress.',
            'rejected' => 'Ticket rejected and closed.',
        ],
        'label' => [
            'approve' => 'Approve',
            'reject' => 'Reject',
            'decision_note' => 'Decision Note (optional)',
            'pending' => 'Pending Approval',
        ],
    ],
    'comment' => [
        'label' => [
            'add_comment' => 'Add Comment',
            'internal_note' => 'Internal Note',
            'public' => 'Public',
            'internal' => 'Internal',
            'visibility' => 'Visibility',
            'placeholder' => 'Write your comment here...',
            'no_comments' => 'No comments yet.',
            'awaiting_reply' => 'This ticket is waiting for your reply.',
        ],
        'action' => [
            'submit' => 'Post Comment',
        ],
        'message' => [
            'created' => ['success' => 'Comment added successfully.'],
        ],
    ],
];
