<?php

namespace App\Console\Commands;

use App\Actions\MasterData\Users\CreateUser;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

#[Signature('init:superadmin')]
#[Description('Initialize the default superadmin user from application configuration')]
class InitSuperadminCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(CreateUser $createUser): int
    {
        $name = $this->defaultName();
        $email = $this->defaultEmail();

        if ($name === '') {
            $this->error('The superadmin name is empty. Configure APP_NAME first.');

            return self::FAILURE;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('The superadmin email is invalid. Configure MAIL_FROM_ADDRESS first.');

            return self::FAILURE;
        }

        $existingUser = User::query()
            ->where('email', $email)
            ->first();

        if ($existingUser !== null) {
            $this->warn("Superadmin user [{$email}] already exists.");

            return self::SUCCESS;
        }

        Role::findOrCreate(UserRole::SuperAdmin->value, 'web');

        $createUser->handle([
            'name' => $name,
            'email' => $email,
            'role' => UserRole::SuperAdmin->value,
        ]);

        $this->info("Superadmin user [{$email}] created with the configured default password.");

        return self::SUCCESS;
    }

    private function defaultName(): string
    {
        return trim((string) config('nova.superadmin.name'));
    }

    private function defaultEmail(): string
    {
        return trim((string) config('nova.superadmin.email'));
    }
}
