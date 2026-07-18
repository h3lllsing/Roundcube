<x-mail::message>
# Audit Event

An audit event has been recorded.

**Action:** {{ $action }}

**Resource:** {{ $resource }}

**Performed by:** {{ $causer }}

**Timestamp:** {{ $timestamp }}

<x-mail::button :url="$url">
View Audit Trail
</x-mail::button>

Regards,<br>
{{ config('app.name') }}
</x-mail::message>
