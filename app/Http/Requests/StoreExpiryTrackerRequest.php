<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreExpiryTrackerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();
            $enabled = !empty($data['email_notifications_enabled']);

            if ($enabled) {
                $hasRecipient = !empty($data['notify_assigned_user'])
                    || !empty($data['notify_admins'])
                    || !empty($data['notify_custom_emails']);

                if (!$hasRecipient) {
                    $validator->errors()->add('notify_assigned_user', 'At least one recipient type must be selected.');
                }
            }
        });
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'service_provider_id' => 'nullable|exists:service_providers,id',
            'username' => 'nullable|string|max:255',
            'login_url' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'renewal_date' => 'nullable|date|after_or_equal:expiry_date',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,expired,pending_renewal,cancelled',
            'description' => 'nullable|string',
            'module_id' => 'nullable|exists:modules,id',
            'email_notifications_enabled' => 'nullable|boolean',
            'smtp_profile_id' => 'nullable|integer|exists:smtp_profiles,id,is_active,1',
            'notify_days' => 'nullable|array',
            'notify_days.*' => 'integer|in:1,7,15,30',
            'notify_on_expiry_day' => 'nullable|boolean',
            'notify_assigned_user' => 'nullable|boolean',
            'notify_admins' => 'nullable|boolean',
            'notify_custom_emails' => 'nullable|array',
            'notify_custom_emails.*' => 'nullable|email|max:255',
        ];
    }
}
