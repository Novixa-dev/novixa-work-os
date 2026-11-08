<?php

namespace App\Modules\Work\Resources;

use App\Modules\Identity\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $base = (new TaskResource($this->resource))->toArray($request);

        $base['subtasks'] = TaskResource::collection($this->whenLoaded('subtasks'));
        $base['dependencies'] = TaskResource::collection($this->whenLoaded('dependencies'));
        $base['dependents'] = TaskResource::collection($this->whenLoaded('dependents'));
        $base['watchers'] = UserResource::collection($this->whenLoaded('watchers'));
        $base['comments'] = $this->whenLoaded('comments', fn () => $this->comments->map(fn ($c) => [
            'id' => $c->id,
            'user' => new UserResource($c->user),
            'comment' => $c->comment,
            'created_at' => $c->created_at?->toIso8601String(),
        ]));
        $base['attachments'] = $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($a) => [
            'id' => $a->id,
            'filename' => $a->filename,
            'size_bytes' => $a->size_bytes,
            'mime_type' => $a->mime_type,
            'user' => new UserResource($a->user),
            'created_at' => $a->created_at?->toIso8601String(),
        ]));

        return $base;
    }
}
