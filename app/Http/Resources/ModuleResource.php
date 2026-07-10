<?php

namespace App\Http\Resources;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Module */
class ModuleResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'feature_id' => $this->feature_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'feature' => new FeatureResource($this->whenLoaded('feature')),
            'permissions' => $this->whenLoaded('rolePermissions', function () {
                return $this->rolePermissions->map(fn ($p) => [
                    'role_id' => $p->role_id,
                    'can_create' => $p->can_create,
                    'can_read' => $p->can_read,
                    'can_update' => $p->can_update,
                    'can_delete' => $p->can_delete,
                    'can_approve' => $p->can_approve,
                    'can_export' => $p->can_export,
                ]);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
