<?php

namespace App\Modules\Work\Resources;

use App\Models\Task;
use App\Modules\Identity\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Task */
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'project_id' => $this->project_id,
            'project' => $this->whenLoaded('project', fn () => [
                'id' => $this->project->id,
                'code' => $this->project->code,
                'name_ar' => $this->project->name_ar,
                'name_en' => $this->project->name_en,
            ]),
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
            'milestone_id' => $this->milestone_id,
            'parent_id' => $this->parent_id,
            'status' => $this->status,
            'priority' => $this->priority,
            'assignee_id' => $this->assignee_id,
            'assignee' => $this->whenLoaded('assignee', fn () => new UserResource($this->assignee)),
            'reporter_id' => $this->reporter_id,
            'reporter' => $this->whenLoaded('reporter', fn () => new UserResource($this->reporter)),
            'is_my_day' => (bool) $this->is_my_day,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'claimed_at' => $this->claimed_at?->toIso8601String(),
            'is_taken' => $this->assignee_id !== null && in_array($this->status, ['in_progress', 'in_review'], true),
            'is_overdue' => $this->due_date && $this->due_date->isPast() && $this->status !== 'done',
            'estimated_hours' => $this->estimated_hours ? (float) $this->estimated_hours : null,
            'actual_hours' => $this->actual_hours ? (float) $this->actual_hours : null,
            'kanban_order' => (int) $this->kanban_order,
            'labels' => $this->whenLoaded('labels', fn () => $this->labels->map(fn ($l) => [
                'id' => $l->id,
                'name_ar' => $l->name_ar,
                'name_en' => $l->name_en,
                'color' => $l->color,
            ])),
            'subtasks_count' => $this->whenCounted('subtasks'),
            'comments_count' => $this->whenCounted('comments'),
            'attachments_count' => $this->whenCounted('attachments'),
            'archived_at' => $this->archived_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
