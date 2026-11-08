<?php

namespace App\Modules\Projects\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Project;
use App\Modules\Projects\Requests\ProjectStoreRequest;
use App\Modules\Projects\Resources\ProjectResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Project::class);

        $query = Project::query()
            ->with(['lead', 'product', 'client'])
            ->withCount('tasks');

        // Non-admin/manager filter
        if ($request->user()->isGuest()) {
            $query->whereHas('members', fn ($q) => $q->where('user_id', $request->user()->id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } elseif (! $request->boolean('include_archived')) {
            $query->where('status', '!=', 'archived');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('lead_id')) {
            $query->where('lead_id', $request->input('lead_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $projects = $query->orderBy('priority', 'desc')->orderBy('created_at', 'desc')->paginate($request->input('per_page', 20));

        return response()->json([
            'data' => ProjectResource::collection($projects->items()),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
            ],
        ]);
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load(['lead', 'product', 'client', 'members', 'milestones']);
        $project->loadCount('tasks');

        return response()->json([
            'project' => new ProjectResource($project),
        ]);
    }

    public function store(ProjectStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $memberIds = $validated['member_ids'] ?? [];
        unset($validated['member_ids']);

        $project = DB::transaction(function () use ($validated, $memberIds) {
            $proj = Project::create($validated);

            if (! empty($memberIds)) {
                $proj->members()->sync($memberIds);
            } elseif ($proj->lead_id) {
                $proj->members()->attach($proj->lead_id, ['role' => 'lead']);
            }

            AuditLog::log('project.created', $proj, [
                'code' => $proj->code,
                'name_ar' => $proj->name_ar,
                'type' => $proj->type,
            ]);

            return $proj;
        });

        $project->load(['lead', 'product', 'client', 'members', 'milestones']);

        return response()->json([
            'message' => 'تم إنشاء المشروع بنجاح / Project created successfully',
            'project' => new ProjectResource($project),
        ], 201);
    }

    public function update(ProjectStoreRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validated();
        $memberIds = $validated['member_ids'] ?? null;
        unset($validated['member_ids']);

        DB::transaction(function () use ($project, $validated, $memberIds) {
            $project->update($validated);

            if ($memberIds !== null) {
                $project->members()->sync($memberIds);
            }

            $project->recalculateProgress();

            AuditLog::log('project.updated', $project, [
                'updated_fields' => array_keys($validated),
            ]);
        });

        $project->load(['lead', 'product', 'client', 'members', 'milestones']);

        return response()->json([
            'message' => 'تم تحديث بيانات المشروع / Project updated successfully',
            'project' => new ProjectResource($project),
        ]);
    }

    public function archive(Request $request, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $isArchived = $project->status === 'archived';
        $project->status = $isArchived ? 'active' : 'archived';
        $project->archived_at = $isArchived ? null : now();
        $project->save();

        AuditLog::log('project.archive_toggle', $project, [
            'status' => $project->status,
        ]);

        return response()->json([
            'message' => $isArchived ? 'تم استعادة المشروع' : 'تم أرشفة المشروع',
            'project' => new ProjectResource($project),
        ]);
    }

    public function addMilestone(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
        ]);

        $milestone = $project->milestones()->create($validated);

        return response()->json([
            'message' => 'تم إضافة مرحلة جديدة للمشروع',
            'milestone' => $milestone,
        ], 201);
    }
}
