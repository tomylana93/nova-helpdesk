<?php

return [
    'greeting' => 'Hello, :name',
    'subtitle' => 'Your helpdesk overview for :period.',
    'period' => [
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
        'month' => 'Month',
        'year' => 'Year',
        'vs_previous' => 'vs :period',
    ],
    'live' => [
        'heading' => 'Right now',
        'active' => 'Active Tickets',
        'assigned' => 'Assigned to you',
        'unassigned' => 'Unassigned',
        'pending_approval' => 'Pending Approvals',
        'sla_breached' => 'SLA Breached',
    ],
    'metric' => [
        'heading' => 'In :period',
        'created' => 'Created',
        'resolved' => 'Resolved',
        'new' => 'New',
    ],
    'compliance' => [
        'title' => 'SLA Compliance Rate',
        'caption' => 'Resolved in SLA',
        'tooltip' => ':within of :total tickets resolved on time (before their resolution due date).',
    ],
    'trend' => [
        'title' => 'Created vs Resolved',
        'created' => 'Created',
        'resolved' => 'Resolved',
        'empty' => 'No activity in this period.',
    ],
    'breakdown' => [
        'priority_title' => 'Ticket Priority Distribution',
        'status_title' => 'Active Status Distribution',
        'tickets' => 'Tickets',
        'empty' => 'No distribution data available.',
    ],
    'priority' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ],
    'status' => [
        'open' => 'Open',
        'pending_approval' => 'Pending Approval',
        'in_progress' => 'In Progress',
        'waiting_for_requester' => 'Waiting for Requester',
        'reopened' => 'Reopened',
    ],
];
