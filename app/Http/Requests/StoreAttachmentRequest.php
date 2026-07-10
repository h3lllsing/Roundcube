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
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,zip|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/jpeg,image/png,image/gif,application/zip|max:10240',
            'notable_type' => 'nullable|string|in:App\Models\Domain,App\Models\Hosting,App\Models\Vps,App\Models\Voip,App\Models\ServiceProvider,App\Models\DomainEmail,App\Models\OtherService,App\Models\ExpiryTracker,App\Models\Note,App\Models\Task,App\Models\Feature,App\Models\Module',
            'notable_id' => 'nullable|integer',
        ];
    }
}
