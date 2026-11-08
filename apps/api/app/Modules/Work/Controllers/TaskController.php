<?php

namespace App\Modules\Work\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Task;
use App\Modules\Work\Requests\TaskStoreRequest;
use App\Modules\Work\Requests\TaskUpdateRequest;
use App\Modules\Work\Resources\TaskDetailResource;
use App\Modules\Work\Resources\TaskResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Task::class);

        $query = Task::query()
            ->with(['project', 'product', 'client', 'assignee', 'reporter', 'labels'])
            ->withCount(['subtasks', 'comments', 'attachments']);

        // Default: root tasks unless subtasks requested explicitly
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->input('parent_id'));
        } elseif (! $request->boolean('include_subtasks')) {
            $query->whereNull('parent_id');
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('assignee_id')) {
            $query->where('assignee_id', $request->input('assignee_id'));
        }

        if ($request->boolean('is_my_day')) {
            $query->where('is_my_day', true);
        }

        if ($request->boolean('overdue')) {
            $query->where('due_date', '<', now()->startOfDay())
                ->where('status', '!=', 'done');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title_ar', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($request->boolean('kanban')) {
            $tasks = $query->orderBy('kanban_order', 'asc')->get();

            return response()->json([
                'data' => TaskResource::collection($tasks),
            ]);
        }

        $tasks = $query->orderBy($sortField, $sortOrder)->paginate($request->input('per_page', 25));

        return response()->json([
            'data' => TaskResource::collection($tasks->items()),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ],
        ]);
    }

    public function myWork(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $myDayTasks = Task::with(['project', 'labels', 'assignee'])
            ->where('assignee_id', $userId)
            ->where('is_my_day', true)
            ->where('status', '!=', 'done')
            ->orderBy('priority', 'desc')
            ->get();

        $myTasks = Task::with(['project', 'labels', 'assignee'])
            ->where('assignee_id', $userId)
            ->where('status', '!=', 'done')
            ->orderBy('due_date', 'asc')
            ->get();

        $overdueTasks = Task::with(['project', 'labels', 'assignee'])
            ->where('assignee_id', $userId)
            ->where('due_date', '<', now()->startOfDay())
            ->where('status', '!=', 'done')
            ->orderBy('due_date', 'asc')
            ->get();

        $assignedByMe = Task::with(['project', 'labels', 'assignee'])
            ->where('reporter_id', $userId)
            ->where('assignee_id', '!=', $userId)
            ->where('status', '!=', 'done')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'my_day' => TaskResource::collection($myDayTasks),
            'my_tasks' => TaskResource::collection($myTasks),
            'overdue' => TaskResource::collection($overdueTasks),
            'assigned_by_me' => TaskResource::collection($assignedByMe),
            'counts' => [
                'my_day' => $myDayTasks->count(),
                'my_tasks' => $myTasks->count(),
                'overdue' => $overdueTasks->count(),
                'assigned_by_me' => $assignedByMe->count(),
            ],
        ]);
    }

    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task->load([
            'project', 'product', 'client', 'assignee', 'reporter', 'labels',
            'subtasks.assignee', 'dependencies', 'dependents',
            'comments.user', 'attachments.user', 'watchers',
        ]);

        return response()->json([
            'task' => new TaskDetailResource($task),
        ]);
    }

    public function store(TaskStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $labelIds = $validated['label_ids'] ?? [];
        unset($validated['label_ids']);

        $task = DB::transaction(function () use ($validated, $labelIds, $request) {
            // Generate next incremental code: NW-XXX
            $maxId = Task::max('id') ?? 0;
            $code = 'NW-'.str_pad((string) ($maxId + 1), 3, '0', STR_PAD_LEFT);

            $validated['code'] = $code;
            $validated['reporter_id'] = $request->user()->id;

            $task = Task::create($validated);

            if (! empty($labelIds)) {
                $task->labels()->sync($labelIds);
            }

            // If assigned to someone else, trigger notification
            if ($task->assignee_id && $task->assignee_id !== $request->user()->id) {
                Notification::create([
                    'user_id' => $task->assignee_id,
                    'type' => 'task.assigned',
                    'title_ar' => "تم تعيين مهمة جديدة لك: {$task->title_ar}",
                    'title_en' => "Task assigned to you: {$task->code}",
                    'body_ar' => "قام {$request->user()->name} بتعيينك للمهمة {$task->code}",
                    'body_en' => "{$request->user()->name} assigned you to task {$task->code}",
                    'link' => "/tasks?code={$task->code}",
                ]);
            }

            // Recalculate project progress if linked
            if ($task->project_id) {
                $task->project->recalculateProgress();
            }

            AuditLog::log('task.created', $task, [
                'code' => $task->code,
                'title_ar' => $task->title_ar,
                'assignee_id' => $task->assignee_id,
            ]);

            return $task;
        });

        $task->load(['project', 'product', 'client', 'assignee', 'reporter', 'labels']);

        return response()->json([
            'message' => 'تم إنشاء المهمة بنجاح / Task created successfully',
            'task' => new TaskResource($task),
        ], 201);
    }

    public function update(TaskUpdateRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validated();
        $labelIds = $validated['label_ids'] ?? null;
        unset($validated['label_ids']);

        DB::transaction(function () use ($task, $validated, $labelIds, $request) {
            $prevAssigneeId = $task->assignee_id;
            $prevStatus = $task->status;

            if (isset($validated['status']) && $validated['status'] === 'done' && $prevStatus !== 'done') {
                $validated['completed_at'] = now();
            } elseif (isset($validated['status']) && $validated['status'] !== 'done') {
                $validated['completed_at'] = null;
            }

            $task->update($validated);

            if ($labelIds !== null) {
                $task->labels()->sync($labelIds);
            }

            // Notify new assignee if changed
            if ($task->assignee_id && $task->assignee_id !== $prevAssigneeId && $task->assignee_id !== $request->user()->id) {
                Notification::create([
                    'user_id' => $task->assignee_id,
                    'type' => 'task.assigned',
                    'title_ar' => "تم تعيين مهمة لك: {$task->title_ar}",
                    'title_en' => "Task assigned to you: {$task->code}",
                    'body_ar' => "قام {$request->user()->name} بتعيينك للمهمة {$task->code}",
                    'body_en' => "{$request->user()->name} assigned you to task {$task->code}",
                    'link' => "/tasks?code={$task->code}",
                ]);
            }

            if ($task->project_id) {
                $task->project->recalculateProgress();
            }

            AuditLog::log('task.updated', $task, [
                'updated_fields' => array_keys($validated),
            ]);
        });

        $task->load(['project', 'product', 'client', 'assignee', 'reporter', 'labels']);

        return response()->json([
            'message' => 'تم تحديث المهمة بنجاح / Task updated successfully',
            'task' => new TaskResource($task),
        ]);
    }

    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'status' => ['required', 'in:backlog,todo,in_progress,in_review,done,cancelled'],
        ]);

        $task->status = $validated['status'];
        if ($task->status === 'done') {
            $task->completed_at = now();
        } else {
            $task->completed_at = null;
        }
        $task->save();

        if ($task->project_id) {
            $task->project->recalculateProgress();
        }

        AuditLog::log('task.status_changed', $task, [
            'status' => $task->status,
        ]);

        return response()->json([
            'message' => 'تم تحديث حالة المهمة بنجاح',
            'task' => new TaskResource($task),
        ]);
    }

    public function toggleMyDay(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task->is_my_day = ! $task->is_my_day;
        $task->save();

        return response()->json([
            'message' => $task->is_my_day ? 'تمت الإضافة إلى يومي' : 'تمت الإزالة من يومي',
            'task' => new TaskResource($task),
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        if ($task->project_id) {
            $task->project->recalculateProgress();
        }

        AuditLog::log('task.deleted', $task, [
            'code' => $task->code,
        ]);

        return response()->json([
            'message' => 'تم حذف المهمة بنجاح / Task deleted successfully',
        ]);
    }
}
