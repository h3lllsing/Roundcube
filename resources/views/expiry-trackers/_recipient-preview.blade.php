@props(['recipients' => [], 'smtpProfileName' => 'Default System SMTP', 'enabled' => true, 'senderEmail' => '', 'senderName' => '', 'userLookup' => []])

@php
$typeLabels = [
    'assigned_user' => 'Assigned User',
    'admin' => 'Administrator',
    'custom' => 'Custom',
];

if (empty($userLookup) && !empty($recipients)) {
    $userLookup = \App\Models\User::whereIn('email', array_column($recipients, 'email'))->pluck('name', 'email');
}
@endphp

<div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
    <div class="flex items-center gap-2 mb-3">
        <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
            <svg class="w-3.5 h-3.5 text-white" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Notification Recipient Preview</h4>
    </div>

    @if(!$enabled)
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-black/30 rounded-lg px-3 py-2">
            <svg class="w-4 h-4 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Email notifications are disabled.</span>
        </div>
    @else
        <div class="text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-black/30 rounded-lg px-3 py-2.5 space-y-1.5">
            <p>SMTP Profile: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $smtpProfileName }}</span></p>
            @if($senderEmail)
            <p>From: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $senderName ? $senderName . ' <' . $senderEmail . '>' : $senderEmail }}</span></p>
            @endif
        </div>

        @if(count($recipients))
            <div class="mt-2 space-y-1.5">
                @foreach($recipients as $r)
                @php
                    $displayName = $userLookup[$r['email']] ?? null;
                @endphp
                <div class="flex items-start gap-2 text-sm">
                    <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <div>
                        @if($displayName)
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $displayName }}</span>
                        <span class="text-gray-400 dark:text-gray-500 text-xs ml-1">{{ $r['email'] }}</span>
                        @else
                        <span class="text-gray-700 dark:text-gray-300">{{ $r['email'] }}</span>
                        @endif
                        <span class="inline-flex items-center text-xs text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-black/50 px-1.5 py-0.5 rounded ml-1">{{ $typeLabels[$r['type']] ?? ucfirst($r['type']) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">Recipients ({{ count($recipients) }})</p>
        @else
            <div class="flex items-center gap-2 mt-2 text-sm text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/10 rounded-lg px-3 py-2">
                <svg class="w-4 h-4 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                <span>No recipients selected. Email notifications will not be sent.</span>
            </div>
        @endif
    @endif
</div>
