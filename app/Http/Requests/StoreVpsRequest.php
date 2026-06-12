<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVpsRequest extends FormRequest
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
            'ip_address' => 'nullable|string|max:45',
            'os' => 'nullable|string|max:100',
            'ram_mb' => 'nullable|integer|min:0',
            'disk_gb' => 'nullable|integer|min:0',
            'cpu_cores' => 'nullable|integer|min:0',
            'cost' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:active,inactive,expired,suspended,pending_transfer,cancelled',
            'notes' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
        ];
    }
}
