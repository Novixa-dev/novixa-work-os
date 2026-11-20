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
            'links' => $this->whenLoaded('links', fn () => $this->links->map(fn ($l) => [
                'id' => $l->id,
                'title' => $l->title,
                'category' => $l->category ?? 'other',
                'url' => $l->url,
                'note' => $l->note,
                'user_id' => $l->user_id,
                'author' => $l->author ? ['id' => $l->author->id, 'name' => $l->author->name] : null,
                'created_at' => $l->created_at?->toIso8601String(),
            ])),
            'notes' => $this->whenLoaded('notes', fn () => $this->notes->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'type' => $n->type,
                'content' => $n->content,
                'url' => $n->url,
                'username' => $n->username,
                'password' => $n->password,
                'environment' => $n->environment,
                'color' => $n->color,
                'is_pinned' => (bool) $n->is_pinned,
                'user_id' => $n->user_id,
                'author' => $n->author ? ['id' => $n->author->id, 'name' => $n->author->name] : null,
                'created_at' => $n->created_at?->toIso8601String(),
            ])),
            'reports' => $this->whenLoaded('reports', fn () => $this->reports->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->title,
                'type' => $r->type,
                'content' => $r->content,
                'status' => $r->status,
                'user_id' => $r->user_id,
                'author' => $r->author ? ['id' => $r->author->id, 'name' => $r->author->name] : null,
                'created_at' => $r->created_at?->toIso8601String(),
            ])),
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
