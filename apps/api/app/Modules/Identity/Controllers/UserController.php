<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Modules\Identity\Requests\UserCreateRequest;
use App\Modules\Identity\Requests\UserUpdateRequest;
use App\Modules\Identity\Resources\UserResource;
use App\Support\Like;
use App\Support\Pagination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', Like::contains($search))
                    ->orWhere('email', 'like', Like::contains($search))
                    ->orWhere('job_title', 'like', Like::contains($search));
            });
        }

        if ($request->has('active')) {
            $query->where('is_active', filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN));
        }

        $users = $query->orderBy('name')->paginate(Pagination::perPage($request, 25));

        return response()->json([
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    public function store(UserCreateRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        AuditLog::log('user.created', $user, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return response()->json([
            'message' => 'تم إنشاء المستخدم بنجاح / User created successfully',
            'user' => new UserResource($user),
        ], 201);
    }

    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validated();

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Only admin/owner can change role
        if (isset($validated['role']) && ! $request->user()->isAdmin()) {
            unset($validated['role']);
        }

        // Owner protection
        if ($user->isOwner() && isset($validated['role']) && $validated['role'] !== 'owner') {
            return response()->json([
                'message' => 'لا يمكن تغيير رتبة مالك المنظومة / Cannot change owner role.',
            ], 422);
        }

        $user->update($validated);

        AuditLog::log('user.updated', $user, [
            'updated_fields' => array_keys($validated),
        ]);

        return response()->json([
            'message' => 'تم تحديث بيانات المستخدم / User updated successfully',
            'user' => new UserResource($user),
        ]);
    }

    public function toggleActive(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        if ($user->isOwner()) {
            return response()->json([
                'message' => 'لا يمكن تعطيل حساب مالك المنظومة / Cannot deactivate owner account.',
            ], 422);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        AuditLog::log('user.toggle_active', $user, [
            'is_active' => $user->is_active,
        ]);

        return response()->json([
            'message' => $user->is_active ? 'تم تفعيل الحساب' : 'تم تعطيل الحساب',
            'user' => new UserResource($user),
        ]);
    }
}
