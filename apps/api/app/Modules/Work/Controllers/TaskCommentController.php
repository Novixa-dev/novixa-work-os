<?php

namespace App\Modules\Work\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Task;
use App\Models\TaskComment;
use App\Modules\Identity\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:5000'],
        ]);

        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        // Auto-add commenter to watchers
        $task->watchers()->syncWithoutDetaching([$request->user()->id]);

        // Notify assignee if not the commenter
        if ($task->assignee_id && $task->assignee_id !== $request->user()->id) {
            Notification::create([
                'user_id' => $task->assignee_id,
                'type' => 'comment.added',
                'title_ar' => "تعليق جديد على المهمة {$task->code}",
                'title_en' => "New comment on {$task->code}",
                'body_ar' => "أضاف {$request->user()->name}: " . mb_substr($comment->comment, 0, 80) . '...',
                'body_en' => "{$request->user()->name} commented on {$task->code}",
                'link' => "/tasks?code={$task->code}",
            ]);
        }

        AuditLog::log('task.comment_added', $task, [
            'comment_id' => $comment->id,
        ]);

        return response()->json([
            'message' => 'تم إضافة التعليق بنجاح',
            'comment' => [
                'id' => $comment->id,
                'user' => new UserResource($request->user()),
                'comment' => $comment->comment,
                'created_at' => $comment->created_at->toIso8601String(),
            ],
        ], 201);
    }

    public function destroy(Task $task, TaskComment $comment): JsonResponse
    {
        if ($comment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'تم حذف التعليق']);
    }
}
