<?php

namespace App\Modules\Projects\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectLinkController extends Controller
{
    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        abort_if($request->user()->isGuest(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            // Only http(s) links: blocks javascript:/data: URLs from being rendered as hrefs.
            'url' => ['nullable', 'url:http,https', 'max:2048', 'required_without:note'],
            'note' => ['nullable', 'string', 'max:5000', 'required_without:url'],
        ]);

        $link = $project->links()->create($validated + ['user_id' => $request->user()->id]);
        AuditLog::log('project.link_added', $project, ['link_id' => $link->id]);

        return response()->json([
            'message' => 'تمت الإضافة / Added',
            'link' => $link,
        ], 201);
    }

    public function destroy(Request $request, Project $project, ProjectLink $link): JsonResponse
    {
        abort_unless($link->project_id === $project->id, 404);
        $user = $request->user();
        abort_unless($user->isAdmin() || $user->isManager() || $link->user_id === $user->id, 403);

        $link->delete();

        return response()->json(['message' => 'تم الحذف / Deleted']);
    }
}
