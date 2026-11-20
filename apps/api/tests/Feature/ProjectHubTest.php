<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectNote;
use App\Models\ProjectReport;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectHubTest extends TestCase
{
    use RefreshDatabase;

    private User $ahmed;

    private User $omar;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->ahmed = User::where('email', 'ahmed@novixa.dev')->firstOrFail();
        $this->omar = User::where('email', 'omar@novixa.dev')->firstOrFail();
        $this->project = Project::firstOrFail();
    }

    public function test_can_create_project_info_card_and_credential_note(): void
    {
        $payload = [
            'title' => 'لوحة تحكم وبيانات الخادم (Server & Console)',
            'type' => 'credential',
            'url' => 'https://console.novixa.dev',
            'username' => 'admin@novixa.dev',
            'password' => 'SuperSecretPass2026!',
            'environment' => 'production',
            'content' => 'بيانات الدخول للوحة التحكم السحابية',
            'is_pinned' => true,
        ];

        $response = $this->actingAs($this->ahmed)
            ->postJson("/api/v1/projects/{$this->project->id}/notes", $payload)
            ->assertStatus(201)
            ->assertJsonPath('note.title', $payload['title'])
            ->assertJsonPath('note.type', 'credential')
            ->assertJsonPath('note.username', $payload['username']);

        $noteId = $response->json('note.id');

        $this->assertDatabaseHas('project_notes', [
            'id' => $noteId,
            'project_id' => $this->project->id,
            'is_pinned' => 1,
        ]);

        // Toggle Pin
        $this->actingAs($this->ahmed)
            ->patchJson("/api/v1/projects/{$this->project->id}/notes/{$noteId}/pin")
            ->assertOk()
            ->assertJsonPath('note.is_pinned', false);

        // Delete note
        $this->actingAs($this->ahmed)
            ->deleteJson("/api/v1/projects/{$this->project->id}/notes/{$noteId}")
            ->assertOk();

        $this->assertDatabaseMissing('project_notes', ['id' => $noteId]);
    }

    public function test_can_create_project_report_and_suggestion(): void
    {
        $payload = [
            'title' => 'تقرير تقدم سبرنت التسليم الأسبوعي',
            'type' => 'progress_report',
            'content' => 'تم إنهاء واجهات لوحة التحكم والتحقق من صلاحيات الربط بنجاح.',
            'status' => 'open',
        ];

        $response = $this->actingAs($this->omar)
            ->postJson("/api/v1/projects/{$this->project->id}/reports", $payload)
            ->assertStatus(201)
            ->assertJsonPath('report.title', $payload['title'])
            ->assertJsonPath('report.type', 'progress_report');

        $reportId = $response->json('report.id');

        // Update status
        $this->actingAs($this->ahmed)
            ->patchJson("/api/v1/projects/{$this->project->id}/reports/{$reportId}/status", ['status' => 'approved'])
            ->assertOk()
            ->assertJsonPath('report.status', 'approved');

        // Delete report
        $this->actingAs($this->ahmed)
            ->deleteJson("/api/v1/projects/{$this->project->id}/reports/{$reportId}")
            ->assertOk();

        $this->assertDatabaseMissing('project_reports', ['id' => $reportId]);
    }

    public function test_project_show_includes_notes_and_reports(): void
    {
        ProjectNote::create([
            'project_id' => $this->project->id,
            'user_id' => $this->ahmed->id,
            'title' => 'ملاحظة تخطيطية',
            'type' => 'plan',
            'content' => 'خطة إطلاق النسخة التجريبية',
        ]);

        ProjectReport::create([
            'project_id' => $this->project->id,
            'user_id' => $this->omar->id,
            'title' => 'مقترح تحسين الاستجابة',
            'type' => 'suggestion',
            'content' => 'نقترح استخدام التخزين المؤقت للاستعلامات المتكررة',
        ]);

        $this->actingAs($this->ahmed)
            ->getJson("/api/v1/projects/{$this->project->id}")
            ->assertOk()
            ->assertJsonStructure([
                'project' => [
                    'id',
                    'code',
                    'notes',
                    'reports',
                    'links',
                    'milestones',
                ],
            ]);
    }
}
