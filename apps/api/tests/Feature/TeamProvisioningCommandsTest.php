<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamProvisioningCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ensure_team_syncs_all_novixa_core_users(): void
    {
        $this->artisan('workos:ensure-team', ['--password' => 'secret123'])
            ->assertSuccessful();

        $radwan = User::where('email', 'ak01redwan@novixa.dev')->firstOrFail();
        $this->assertSame('owner', $radwan->role);
        $this->assertTrue($radwan->is_active);

        $ahmed = User::where('email', 'ahmed@novixa.dev')->firstOrFail();
        $this->assertSame('owner', $ahmed->role);
        $this->assertTrue($ahmed->is_active);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'ak01redwan@novixa.dev',
            'password' => 'secret123',
        ])->assertOk();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'ahmed@novixa.dev',
            'password' => 'secret123',
        ])->assertOk();
    }

    public function test_user_set_command_creates_and_updates_credentials(): void
    {
        $this->artisan('workos:user-set', [
            'email' => 'custom@novixa.dev',
            'password' => 'customPass#2026',
            '--name' => 'Custom User',
            '--role' => 'manager',
        ])->assertSuccessful();

        $user = User::where('email', 'custom@novixa.dev')->firstOrFail();
        $this->assertSame('manager', $user->role);
        $this->assertSame('Custom User', $user->name);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'custom@novixa.dev',
            'password' => 'customPass#2026',
        ])->assertOk();
    }
}
