<?php

namespace App\Modules\CRM\Resources;

use App\Modules\Identity\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'type' => $this->type,
            'industry' => $this->industry,
            'status' => $this->status,
            'website' => $this->website,
            'phone' => $this->phone,
            'email' => $this->email,
            'city' => $this->city,
            'country' => $this->country,
            'account_manager_id' => $this->account_manager_id,
            'account_manager' => $this->whenLoaded('accountManager', fn () => new UserResource($this->accountManager)),
            'contacts' => $this->whenLoaded('contacts'),
            'opportunities_count' => $this->whenCounted('opportunities'),
            'projects_count' => $this->whenCounted('projects'),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
