<?php

use App\Enums\UserRole;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('helpdesk.agents', function ($user) {
    return $user->hasRole(UserRole::ItAgent->value)
        || $user->hasRole(UserRole::SuperAdmin->value);
});
