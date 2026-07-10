<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVaultRequest extends FormRequest
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
            'service_name' => 'sometimes|required|string|max:255',
            'service_url' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string',
            'encrypted_password' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
            'description' => 'nullable|string',
        ];
    }
}
