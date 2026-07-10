<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOtherServiceRequest extends FormRequest
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
            'service_type' => 'required|string|in:saas,api,monitoring,analytics,cdn,ssl,other',
            'service_provider_id' => 'nullable|exists:service_providers,id',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'login_url' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'status' => 'nullable|string|in:active,expired,cancelled',
            'description' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
        ];
    }
}
