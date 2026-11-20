<?php

namespace App\Modules\Projects\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectNoteController extends Controller
{
    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        abort_if($request->user()->isGuest(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:note,credential,plan,dashboard,custom'],
            'content' => ['nullable', 'string'],
            'url' => ['nullable', 'url:http,https', 'max:2048'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:500'],
            'environment' => ['nullable', 'string', 'in:production,staging,development,internal'],
            'color' => ['nullable', 'string', 'max:50'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $note = $project->notes()->create($validated + ['user_id' => $request->user()->id]);

        AuditLog::log('project.note_added', $project, ['note_id' => $note->id, 'type' => $note->type]);

        $note->load('author');

        return response()->json([
            'message' => 'تم حفظ البطاقة بنجاح / Info card saved successfully',
            'note' => $note,
        ], 201);
    }

    public function togglePin(Request $request, Project $project, ProjectNote $note): JsonResponse
    {
        abort_unless($note->project_id === $project->id, 404);
        $user = $request->user();
        abort_unless($user->isAdmin() || $user->isManager() || $note->user_id === $user->id, 403);

        $note->update(['is_pinned' => ! $note->is_pinned]);

        return response()->json([
            'message' => 'تم تحديث التثبيت / Pin status updated',
            'note' => $note,
        ]);
    }

    public function destroy(Request $request, Project $project, ProjectNote $note): JsonResponse
    {
        abort_unless($note->project_id === $project->id, 404);
        $user = $request->user();
        abort_unless($user->isAdmin() || $user->isManager() || $note->user_id === $user->id, 403);

        $note->delete();

        return response()->json(['message' => 'تم الحذف / Deleted successfully']);
    }
}
