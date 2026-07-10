<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDomainRequest extends FormRequest
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
            'registration_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'auto_renew' => 'nullable|boolean',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,inactive,expired,suspended,pending_transfer,cancelled',
            'cloudflare_status' => 'nullable|string|in:enabled,disabled,unknown',
            'dns_servers' => 'nullable|string',
            'description' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
            'hosting_id' => 'nullable|exists:hostings,id',
            'service_provider_id' => 'nullable|exists:service_providers,id',
        ];
    }
}
