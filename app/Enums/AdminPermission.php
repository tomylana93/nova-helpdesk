<?php

namespace App\Enums;

enum AdminPermission: string
{
    case ManageSettings = 'manage settings';
    case ViewUsers = 'view users';
    case CreateUsers = 'create users';
    case UpdateUsers = 'update users';
    case ManageBranches = 'manage branches';
    case ManageDepartments = 'manage departments';
    case ManageQueues = 'manage queues';
    case ManageCategories = 'manage categories';
}
