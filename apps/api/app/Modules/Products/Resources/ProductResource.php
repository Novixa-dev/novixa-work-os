<?php

namespace App\Modules\Products\Resources;

use App\Models\Product;
use App\Modules\Identity\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'tagline_ar' => $this->tagline_ar,
            'tagline_en' => $this->tagline_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'lifecycle_status' => $this->lifecycle_status,
            'owner_id' => $this->owner_id,
            'owner' => $this->whenLoaded('owner', fn () => new UserResource($this->owner)),
            'repository_url' => $this->repository_url,
            'website_url' => $this->website_url,
            'demo_url' => $this->demo_url,
            'documentation_url' => $this->documentation_url,
            'projects_count' => $this->whenCounted('projects'),
            'tasks_count' => $this->whenCounted('tasks'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
