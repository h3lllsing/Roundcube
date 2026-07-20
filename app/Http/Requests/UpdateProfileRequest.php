<?php

namespace App\Http\Requests;

use App\Rules\NotCommonPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'updated_at' => 'required|date',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$userId,
            'password' => ['nullable', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).+$/', 'confirmed', new NotCommonPassword],
            'current_password' => 'required_with:password|string|current_password',
        ];
    }
}
