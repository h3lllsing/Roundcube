<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHostingRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'provider' => 'nullable|string|max:255',
            'plan' => 'nullable|string|max:255',
            'domain' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,inactive,expired,suspended,pending_transfer,cancelled',
            'notes' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
        ];
    }
}
