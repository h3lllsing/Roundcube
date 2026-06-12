<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDomainRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'registrar' => 'nullable|string|max:255',
            'registration_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'auto_renew' => 'nullable|boolean',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,inactive,expired,suspended,pending_transfer,cancelled',
            'dns_servers' => 'nullable|string',
            'notes' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
        ];
    }
}
