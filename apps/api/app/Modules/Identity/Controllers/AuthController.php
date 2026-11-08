<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Modules\Identity\Requests\LoginRequest;
use App\Modules\Identity\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $throttleKey = 'login|' . $request->ip() . '|' . strtolower($request->input('email'));

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', ['seconds' => $seconds])],
            ])->status(429);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials, true)) {
            RateLimiter::hit($throttleKey, 60);

            AuditLog::log('auth.failed', null, [
                'email' => $request->input('email'),
                'reason' => 'invalid_credentials',
            ]);

            throw ValidationException::withMessages([
                'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة / Invalid credentials.'],
            ]);
        }

        RateLimiter::clear($throttleKey);

        /** @var User $user */
        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return response()->json([
                'message' => 'الحساب معطل حالياً. يرجى مراجعة إدارة النظام / Your account is inactive.',
            ], 403);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        // Create Sanctum personal token for SPA / API client
        $token = $user->createToken('workos_session')->plainTextToken;

        AuditLog::log('auth.login', $user, [
            'ip' => $request->ip(),
        ], $user->id);

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح / Successfully logged in',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            AuditLog::log('auth.logout', $user, [], $user->id);
            // Revoke current token
            $user->currentAccessToken()?->delete();
        }

        Auth::guard('web')->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح / Successfully logged out',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'locale' => ['nullable', 'in:ar,en'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'theme' => ['nullable', 'in:dark,light,system'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'string', 'min:8'],
        ]);

        if (!empty($validated['new_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['كلمة المرور الحالية غير صحيحة / Current password is incorrect.'],
                ]);
            }
            $user->password = Hash::make($validated['new_password']);
        }

        if (isset($validated['locale'])) $user->locale = $validated['locale'];
        if (isset($validated['timezone'])) $user->timezone = $validated['timezone'];
        if (isset($validated['theme'])) $user->theme = $validated['theme'];
        if (isset($validated['name'])) $user->name = $validated['name'];
        if (isset($validated['phone'])) $user->phone = $validated['phone'];

        $user->save();

        AuditLog::log('user.update_preferences', $user, [
            'updated_fields' => array_keys($validated),
        ], $user->id);

        return response()->json([
            'message' => 'تم تحديث التفضيلات بنجاح / Preferences updated successfully',
            'user' => new UserResource($user),
        ]);
    }
}
