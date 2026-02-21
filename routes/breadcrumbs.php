<?php

use App\Models\User;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

Breadcrumbs::for('dashboard', function (BreadcrumbTrail $trail): void {
    $trail->push(__('breadcrumbs.dashboard'), route('dashboard'));
});

Breadcrumbs::for('profile.edit', function (BreadcrumbTrail $trail): void {
    $trail->parent('dashboard');
    $trail->push(__('breadcrumbs.profile'), route('profile.edit'));
});

Breadcrumbs::for('user-password.edit', function (BreadcrumbTrail $trail): void {
    $trail->parent('dashboard');
    $trail->push(__('breadcrumbs.password'), route('user-password.edit'));
});

Breadcrumbs::for('two-factor.show', function (BreadcrumbTrail $trail): void {
    $trail->parent('dashboard');
    $trail->push(__('breadcrumbs.two_factor'), route('two-factor.show'));
});

Breadcrumbs::for('master.index', function (BreadcrumbTrail $trail): void {
    $trail->parent('dashboard');
    $trail->push(__('breadcrumbs.master'), route('master.index'));
});

Breadcrumbs::for('master.users.index', function (BreadcrumbTrail $trail): void {
    $trail->parent('master.index');
    $trail->push(__('breadcrumbs.users'), route('master.users.index'));
});

Breadcrumbs::for('master.users.create', function (BreadcrumbTrail $trail): void {
    $trail->parent('master.users.index');
    $trail->push(__('breadcrumbs.user_create'), route('master.users.create'));
});

Breadcrumbs::for('master.users.edit', function (BreadcrumbTrail $trail, User $user): void {
    $trail->parent('master.users.index');
    $trail->push(__('breadcrumbs.user_edit'), route('master.users.edit', $user));
});
