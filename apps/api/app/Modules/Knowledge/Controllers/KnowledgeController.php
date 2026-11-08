<?php

namespace App\Modules\Knowledge\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\KnowledgePage;
use App\Modules\Identity\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KnowledgeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = KnowledgePage::with('author');

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title_ar', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%")
                    ->orWhere('content_ar', 'like', "%{$search}%");
            });
        }

        $pages = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $pages->map(fn ($p) => [
                'id' => $p->id,
                'title_ar' => $p->title_ar,
                'title_en' => $p->title_en,
                'slug' => $p->slug,
                'category' => $p->category,
                'content_ar' => $p->content_ar,
                'content_en' => $p->content_en,
                'author' => new UserResource($p->author),
                'created_at' => $p->created_at->toIso8601String(),
            ]),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $page = KnowledgePage::with(['author', 'project', 'product'])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'page' => [
                'id' => $page->id,
                'title_ar' => $page->title_ar,
                'title_en' => $page->title_en,
                'slug' => $page->slug,
                'category' => $page->category,
                'content_ar' => $page->content_ar,
                'content_en' => $page->content_en,
                'author' => new UserResource($page->author),
                'project' => $page->project ? ['id' => $page->project->id, 'name_ar' => $page->project->name_ar] : null,
                'product' => $page->product ? ['id' => $page->product->id, 'name_ar' => $page->product->name_ar] : null,
                'created_at' => $page->created_at->toIso8601String(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->user()->isGuest()) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $validated = $request->validate([
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:100', 'unique:knowledge_pages,slug'],
            'category' => ['required', Rule::in(['sop', 'architecture', 'playbook', 'onboarding'])],
            'content_ar' => ['required', 'string'],
            'content_en' => ['nullable', 'string'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        $validated['author_id'] = $request->user()->id;

        $page = KnowledgePage::create($validated);

        AuditLog::log('knowledge.created', $page, [
            'slug' => $page->slug,
            'title_ar' => $page->title_ar,
        ]);

        return response()->json([
            'message' => 'تم إنشاء صفحة المعرفة بنجاح',
            'page' => $page,
        ], 201);
    }
}
