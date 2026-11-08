<?php

namespace App\Modules\Work\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Task;
use App\Modules\Work\Services\TaskDependencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskDependencyController extends Controller
{
    public function __construct(
        protected TaskDependencyService $dependencyService
    ) {}

    public function store(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'depends_on_task_id' => ['required', 'exists:tasks,id'],
            'type' => ['nullable', 'in:blocks,relates_to'],
        ]);

        $depTaskId = (int) $validated['depends_on_task_id'];

        // Prevent self dependency
        if ($task->id === $depTaskId) {
            return response()->json([
                'message' => 'لا يمكن للمهمة أن تعتمد على نفسها / Task cannot depend on itself.',
            ], 422);
        }

        // Cycle detection check
        if ($this->dependencyService->wouldCreateCycle($task->id, $depTaskId)) {
            return response()->json([
                'message' => 'تعذر إضافة الاعتمادية: سيؤدي ذلك إلى تشكيل حلقة اعتماد دائرية مغلقة (Cycle detected).',
            ], 422);
        }

        $task->dependencies()->syncWithoutDetaching([
            $depTaskId => ['type' => $validated['type'] ?? 'blocks'],
        ]);

        AuditLog::log('task.dependency_added', $task, [
            'depends_on_task_id' => $depTaskId,
        ]);

        return response()->json([
            'message' => 'تم إضافة الاعتمادية بنجاح',
            'dependencies' => $task->dependencies()->get(),
        ], 201);
    }

    public function destroy(Task $task, int $dependsOnTaskId): JsonResponse
    {
        $this->authorize('update', $task);

        $task->dependencies()->detach($dependsOnTaskId);

        AuditLog::log('task.dependency_removed', $task, [
            'detached_task_id' => $dependsOnTaskId,
        ]);

        return response()->json([
            'message' => 'تم إزالة الاعتمادية بنجاح',
        ]);
    }
}
