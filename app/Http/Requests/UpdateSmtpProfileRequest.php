<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSmtpProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super-admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'updated_at' => 'required|date',
            'name' => 'sometimes|required|string|max:255',
            'sender_name' => 'sometimes|required|string|max:255',
            'sender_email' => 'sometimes|required|email|max:255',
            'reply_to_email' => 'nullable|email|max:255',
            'smtp_host' => 'sometimes|required|string|max:255',
            'smtp_port' => 'sometimes|required|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|string|in:tls,ssl',
            'smtp_username' => 'sometimes|required|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0|max:9999',
        ];
    }
}
