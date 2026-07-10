<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDomainEmailRequest extends FormRequest
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
            'email' => 'sometimes|required|string|max:255',
            'password' => 'nullable|string|max:255',
            'service_provider_id' => 'nullable|exists:service_providers,id',
            'domain_id' => 'nullable|exists:domains,id',
            'storage_mb' => 'nullable|integer|min:0',
            'cost' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'status' => 'nullable|string|in:active,expired,cancelled',
            'description' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
        ];
    }
}
