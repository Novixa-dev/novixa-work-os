<?php

namespace App\Modules\Work\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskAttachmentController extends Controller
{
    public function upload(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:25600', // 25 MB max
                'mimes:pdf,docx,xlsx,png,jpg,jpeg,webp,zip',
            ],
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $sizeBytes = $file->getSize();

        // Store with secure random UUID filename in private storage
        $storedName = Str::uuid()->toString().'.'.$file->extension();
        $path = $file->storeAs('private/attachments', $storedName);

        $attachment = $task->attachments()->create([
            'project_id' => $task->project_id,
            'user_id' => $request->user()->id,
            'filename' => $originalName,
            'disk_path' => $path,
            'mime_type' => $mimeType,
            'size_bytes' => $sizeBytes,
        ]);

        AuditLog::log('task.attachment_uploaded', $task, [
            'attachment_id' => $attachment->id,
            'filename' => $originalName,
            'size_bytes' => $sizeBytes,
        ]);

        return response()->json([
            'message' => 'تم رفع الملف بنجاح / File uploaded successfully',
            'attachment' => [
                'id' => $attachment->id,
                'filename' => $attachment->filename,
                'mime_type' => $attachment->mime_type,
                'size_bytes' => $attachment->size_bytes,
                'created_at' => $attachment->created_at->toIso8601String(),
            ],
        ], 201);
    }

    public function download(TaskAttachment $attachment): StreamedResponse|JsonResponse
    {
        // Enforce access control: verify user has access to task or project
        if ($attachment->task) {
            $this->authorize('view', $attachment->task);
        } elseif ($attachment->project) {
            $this->authorize('view', $attachment->project);
        } else {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        if (! Storage::exists($attachment->disk_path)) {
            return response()->json([
                'message' => 'الملف غير موجود على السيرفر / File not found on disk',
            ], 404);
        }

        AuditLog::log('task.attachment_downloaded', $attachment->task, [
            'attachment_id' => $attachment->id,
            'filename' => $attachment->filename,
        ]);

        return Storage::download($attachment->disk_path, $attachment->filename, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
