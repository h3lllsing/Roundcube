<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHostingRequest extends FormRequest
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
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'cpanel_url' => 'nullable|string|max:255|url',
            'service_provider_id' => 'nullable|exists:service_providers,id',
            'plan' => 'nullable|string|max:255',
            'domain' => 'nullable|string|max:255',
            'domain_ip' => 'nullable|string|max:45',
            'mail_domain_ip' => 'nullable|string|max:45',
            'cpanel_ip' => 'nullable|string|max:45',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,inactive,expired,suspended,pending_transfer,cancelled',
            'description' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
        ];
    }
}
