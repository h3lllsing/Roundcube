<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSmtpProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super-admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'sender_name' => 'required|string|max:255',
            'sender_email' => 'required|email|max:255',
            'reply_to_email' => 'nullable|email|max:255',
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|string|in:tls,ssl',
            'smtp_username' => 'required|string|max:255',
            'smtp_password' => 'required|string|max:255',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0|max:9999',
        ];
    }
}
