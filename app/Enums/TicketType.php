<?php

namespace App\Enums;

use App\Concerns\HasOptions;

enum TicketType: string
{
    use HasOptions;

    case Incident = 'incident';
    case ServiceRequest = 'service_request';

    public function label(): string
    {
        return match ($this) {
            self::Incident => 'Incident',
            self::ServiceRequest => 'Service Request',
        };
    }

    public function prefix(): string
    {
        return match ($this) {
            self::Incident => 'INC',
            self::ServiceRequest => 'REQ',
        };
    }
}
