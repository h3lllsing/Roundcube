<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceProviderRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'type' => 'nullable|string|max:100',
            'provider' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'login_id' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'password' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:active,inactive,expired,suspended,pending_transfer,cancelled',
            'description' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
        ];
    }
}
