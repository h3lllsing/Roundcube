<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'file' => 'required|file|max:10240',
            'notable_type' => 'nullable|string|in:App\Models\Domain,App\Models\Hosting,App\Models\Vps,App\Models\Voip,App\Models\ServiceProvider,App\Models\DomainEmail,App\Models\OtherService,App\Models\ExpiryTracker,App\Models\Note,App\Models\Task,App\Models\Feature,App\Models\Module',
            'notable_id' => 'nullable|integer',
        ];
    }
}
