<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $module = $this->route('module');
        return [
            'feature_id' => $this->route('feature') ? 'nullable|exists:features,id' : 'sometimes|exists:features,id',
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255|unique:modules,slug,' . ($module->id ?? 'NULL') . ',id,feature_id,' . ($module->feature_id ?? 'NULL'),
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
