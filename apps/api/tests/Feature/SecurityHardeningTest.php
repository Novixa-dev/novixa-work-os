<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use App\Support\Like;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function makeNotification(User $user): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => 'task',
            'title_ar' => 'اختبار',
            'title_en' => 'Test',
        ]);
    }

    public function test_responses_carry_security_headers(): void
    {
        $response = $this->getJson('/api/v1/settings/workspace');

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = User::where('role', 'member')->first();
        $notification = $this->makeNotification($user);

        $this->actingAs($user)
            ->patchJson("/api/v1/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('unread_count', 0);

        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_user_cannot_mark_another_users_notification(): void
    {
        $owner = User::where('role', 'member')->first();
        $other = User::where('id', '!=', $owner->id)->first();
        $notification = $this->makeNotification($owner);

        $this->actingAs($other)
            ->patchJson("/api/v1/notifications/{$notification->id}/read")
            ->assertForbidden();

        $this->assertFalse($notification->fresh()->is_read);
    }

    public function test_protected_routes_require_authentication(): void
    {
        foreach (['/api/v1/tasks', '/api/v1/projects', '/api/v1/clients', '/api/v1/notifications', '/api/v1/dashboard'] as $uri) {
            $this->getJson($uri)->assertUnauthorized();
        }
    }

    public function test_weak_new_password_is_rejected(): void
    {
        $user = User::where('role', 'member')->first();

        $this->actingAs($user)
            ->patchJson('/api/v1/auth/preferences', [
                'current_password' => 'password123',
                'new_password' => 'short',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('new_password');
    }

    public function test_like_wildcards_are_escaped(): void
    {
        $this->assertSame('%100\%\_x%', Like::contains('100%_x'));

        $user = User::where('role', 'member')->first();
        $this->actingAs($user)
            ->getJson('/api/v1/search?q=%25%25')
            ->assertOk()
            ->assertJsonPath('results', []);
    }
}
