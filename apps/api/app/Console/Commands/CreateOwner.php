<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Bootstraps the first workspace owner on a fresh production install
 * (no demo data is seeded in production).
 */
class CreateOwner extends Command
{
    protected $signature = 'workos:create-owner
        {email : Login e-mail of the owner}
        {name : Display name}
        {--password= : Password (a strong random one is generated when omitted)}
        {--print-password : Print the generated password once}';

    protected $description = 'Create the first workspace owner (idempotent: refuses when the e-mail exists).';

    public function handle(): int
    {
        $email = strtolower((string) $this->argument('email'));

        if (User::where('email', $email)->exists()) {
            $this->warn("User {$email} already exists — nothing changed.");

            return self::SUCCESS;
        }

        $password = (string) ($this->option('password') ?: Str::password(20, symbols: false));

        User::create([
            'name' => $this->argument('name'),
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'owner',
            'locale' => 'ar',
            'timezone' => 'Asia/Riyadh',
            'theme' => 'system',
            'is_active' => true,
        ]);

        $this->info("Owner {$email} created.");

        if ($this->option('print-password')) {
            $this->line("PASSWORD={$password}");
        }

        return self::SUCCESS;
    }
}
