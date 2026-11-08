<?php

namespace App\Modules\Identity\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'job_title' => $this->job_title,
            'locale' => $this->locale ?? 'ar',
            'timezone' => $this->timezone ?? 'Asia/Riyadh',
            'theme' => $this->theme ?? 'dark',
            'avatar_url' => $this->avatar_url,
            'phone' => $this->phone,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
