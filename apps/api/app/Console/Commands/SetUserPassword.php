<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Manually creates or resets credentials for any user account.
 */
class SetUserPassword extends Command
{
    protected $signature = 'workos:user-set
        {email : The email address of the user}
        {password : The new plain-text password}
        {--name= : The full display name (required if creating a new user)}
        {--role=owner : The role (owner, admin, manager, member, guest)}
        {--active=1 : Set active status (1 or 0)}';

    protected $description = 'Set or reset the password and status for any user account.';

    public function handle(): int
    {
        $email = strtolower((string) $this->argument('email'));
        $password = (string) $this->argument('password');
        $name = $this->option('name') ?: explode('@', $email)[0];
        $role = (string) $this->option('role');
        $active = (bool) $this->option('active');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => $role,
                'is_active' => $active,
                'locale' => 'ar',
                'timezone' => 'Asia/Riyadh',
                'theme' => 'dark',
            ]
        );

        $this->info("✔ User {$user->email} updated successfully. Role: {$user->role}, Active: ".($user->is_active ? 'Yes' : 'No'));

        return self::SUCCESS;
    }
}
