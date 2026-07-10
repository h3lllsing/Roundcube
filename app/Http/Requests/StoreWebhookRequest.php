<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048|regex:/^https?:\/\//i',
            'events' => 'nullable|array',
            'events.*' => 'string|in:vault.revealed,task.created,task.updated,expiring_soon',
            'is_active' => 'boolean',
        ];
    }
}
