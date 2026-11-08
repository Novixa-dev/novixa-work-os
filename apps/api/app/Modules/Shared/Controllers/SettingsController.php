<?php

namespace App\Modules\Shared\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\WorkspaceSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function getWorkspaceInfo(): JsonResponse
    {
        $info = WorkspaceSetting::get('workspace_info', [
            'name_ar' => 'نوڤيكسا للبرمجيات والحلول الرقمية',
            'name_en' => 'Novixa Software & Digital Solutions',
            'tagline_ar' => 'نبني التقنية التي تجعل أعمالك أقوى',
            'tagline_en' => 'Engineered for Growth',
            'website' => 'https://novixa.dev/ar',
            'email' => 'info@novixa.dev',
            'phone' => '+967 776 248 526',
            'default_currency' => 'USD',
            'default_timezone' => 'Asia/Riyadh',
        ]);

        return response()->json([
            'settings' => $info,
        ]);
    }

    public function updateWorkspaceInfo(Request $request): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'tagline_ar' => ['nullable', 'string', 'max:255'],
            'tagline_en' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'default_currency' => ['required', 'string', 'max:3'],
            'default_timezone' => ['required', 'string', 'max:50'],
        ]);

        WorkspaceSetting::set('workspace_info', $validated);

        AuditLog::log('settings.workspace_updated', null, $validated);

        return response()->json([
            'message' => 'تم تحديث إعدادات المنظومة بنجاح',
            'settings' => $validated,
        ]);
    }
}
