<?php

namespace App\Http\Requests;

use App\Rules\NotCommonPassword;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'email' => 'required|email|max:255|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).+$/', 'confirmed', new NotCommonPassword],
            'role' => 'nullable|in:user,admin',
            'status' => 'nullable|in:active,suspended',
        ];
    }
}
