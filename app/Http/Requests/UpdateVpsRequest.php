<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVpsRequest extends FormRequest
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
            'service_provider_id' => 'nullable|exists:service_providers,id',
            'plan' => 'nullable|string|max:255',
            'ip_address' => 'nullable|string|max:45',
            'os' => 'nullable|string|max:100',
            'ram_mb' => 'nullable|integer|min:0',
            'disk_gb' => 'nullable|integer|min:0',
            'cpu_cores' => 'nullable|integer|min:0',
            'password' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'department' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'login_ids' => 'nullable|json',
            'additional_ips' => 'nullable|json',
            'status' => 'nullable|string|in:active,inactive,expired,suspended,pending_transfer,cancelled',
            'description' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
        ];
    }
}
