<?php

namespace App\Modules\Shared\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ClientInteraction;
use App\Models\Opportunity;
use App\Models\Project;
use App\Models\Task;
use App\Modules\Identity\Resources\UserResource;
use App\Modules\Projects\Resources\ProjectResource;
use App\Modules\Work\Resources\TaskResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Metrics
        $pendingTasksQuery = Task::where('status', '!=', 'done');
        $overdueTasksQuery = Task::where('status', '!=', 'done')->where('due_date', '<', now()->startOfDay());

        if (!$user->isAdmin() && !$user->isManager()) {
            $pendingTasksQuery->where('assignee_id', $user->id);
            $overdueTasksQuery->where('assignee_id', $user->id);
        }

        $metrics = [
            'active_projects' => Project::where('status', 'active')->count(),
            'pending_tasks' => $pendingTasksQuery->count(),
            'overdue_tasks' => $overdueTasksQuery->count(),
            'today_tasks' => Task::where('assignee_id', $user->id)->where('is_my_day', true)->where('status', '!=', 'done')->count(),
            'pipeline_value' => $user->isGuest() ? 0 : (float) Opportunity::whereNotIn('stage', ['lost'])->sum('estimated_value'),
        ];

        // 2. Tasks Due Today / My Day
        $myDayTasks = Task::with(['project', 'labels', 'assignee'])
            ->where('assignee_id', $user->id)
            ->where('is_my_day', true)
            ->where('status', '!=', 'done')
            ->take(10)
            ->get();

        // 3. Overdue Tasks
        $overdueTasks = Task::with(['project', 'labels', 'assignee'])
            ->where('due_date', '<', now()->startOfDay())
            ->where('status', '!=', 'done')
            ->orderBy('due_date', 'asc')
            ->take(10)
            ->get();

        // 4. Active Projects with progress
        $activeProjects = Project::with(['lead', 'client', 'product'])
            ->where('status', 'active')
            ->orderBy('priority', 'desc')
            ->take(6)
            ->get();

        // 5. CRM Follow-ups (hidden for guests)
        $followUps = [];
        if (!$user->isGuest()) {
            $followUps = ClientInteraction::with(['client', 'contact', 'user'])
                ->whereNotNull('next_follow_up_date')
                ->where('next_follow_up_date', '>=', now()->subDays(1)->startOfDay())
                ->orderBy('next_follow_up_date', 'asc')
                ->take(5)
                ->get()
                ->map(fn ($f) => [
                    'id' => $f->id,
                    'client_id' => $f->client_id,
                    'client_name' => $f->client->name_ar,
                    'channel' => $f->channel,
                    'date' => $f->next_follow_up_date->format('Y-m-d'),
                    'summary' => $f->summary,
                    'user' => new UserResource($f->user),
                ]);
        }

        // 6. Recent Activity Feed
        $recentActivity = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'user' => $log->user ? new UserResource($log->user) : null,
                'metadata' => $log->metadata,
                'created_at' => $log->created_at->toIso8601String(),
            ]);

        return response()->json([
            'metrics' => $metrics,
            'my_day_tasks' => TaskResource::collection($myDayTasks),
            'overdue_tasks' => TaskResource::collection($overdueTasks),
            'active_projects' => ProjectResource::collection($activeProjects),
            'follow_ups' => $followUps,
            'recent_activity' => $recentActivity,
        ]);
    }
}
