<?php

namespace App\Modules\Projects\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectReportController extends Controller
{
    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        abort_if($request->user()->isGuest(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:progress_report,suggestion,risk,review'],
            'content' => ['required', 'string'],
            'status' => ['nullable', 'string', 'in:open,reviewed,approved,resolved'],
        ]);

        $report = $project->reports()->create($validated + [
            'user_id' => $request->user()->id,
            'type' => $validated['type'] ?? 'progress_report',
            'status' => $validated['status'] ?? 'open',
        ]);

        AuditLog::log('project.report_added', $project, ['report_id' => $report->id, 'type' => $report->type]);

        $report->load('author');

        return response()->json([
            'message' => 'تم حفظ التقرير/المقترح بنجاح / Report saved successfully',
            'report' => $report,
        ], 201);
    }

    public function updateStatus(Request $request, Project $project, ProjectReport $report): JsonResponse
    {
        abort_unless($report->project_id === $project->id, 404);
        $user = $request->user();
        abort_unless($user->isAdmin() || $user->isManager() || $report->user_id === $user->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,reviewed,approved,resolved'],
        ]);

        $report->update($validated);

        return response()->json([
            'message' => 'تم تحديث حالة التقرير / Report status updated',
            'report' => $report,
        ]);
    }

    public function destroy(Request $request, Project $project, ProjectReport $report): JsonResponse
    {
        abort_unless($report->project_id === $project->id, 404);
        $user = $request->user();
        abort_unless($user->isAdmin() || $user->isManager() || $report->user_id === $user->id, 403);

        $report->delete();

        return response()->json(['message' => 'تم الحذف / Deleted successfully']);
    }
}
