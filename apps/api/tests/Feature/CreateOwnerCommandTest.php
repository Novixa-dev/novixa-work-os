<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateOwnerCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_an_active_owner_with_a_working_password(): void
    {
        $this->artisan('workos:create-owner', ['email' => 'Admin@Novixa.dev', 'name' => 'Admin', '--password' => 'S3cure-Passw0rd!'])
            ->assertSuccessful();

        $user = User::where('email', 'admin@novixa.dev')->firstOrFail();
        $this->assertSame('owner', $user->role);
        $this->assertTrue($user->is_active);

        $this->postJson('/api/v1/auth/login', ['email' => 'admin@novixa.dev', 'password' => 'S3cure-Passw0rd!'])
            ->assertOk();
    }

    public function test_is_idempotent_and_does_not_overwrite_existing_users(): void
    {
        $this->artisan('workos:create-owner', ['email' => 'a@novixa.dev', 'name' => 'A', '--password' => 'First-Passw0rd!'])->assertSuccessful();
        $this->artisan('workos:create-owner', ['email' => 'a@novixa.dev', 'name' => 'B', '--password' => 'Other-Passw0rd!'])->assertSuccessful();

        $this->assertSame(1, User::where('email', 'a@novixa.dev')->count());
        $this->assertSame('A', User::where('email', 'a@novixa.dev')->value('name'));
    }
}
