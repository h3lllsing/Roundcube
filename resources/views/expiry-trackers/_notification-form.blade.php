@php
    $tracker ??= null;
    $smtpProfileOptions = $smtpProfiles ?? [];
    if ($smtpProfileOptions instanceof \Illuminate\Support\Collection) {
        $smtpProfileOptions = $smtpProfileOptions->toArray();
    }
@endphp

<hr class="border-gray-200 dark:border-gray-700">

<div class="pt-2">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Email Notifications</h3>

    <x-form.checkbox
        name="email_notifications_enabled"
        label="Enable Email Notifications"
        :checked="old('email_notifications_enabled', $tracker?->email_notifications_enabled ?? false)"
    />

    <div id="notification-disabled-reason" class="mt-2 {{ old('email_notifications_enabled', $tracker?->email_notifications_enabled ?? false) ? 'hidden' : '' }}">
        <x-form.select
            name="disable_reason"
            label="Disable Reason"
            :options="['' => 'Select reason...', 'Manual' => 'Manual', 'Migrated' => 'Migrated', 'Cancelled' => 'Cancelled', 'Duplicate' => 'Duplicate', 'Other' => 'Other']"
            :value="old('disable_reason', $tracker?->disable_reason ?? '')"
        />
    </div>

    <div id="notification-settings" class="mt-4 space-y-4 {{ old('email_notifications_enabled', $tracker?->email_notifications_enabled ?? false) ? '' : 'hidden' }}">
        <x-form.select
            name="smtp_profile_id"
            label="Send From / SMTP Profile"
            :options="['' => 'Default System SMTP'] + $smtpProfileOptions"
            :value="old('smtp_profile_id', $tracker?->smtp_profile_id)"
        />

        <fieldset>
            <legend class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notify Before</legend>
            <div class="space-y-1">
                @foreach([30, 15, 7, 1] as $day)
                    <x-form.checkbox
                        name="notify_days[]"
                        :value="$day"
                        :label="$day . ' days before'"
                        :checked="in_array($day, old('notify_days', $tracker?->notify_days_before ?? [30, 15, 7, 1]))"
                    />
                @endforeach
                <x-form.checkbox
                    name="notify_on_expiry_day"
                    label="On expiry day"
                    :checked="old('notify_on_expiry_day', $tracker?->notify_on_expiry_day ?? false)"
                />
            </div>
        </fieldset>

        <fieldset>
            <legend class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Recipients</legend>
            <div class="space-y-1">
                <x-form.checkbox name="notify_assigned_user" label="Assigned User" :checked="old('notify_assigned_user', $tracker?->notify_assigned_user ?? true)" />
                <x-form.checkbox name="notify_admins" label="All Administrators" :checked="old('notify_admins', $tracker?->notify_admins ?? false)" />
            </div>
        </fieldset>

        <div x-data="{ emails: {{ json_encode(old('notify_custom_emails', $tracker?->notify_custom_emails ?? [])) }} }">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Custom Email Recipients</label>
            <div id="custom-email-list" class="space-y-2 mt-1">
                <template x-for="(email, i) in emails" :key="i">
                    <div class="flex gap-2">
                        <input type="email" x-model="emails[i]" :name="'notify_custom_emails[' + i + ']'" placeholder="email@example.com"
                            class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-transparent px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-colors" />
                        <button type="button" x-on:click="emails.splice(i, 1)" class="px-2 py-2 text-sm text-red-500 hover:text-red-700 dark:hover:text-red-300 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">&times;</button>
                    </div>
                </template>
            </div>
            <button type="button" x-on:click="emails.push('')" class="mt-2 text-sm text-indigo-600 dark:text-indigo-400 hover:underline">+ Add Email</button>
        </div>

        @if($tracker)
        @include('expiry-trackers._recipient-preview', [
            'recipients' => $recipientPreview ?? [],
            'smtpProfileName' => $tracker->smtpProfile?->name ?? 'Default System SMTP',
            'enabled' => $tracker->email_notifications_enabled,
            'senderEmail' => $senderEmail ?? '',
            'senderName' => $senderName ?? '',
        ])
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 space-y-3">
            <div class="flex flex-wrap gap-2">
                <button type="button" id="preview-email-btn"
                    data-url="{{ route('expiry-trackers.preview-email', $tracker->id) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-black border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                    Preview Email
                </button>
                <button type="button" id="send-test-email-btn"
                    data-url="{{ route('expiry-trackers.test-email', $tracker->id) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-gradient-to-br from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 rounded-xl shadow-sm shadow-indigo-500/20 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    Send Test Email
                </button>
                <button type="button" id="send-reminder-now-btn"
                    data-url="{{ route('expiry-trackers.send-reminder', $tracker->id) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-gradient-to-br from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 rounded-xl shadow-sm shadow-amber-500/20 transition-all focus:outline-none focus:ring-2 focus:ring-amber-500/50">
                    Send Reminder Now
                </button>
            </div>

            <div class="text-sm text-gray-500 dark:text-gray-400 space-y-1">
                <p>SMTP Profile: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $tracker->smtpProfile?->name ?? 'Default System SMTP' }}</span></p>
                <p>Last notification: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $tracker->last_notification_sent_at?->diffForHumans() ?? 'Never' }}</span>
                    @if($tracker->notifications()->count())
                        (source: {{ $tracker->notifications()->latest()->value('trigger_source') ?? '—' }})
                    @endif
                </p>
                <p>Next due: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $tracker->next_notification_due_at?->format('Y-m-d') ?? '—' }}</span></p>
                <a href="{{ route('expiry-trackers.notification-history', $tracker->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">View full notification history &rarr;</a>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var enabledCheckbox = document.querySelector('[name="email_notifications_enabled"]');
    var settingsDiv = document.getElementById('notification-settings');
    var disableReasonDiv = document.getElementById('notification-disabled-reason');
    if (enabledCheckbox && settingsDiv) {
        enabledCheckbox.addEventListener('change', function() {
            settingsDiv.classList.toggle('hidden', !this.checked);
            if (disableReasonDiv) {
                disableReasonDiv.classList.toggle('hidden', this.checked);
            }
        });
    }

    @if($tracker)
    var previewBtn = document.getElementById('preview-email-btn');
    var testBtn = document.getElementById('send-test-email-btn');
    var reminderBtn = document.getElementById('send-reminder-now-btn');

    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            var url = this.getAttribute('data-url');
            fetch(url)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var modalHtml = '<div id="previewModalOverlay" class="fixed inset-0 z-50 flex items-center justify-center p-4">' +
                        '<div class="fixed inset-0 bg-black/60 backdrop-blur-sm" data-close-preview></div>' +
                        '<div class="scale-in relative bg-white dark:bg-black rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col">' +
                        '<div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">' +
                        '<div><h3 class="text-base font-semibold">Email Preview</h3>' +
                        '<p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">' +
                        'From: <span class="font-medium">' + escapeHtml(data.sender_name) + ' &lt;' + escapeHtml(data.sender_email) + '&gt;</span>' +
                        ' | Profile: <span class="font-medium">' + escapeHtml(data.profileName) + '</span></p>' +
                        '<p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Subject: <span class="font-medium">' + escapeHtml(data.subject) + '</span></p></div>' +
                        '<div class="flex items-center gap-2">' +
                        '<form action="' + previewBtn.getAttribute('data-url').replace('preview-email', 'test-email') + '" method="POST" class="inline">' +
                        '@csrf' +
                        '<button type="submit" class="px-3 py-1.5 text-xs font-medium text-white bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg hover:from-indigo-600 hover:to-indigo-700">Send Test Email</button>' +
                        '</form>' +
                        '<button data-close-preview class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">&times;</button>' +
                        '</div></div>' +
                        '<div class="flex-1 overflow-y-auto p-6 bg-gray-50 dark:bg-gray-900 rounded-b-2xl">' +
                        '<div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">' +
                        '<iframe srcdoc="' + escapeHtml(data.html) + '" class="w-full border-0" style="min-height: 400px" title="Email Preview"></iframe>' +
                        '</div></div></div></div>';
                    var el = document.createElement('div');
                    el.id = 'previewModalWrapper';
                    el.innerHTML = modalHtml;
                    document.body.appendChild(el);
                    document.body.style.overflow = 'hidden';
                });
        });
    }

    if (testBtn) {
        testBtn.addEventListener('click', function() {
            if (confirm('Send a test email to your address?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = this.getAttribute('data-url');
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    if (reminderBtn) {
        reminderBtn.addEventListener('click', function() {
            if (confirm('Send reminder now to all configured recipients?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = this.getAttribute('data-url');
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    @endif
});

window.closePreviewModal = function() {
    var wrapper = document.getElementById('previewModalWrapper');
    if (wrapper) { wrapper.remove(); }
    document.body.style.overflow = '';
};

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
@endpush
