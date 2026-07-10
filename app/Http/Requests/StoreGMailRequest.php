<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGMailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|string|max:255',
            'user_name' => 'nullable|string|max:255',
            'pseudo' => 'nullable|string|max:255',
            'emails_address' => 'nullable|string|max:255',
            'password' => 'nullable|string',
            'security_number' => 'nullable|string|max:255',
            'security_number_person' => 'nullable|string|max:255',
            'recovery_email' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'assigned' => 'nullable|string|max:255',
            'user_remarks' => 'nullable|string',
            'comments' => 'nullable|string',
        ];
    }
}
