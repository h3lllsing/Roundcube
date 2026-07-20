<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailAccountResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'domain_id' => $this->domain_id,
            'email' => $this->email,
            'status' => $this->status?->value,
            'sync_enabled' => $this->sync_enabled,
            'last_sync_at' => $this->last_sync_at,
            'imap_host' => $this->imap_host,
            'imap_port' => $this->imap_port,
            'imap_encryption' => $this->imap_encryption,
            'smtp_host' => $this->smtp_host,
            'smtp_port' => $this->smtp_port,
            'smtp_encryption' => $this->smtp_encryption,
            'smtp_username' => $this->smtp_username,
            'domain' => new DomainResource($this->whenLoaded('domain')),
            'assigned_users' => UserResource::collection($this->whenLoaded('assignedUsers')),
            'created_by' => $this->whenHas('created_by'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
