<?php

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
