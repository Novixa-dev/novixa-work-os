<?php

namespace App\Modules\Products\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Modules\Products\Resources\ProductResource;
use App\Modules\Projects\Resources\ProjectResource;
use App\Modules\Work\Resources\TaskResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::with('owner')->withCount(['projects', 'tasks']);

        if ($request->filled('status')) {
            $query->where('lifecycle_status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name_ar')->get();

        return response()->json([
            'data' => ProductResource::collection($products),
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        $product->load('owner')->loadCount(['projects', 'tasks']);

        return response()->json([
            'product' => new ProductResource($product),
        ]);
    }

    public function work(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        $projects = $product->projects()->with('lead')->get();
        $tasks = $product->tasks()->with(['assignee', 'labels'])->take(50)->get();

        return response()->json([
            'projects' => ProjectResource::collection($projects),
            'tasks' => TaskResource::collection($tasks),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:100', 'unique:products,slug'],
            'tagline_ar' => ['nullable', 'string', 'max:255'],
            'tagline_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'lifecycle_status' => ['required', Rule::in(['ideation', 'in_development', 'beta', 'active', 'sunset'])],
            'owner_id' => ['nullable', 'exists:users,id'],
            'repository_url' => ['nullable', 'url'],
            'website_url' => ['nullable', 'url'],
            'demo_url' => ['nullable', 'url'],
            'documentation_url' => ['nullable', 'url'],
        ]);

        $product = Product::create($validated);

        AuditLog::log('product.created', $product, [
            'slug' => $product->slug,
            'name_ar' => $product->name_ar,
        ]);

        return response()->json([
            'message' => 'تم إضافة المنتج بنجاح',
            'product' => new ProductResource($product),
        ], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('products')->ignore($product->id)],
            'tagline_ar' => ['nullable', 'string', 'max:255'],
            'tagline_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'lifecycle_status' => ['sometimes', 'required', Rule::in(['ideation', 'in_development', 'beta', 'active', 'sunset'])],
            'owner_id' => ['nullable', 'exists:users,id'],
            'repository_url' => ['nullable', 'url'],
            'website_url' => ['nullable', 'url'],
            'demo_url' => ['nullable', 'url'],
            'documentation_url' => ['nullable', 'url'],
        ]);

        $product->update($validated);

        AuditLog::log('product.updated', $product, [
            'updated_fields' => array_keys($validated),
        ]);

        return response()->json([
            'message' => 'تم تحديث بيانات المنتج بنجاح',
            'product' => new ProductResource($product),
        ]);
    }
}
