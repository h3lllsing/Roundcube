<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $featureId = optional($this->route('feature'))->id ?? $this->input('feature_id');
        return [
            'feature_id' => $this->route('feature') ? 'nullable|exists:features,id' : 'required|exists:features,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:modules,slug,NULL,id,feature_id,' . $featureId,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
