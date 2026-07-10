<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $feature = $this->route('feature');

        return [
            'updated_at' => 'required|date',
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255|unique:features,slug,'.($feature->id ?? 'NULL'),
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ];
    }
}
