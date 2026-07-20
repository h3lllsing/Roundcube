<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:domains,name,NULL,id,deleted_at,NULL',
            'status' => 'required|in:active,suspended,expired',
            'notes' => 'nullable|string',
        ];
    }
}
