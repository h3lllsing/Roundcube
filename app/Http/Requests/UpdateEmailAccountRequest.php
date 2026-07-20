<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $emailAccount = $this->route('email_account');

        return [
            'domain_id' => 'required|exists:domains,id',
            'email' => 'required|email|max:255|unique:email_accounts,email,' . ($emailAccount->id ?? 'NULL') . ',id,deleted_at,NULL',
            'password' => 'nullable|string',
            'imap_host' => 'required|string|max:255',
            'imap_port' => 'required|integer|min:1|max:65535',
            'imap_encryption' => 'required|in:ssl,tls,none',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|in:ssl,tls,none',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string',
            'status' => 'required|in:active,suspended',
            'sync_enabled' => 'boolean',
        ];
    }
}
