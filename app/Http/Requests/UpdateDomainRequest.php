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
        ];
    }
}
