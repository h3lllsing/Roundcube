<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreModulePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'role_id' => 'required|integer|exists:roles,id',
            'can_create' => 'boolean',
            'can_read' => 'boolean',
            'can_update' => 'boolean',
            'can_delete' => 'boolean',
            'can_approve' => 'boolean',
            'can_export' => 'boolean',
            'can_reveal' => 'boolean',
            'can_import' => 'boolean',
        ];
    }
}
