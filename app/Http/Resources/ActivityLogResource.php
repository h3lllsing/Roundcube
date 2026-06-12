<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Spatie\Activitylog\Models\Activity */
class ActivityLogResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'description' => $this->description,
            'event' => $this->event,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'subject' => $this->whenLoaded('subject', fn() => [
                'id' => $this->subject?->getAttribute('id'),
                'label' => match (class_basename($this->subject_type)) {
                    'Feature' => $this->subject?->getAttribute('name'),
                    'Module' => $this->subject?->getAttribute('name'),
                    'Task' => $this->subject?->getAttribute('title'),
                    'Note' => 'Note #'.$this->subject?->getAttribute('id'),
                    'VaultEntry' => $this->subject?->getAttribute('service_name'),
                    default => $this->subject_type.' #'.$this->subject?->getAttribute('id'),
                },
            ]),
            'causer' => $this->whenLoaded('causer', fn() => [
                'id' => $this->causer->getAttribute('id'),
                'name' => $this->causer->getAttribute('name'),
                'email' => $this->causer->getAttribute('email'),
            ]),
            'properties' => $this->properties,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
