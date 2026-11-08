<?php

namespace App\Modules\Projects\Resources;

use App\Models\Project;
use App\Modules\Identity\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'type' => $this->type,
            'status' => $this->status,
            'priority' => $this->priority,
            'lead_id' => $this->lead_id,
            'lead' => $this->whenLoaded('lead', fn () => new UserResource($this->lead)),
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'name_ar' => $this->product->name_ar,
                'name_en' => $this->product->name_en,
                'slug' => $this->product->slug,
            ]),
            'client_id' => $this->client_id,
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'name_ar' => $this->client->name_ar,
                'name_en' => $this->client->name_en,
            ]),
            'members' => $this->whenLoaded('members', fn () => UserResource::collection($this->members)),
            'milestones' => $this->whenLoaded('milestones'),
            'tasks_count' => $this->whenCounted('tasks'),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'progress_percentage' => (int) $this->progress_percentage,
            'archived_at' => $this->archived_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
