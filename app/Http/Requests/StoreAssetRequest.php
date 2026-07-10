<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_tag' => 'nullable|string|max:100|unique:assets,asset_tag',
            'brand' => 'nullable|string|max:191',
            'model' => 'nullable|string|max:191',
            'processor' => 'nullable|string|max:191',
            'ram' => 'nullable|string|max:191',
            'storage' => 'nullable|string|max:191',
            'os' => 'nullable|string|max:191',
            'serial_number' => 'nullable|string|max:191',
            'status' => 'nullable|string|in:available,assigned,lost,decommissioned',
            'headphone' => 'nullable|string|max:191',
            'additional_equipments' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'assigned_user_name' => 'nullable|string|max:191',
            'reporting_authority' => 'nullable|string|max:191',
            'location_id' => 'nullable|exists:asset_locations,id',
            'premises' => 'nullable|string|max:191',
            'department' => 'nullable|string|max:191',
            'issue_date' => 'nullable|date',
            'return_date' => 'nullable|date',
            'condition' => 'nullable|string|in:new,good,fair,poor,damaged',
            'description' => 'nullable|string',
            'additional_comments' => 'nullable|string',
            'primary_image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'vault_entry_id' => 'nullable|exists:password_vault,id',
            'qr_identifier' => 'nullable|string|max:100|unique:assets,qr_identifier',
            'anydesk_id' => 'nullable|string|max:191',
            'anydesk_password' => 'nullable|string|max:191',
            'module_id' => 'nullable|exists:modules,id',
        ];
    }
}
