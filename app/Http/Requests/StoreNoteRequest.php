<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'content' => 'required|string',
            'notable_type' => 'nullable|string|in:App\Models\Feature,App\Models\Module',
            'notable_id' => 'nullable|integer|required_with:notable_type',
        ];
    }
}
