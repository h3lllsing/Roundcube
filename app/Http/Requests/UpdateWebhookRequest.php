<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'updated_at' => 'required|date',
            'name' => 'sometimes|string|max:255',
            'url' => 'sometimes|url|max:2048|regex:/^https?:\/\//i',
            'events' => 'nullable|array',
            'events.*' => 'string|in:vault.revealed,task.created,task.updated,expiring_soon',
            'is_active' => 'boolean',
        ];
    }
}
