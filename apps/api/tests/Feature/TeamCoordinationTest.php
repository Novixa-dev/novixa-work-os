<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamCoordinationTest extends TestCase
{
    use RefreshDatabase;

    private User $ahmed;

    private User $omar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->ahmed = User::where('email', 'ahmed@novixa.dev')->firstOrFail();
        $this->omar = User::where('email', 'omar@novixa.dev')->firstOrFail();
    }

    private function openTask(array $attrs = []): Task
    {
        return Task::create($attrs + [
            'code' => 'T-'.random_int(1000, 9999),
            'title_ar' => 'مهمة اختبار',
            'status' => 'todo',
            'priority' => 'medium',
            'reporter_id' => $this->ahmed->id,
        ]);
    }

    public function test_claiming_an_unassigned_task_assigns_it_and_starts_work(): void
    {
        $task = $this->openTask();

        $this->actingAs($this->omar)->postJson("/api/v1/tasks/{$task->id}/claim")
            ->assertOk()
            ->assertJsonPath('task.assignee_id', $this->omar->id)
            ->assertJsonPath('task.status', 'in_progress')
            ->assertJsonPath('task.is_taken', true);

        $this->assertNotNull($task->fresh()->claimed_at);
        $this->assertDatabaseHas('notifications', ['user_id' => $this->ahmed->id, 'type' => 'task.claimed']);
    }

    public function test_second_user_cannot_take_a_task_already_taken(): void
    {
        $task = $this->openTask();
        $this->actingAs($this->omar)->postJson("/api/v1/tasks/{$task->id}/claim")->assertOk();

        $this->actingAs($this->ahmed)->postJson("/api/v1/tasks/{$task->id}/claim")
            ->assertStatus(409)
            ->assertJsonPath('assignee.id', $this->omar->id);

        $this->assertSame($this->omar->id, $task->fresh()->assignee_id);
    }

    public function test_only_owner_or_manager_can_release_and_it_returns_to_the_pool(): void
    {
        $task = $this->openTask(['assignee_id' => $this->omar->id, 'status' => 'in_progress']);
        $member = User::where('role', 'member')->where('id', '!=', $this->omar->id)->first();

        if ($member) {
            $this->actingAs($member)->postJson("/api/v1/tasks/{$task->id}/release")->assertForbidden();
        }

        $this->actingAs($this->omar)->postJson("/api/v1/tasks/{$task->id}/release")
            ->assertOk()->assertJsonPath('task.assignee_id', null)->assertJsonPath('task.status', 'todo');
    }

    public function test_closed_task_cannot_be_claimed(): void
    {
        $task = $this->openTask(['status' => 'done']);
        $this->actingAs($this->omar)->postJson("/api/v1/tasks/{$task->id}/claim")->assertStatus(422);
    }

    public function test_unassigned_filter_lists_only_open_unassigned_tasks(): void
    {
        $free = $this->openTask(['title_ar' => 'حرة']);
        $this->openTask(['assignee_id' => $this->omar->id]);
        $this->openTask(['status' => 'done']);

        $ids = collect($this->actingAs($this->ahmed)->getJson('/api/v1/tasks?unassigned=1&per_page=100')->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($free->id));
        $this->assertTrue(Task::whereIn('id', $ids)->whereNotNull('assignee_id')->doesntExist());
    }

    public function test_deadline_reminders_are_sent_once_and_cover_overdue_and_due_soon(): void
    {
        Task::query()->delete();
        Notification::query()->delete();
        $soon = $this->openTask(['assignee_id' => $this->omar->id, 'due_date' => now()->addDay()]);
        $late = $this->openTask(['assignee_id' => $this->omar->id, 'due_date' => now()->subDays(3)]);
        $this->openTask(['assignee_id' => $this->omar->id, 'due_date' => now()->addDays(30)]);
        $this->openTask(['assignee_id' => $this->omar->id, 'due_date' => now()->subDay(), 'status' => 'done']);

        $this->artisan('workos:deadline-reminders')->assertSuccessful();
        $this->artisan('workos:deadline-reminders')->assertSuccessful(); // idempotent

        $types = Notification::where('user_id', $this->omar->id)->pluck('type')->all();
        sort($types);
        $this->assertSame(['task.due_soon', 'task.overdue'], $types);
        $this->assertNotNull($soon->id.$late->id);
    }

    public function test_project_links_support_https_only_and_are_returned_with_the_project(): void
    {
        $project = Project::firstOrFail();

        $this->actingAs($this->omar)->postJson("/api/v1/projects/{$project->id}/links", [
            'title' => 'Figma', 'url' => 'javascript:alert(1)',
        ])->assertStatus(422);

        $this->actingAs($this->omar)->postJson("/api/v1/projects/{$project->id}/links", [
            'title' => 'Repo', 'url' => 'https://github.com/Novixa-dev',
        ])->assertCreated();

        $this->actingAs($this->omar)->postJson("/api/v1/projects/{$project->id}/links", [
            'title' => 'ملاحظة', 'note' => 'اجتماع الإطلاق الخميس',
        ])->assertCreated();

        $this->actingAs($this->omar)->getJson("/api/v1/projects/{$project->id}")
            ->assertOk()->assertJsonCount(2, 'project.links');
    }
}
