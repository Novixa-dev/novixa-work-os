<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOSApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'ahmed@novixa.dev',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role', 'locale'],
                'token',
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'ahmed@novixa.dev',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::where('email', 'omar@novixa.dev')->first();
        $user->update(['is_active' => false]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'omar@novixa.dev',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_fetch_profile_and_update_preferences(): void
    {
        $user = User::where('email', 'omar@novixa.dev')->first();

        $response = $this->actingAs($user)->getJson('/api/v1/auth/me');
        $response->assertStatus(200)
            ->assertJsonPath('user.email', 'omar@novixa.dev');

        $updateResponse = $this->actingAs($user)->patchJson('/api/v1/auth/preferences', [
            'locale' => 'en',
            'theme' => 'light',
            'timezone' => 'Asia/Aden',
        ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('user.locale', 'en')
            ->assertJsonPath('user.theme', 'light');

        $this->assertEquals('en', $user->fresh()->locale);
    }

    public function test_admin_can_create_user_and_member_cannot(): void
    {
        $admin = User::where('role', 'admin')->first();
        $member = User::where('role', 'member')->first();

        // Admin can create
        $response = $this->actingAs($admin)->postJson('/api/v1/users', [
            'name' => 'مهندس جديد (New Engineer)',
            'email' => 'engineer.new@novixa.dev',
            'password' => 'Secret12345!',
            'role' => 'member',
            'job_title' => 'Backend Specialist',
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'engineer.new@novixa.dev']);

        // Member cannot create
        $failResponse = $this->actingAs($member)->postJson('/api/v1/users', [
            'name' => 'مستخدم غير مصرح',
            'email' => 'unauthorized@novixa.dev',
            'password' => 'Secret12345!',
            'role' => 'member',
        ]);
        $failResponse->assertStatus(403);
    }

    public function test_manager_can_create_project_and_calculate_progress(): void
    {
        $manager = User::where('role', 'manager')->first();

        $response = $this->actingAs($manager)->postJson('/api/v1/projects', [
            'code' => 'TEST-PRJ',
            'name_ar' => 'مشروع اختبار الجودة',
            'name_en' => 'QA Test Project',
            'type' => 'internal_product',
            'status' => 'active',
            'priority' => 'high',
            'lead_id' => $manager->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('project.code', 'TEST-PRJ');

        $projectId = $response->json('project.id');

        // Add 2 tasks: 1 done, 1 todo
        $t1 = Task::create([
            'code' => 'NW-TEST1',
            'title_ar' => 'مهمة منجزة',
            'project_id' => $projectId,
            'status' => 'done',
        ]);

        $t2 = Task::create([
            'code' => 'NW-TEST2',
            'title_ar' => 'مهمة قيد الانتظار',
            'project_id' => $projectId,
            'status' => 'todo',
        ]);

        $project = Project::find($projectId);
        $percentage = $project->recalculateProgress();

        $this->assertEquals(50, $percentage);
    }

    public function test_task_creation_generates_code_and_tracks_activity(): void
    {
        $user = User::where('role', 'member')->first();

        $response = $this->actingAs($user)->postJson('/api/v1/tasks', [
            'title_ar' => 'مهمة برمجية جديدة من الاختبار التلقائي',
            'title_en' => 'Automated test created task',
            'priority' => 'high',
            'status' => 'todo',
            'assignee_id' => $user->id,
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['task' => ['id', 'code', 'title_ar']]);

        $code = $response->json('task.code');
        $this->assertStringStartsWith('NW-', $code);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'task.created',
        ]);
    }

    public function test_task_dependency_rejects_cycles(): void
    {
        $user = User::where('role', 'manager')->first();

        $t1 = Task::create(['code' => 'NW-CYC1', 'title_ar' => 'مهمة 1', 'reporter_id' => $user->id]);
        $t2 = Task::create(['code' => 'NW-CYC2', 'title_ar' => 'مهمة 2', 'reporter_id' => $user->id]);

        // t1 depends on t2 (t2 blocks t1)
        $resp1 = $this->actingAs($user)->postJson("/api/v1/tasks/{$t1->id}/dependencies", [
            'depends_on_task_id' => $t2->id,
        ]);
        $resp1->assertStatus(201);

        // Attempting to make t2 depend on t1 should fail (cycle!)
        $resp2 = $this->actingAs($user)->postJson("/api/v1/tasks/{$t2->id}/dependencies", [
            'depends_on_task_id' => $t1->id,
        ]);

        $resp2->assertStatus(422)
            ->assertJsonPath('message', 'تعذر إضافة الاعتمادية: سيؤدي ذلك إلى تشكيل حلقة اعتماد دائرية مغلقة (Cycle detected).');
    }

    public function test_opportunity_won_can_be_converted_to_delivery_project(): void
    {
        $manager = User::where('role', 'manager')->first();
        $client = Client::first();

        $opportunity = Opportunity::create([
            'client_id' => $client->id,
            'title_ar' => 'صفقة ترخيص نظام نقاط البيع للمخابز',
            'stage' => 'negotiation',
            'estimated_value' => 1200.00,
            'owner_id' => $manager->id,
        ]);

        $response = $this->actingAs($manager)->postJson("/api/v1/opportunities/{$opportunity->id}/convert-to-project", [
            'code' => 'POS-BAKERY',
            'name_ar' => 'مشروع نشر نقاط البيع لمخابز الغيل',
            'due_date' => now()->addDays(20)->format('Y-m-d'),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('project.code', 'POS-BAKERY')
            ->assertJsonPath('project.type', 'client_delivery');

        $this->assertEquals('won', $opportunity->fresh()->stage);
        $this->assertNotNull($opportunity->fresh()->won_delivery_project_id);
    }

    public function test_guest_cannot_access_crm(): void
    {
        $guest = User::where('role', 'guest')->first();

        $response = $this->actingAs($guest)->getJson('/api/v1/clients');
        $response->assertStatus(403);

        $oppResponse = $this->actingAs($guest)->getJson('/api/v1/opportunities');
        $oppResponse->assertStatus(403);
    }

    public function test_dashboard_returns_authorized_metrics_and_overdue_tasks(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'metrics' => [
                    'active_projects',
                    'pending_tasks',
                    'overdue_tasks',
                    'today_tasks',
                    'pipeline_value',
                ],
                'my_day_tasks',
                'overdue_tasks',
                'active_projects',
                'recent_activity',
            ]);
    }

    public function test_search_filters_by_authorized_resources(): void
    {
        $user = User::where('role', 'member')->first();

        $response = $this->actingAs($user)->getJson('/api/v1/search?q=حضرموت');

        $response->assertStatus(200)
            ->assertJsonStructure(['results']);

        $this->assertNotEmpty($response->json('results'));
    }
}
