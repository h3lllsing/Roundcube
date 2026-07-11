<x-mail::message>
@if($isTest)
# 🔬 TEST EMAIL — Sample Data

**This is a test email.** The data shown is from the selected tracker for verification purposes.

---

@endif
# @if($daysLeft < 0)Expired — @elseif($daysLeft === 0)Expires Today — @else Renewal Reminder — @endif {{ $title }}

**Resource Type:** {{ $resourceType }}  
**Resource Name:** {{ $title }}
@if($relatedDomain)
**Related Domain:** {{ $relatedDomain }}
@endif
@if($relatedHosting)
**Related Hosting:** {{ $relatedHosting }}
@endif
@if($provider)
**Provider:** {{ $provider }}
@endif
@if($expiryDate)
**Expiry Date:** {{ $expiryDate }}
@endif
@if($daysLeft >= 0)
**Days Remaining:** {{ $daysLeft }}
@else
**Days Overdue:** {{ abs($daysLeft) }}
@endif
@if($status)
**Current Status:** {{ ucfirst($status) }}
@endif
@if($assignedUser)
**Assigned User:** {{ $assignedUser }}
@endif
@if($cost)
**Cost:** {{ $cost }}
@endif

{{ $recipientReason }}

<x-mail::button :url="$portalLink">
View in OpsPilot
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
