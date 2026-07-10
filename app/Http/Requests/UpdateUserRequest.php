<?php

namespace App\Http\Requests;

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
        $user = $this->route('user');

        return [
            'updated_at' => 'required|date',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.($user->id ?? 'NULL'),
            'password' => 'nullable|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/|confirmed',
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,id',
        ];
    }
}
