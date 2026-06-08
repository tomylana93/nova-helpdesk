<?php

namespace App\Enums;

enum AdminPermission: string
{
    case ManageSettings = 'manage settings';
    case ViewUsers = 'view users';
    case CreateUsers = 'create users';
    case UpdateUsers = 'update users';
}
