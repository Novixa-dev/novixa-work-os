<?php

namespace App\Modules\CRM\Resources;

use App\Models\Opportunity;
use App\Modules\Identity\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Opportunity */
class OpportunityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'name_ar' => $this->client->name_ar,
                'name_en' => $this->client->name_en,
            ]),
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'name_ar' => $this->product->name_ar,
                'name_en' => $this->product->name_en,
            ]),
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'stage' => $this->stage,
            'estimated_value' => (float) $this->estimated_value,
            'currency' => $this->currency ?? 'USD',
            'owner_id' => $this->owner_id,
            'owner' => $this->whenLoaded('owner', fn () => new UserResource($this->owner)),
            'expected_close_date' => $this->expected_close_date?->format('Y-m-d'),
            'next_follow_up_date' => $this->next_follow_up_date?->format('Y-m-d'),
            'source' => $this->source,
            'notes' => $this->notes,
            'won_delivery_project_id' => $this->won_delivery_project_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
