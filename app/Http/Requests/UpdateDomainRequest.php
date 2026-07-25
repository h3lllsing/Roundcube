<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $domain = $this->route('domain');

        return [
            'name' => 'required|string|max:255|unique:domains,name,' . ($domain->id ?? 'NULL') . ',id,deleted_at,NULL',
            'status' => 'required|in:active,suspended,expired',
            'notes' => 'nullable|string',
            'imap_host' => 'nullable|string|max:255',
            'imap_port' => 'nullable|integer|min:1|max:65535',
            'imap_encryption' => 'nullable|in:none,ssl,tls',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|in:none,ssl,tls',
            'smtp_username' => 'nullable|string|max:255',
        ];
    }
}
