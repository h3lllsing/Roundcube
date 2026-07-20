<?php

namespace App\Http\Requests;

use App\Rules\NotCommonPassword;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'updated_at' => 'required|date',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . ($userId ?? 'NULL') . ',id,deleted_at,NULL',
            'password' => ['nullable', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).+$/', 'confirmed', new NotCommonPassword],
            'role' => 'nullable|in:user,admin',
            'suspended_at' => 'nullable|date',
        ];
    }
}
