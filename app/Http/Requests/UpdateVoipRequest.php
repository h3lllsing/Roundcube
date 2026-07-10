<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVoipRequest extends FormRequest
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
            'phone_number' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:50',
            'direction' => 'nullable|string|in:inbound,outbound,both',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'extension_password' => 'nullable|string|max:255',
            'dashboard_url' => 'nullable|string|max:255',
            'server_ip' => 'nullable|string|max:45',
            'cost' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:active,inactive,expired,suspended,pending_transfer,cancelled',
            'number_status' => 'nullable|string|max:50',
            'outbound_code' => 'nullable|string|max:50',
            'team_details' => 'nullable|string',
            'extension' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
        ];
    }
}
