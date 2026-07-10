<x-mail::message>
# Renewal Reminder

Hello,

This is a renewal reminder for:

**Title:** {{ $title }}  
**Expires:** {{ $expiryDate }}  
**Days Left:** {{ $daysLeft >= 0 ? $daysLeft : 'Overdue by ' . abs($daysLeft) . ' day(s)' }}  
**Type:** {{ $type }}  
**Cost:** {{ $cost }}  
**Provider:** {{ $provider }}  
**Assigned to:** {{ $assignedUser }}

<x-mail::button :url="$portalLink">
View in Portal
</x-mail::button>

Please take the necessary action to renew or update the status.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
