<?php

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

test('users have starter aligned foundation columns and soft deletes', function (): void {
    expect(Schema::hasColumns('users', ['phone', 'last_login_at', 'deleted_at']))->toBeTrue()
        ->and(class_uses_recursive(User::class))->toContain(SoftDeletes::class);
});
