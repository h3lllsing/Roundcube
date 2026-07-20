<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DomainResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status?->value,
            'notes' => $this->notes,
            'email_accounts_count' => $this->whenHas('email_accounts_count'),
            'email_accounts' => EmailAccountResource::collection($this->whenLoaded('emailAccounts')),
            'created_by' => $this->whenHas('created_by'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
