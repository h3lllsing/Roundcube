<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVaultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'service_name' => 'required|string|max:255',
            'service_url' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'required_without:encrypted_password|string|nullable',
            'encrypted_password' => 'required_without:password|string|nullable',
            'module_id' => 'nullable|exists:modules,id',
            'description' => 'nullable|string',
        ];
    }
}
