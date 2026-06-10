<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum UserRole: string
{
    use HasOptions;

    case SuperAdmin = 'super_admin';
    case ItAgent = 'it_agent';
    case Requester = 'requester';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => __('user.role.super_admin'),
            self::ItAgent => __('user.role.it_agent'),
            self::Requester => __('user.role.requester'),
        };
    }
}
