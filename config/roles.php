<?php

use App\Enums\AdminPermission;
use App\Enums\UserRole;

return [
    'permissions' => [
        UserRole::SuperAdmin->value => array_map(
            static fn (AdminPermission $permission): string => $permission->value,
            AdminPermission::cases(),
        ),
        UserRole::ItAgent->value => [
            AdminPermission::ViewTickets->value,
            AdminPermission::CreateTickets->value,
            AdminPermission::UpdateTickets->value,
            AdminPermission::ManageApprovals->value,
            AdminPermission::ViewReports->value,
        ],
        UserRole::Auditor->value => [
            AdminPermission::ViewTickets->value,
            AdminPermission::CreateTickets->value,
            AdminPermission::ViewReports->value,
        ],
        UserRole::Requester->value => [
            AdminPermission::ViewTickets->value,
            AdminPermission::CreateTickets->value,
        ],
    ],
];
