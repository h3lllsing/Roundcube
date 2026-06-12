<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpiryTrackerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'provider' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'renewal_date' => 'nullable|date|after_or_equal:expiry_date',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,expired,pending_renewal,cancelled',
            'notes' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
        ];
    }
}
