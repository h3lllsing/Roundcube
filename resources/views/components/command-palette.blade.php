<div id="cmdPalette" class="hidden fixed inset-0 z-[200] flex items-start justify-center pt-[12vh]" role="dialog" aria-modal="true" aria-label="Command palette">
    <div id="cmdOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="cmdk-modal relative w-full max-w-lg bg-white dark:bg-black rounded-2xl shadow-2xl shadow-indigo-500/10 border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="flex items-center gap-3 px-4 border-b border-gray-200 dark:border-gray-700">
            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input id="cmdInput" type="text" placeholder="Search pages and records..." autocomplete="off" aria-label="Search pages and records"
                class="flex-1 py-4 bg-transparent text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-400 outline-none border-0">
            <kbd class="hidden sm:inline-flex text-[10px] font-semibold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-black text-gray-400 dark:text-gray-500">ESC</kbd>
        </div>
        <div id="cmdResults" class="max-h-72 overflow-y-auto p-2 space-y-0.5" role="listbox"></div>
        <div class="px-4 py-2.5 border-t border-gray-200 dark:border-gray-700 flex items-center gap-3 text-[11px] text-gray-500">
            <span class="flex items-center gap-1"><kbd class="px-1 py-0.5 rounded bg-gray-100 dark:bg-black font-mono">↑↓</kbd> Navigate</span>
            <span class="flex items-center gap-1"><kbd class="px-1 py-0.5 rounded bg-gray-100 dark:bg-black font-mono">↵</kbd> Open</span>
        </div>
    </div>
</div>

<script>
var cmdSearchUrl = '{{ url('/api/search') }}';
var cmdPages = [
    {label:'Dashboard', url:'{{ route("dashboard") }}', icon:'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'},
    {label:'Notifications', url:'{{ route("notifications.index") }}', icon:'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'},
    {label:'Calendar', url:'{{ route("calendar") }}', icon:'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'},
    {label:'My Tasks', url:'{{ route("tasks.my") }}', icon:'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'},
    {label:'Task Management', url:'{{ route("tasks.index") }}', icon:'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'},
    {label:'Service Providers', url:'{{ route("service-providers.index") }}', icon:'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'},
    {label:'Hosting', url:'{{ route("hostings.index") }}', icon:'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2'},
    {label:'Domains', url:'{{ route("domains.index") }}', icon:'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9'},
    {label:'Domain Emails', url:'{{ route("domain-emails.index") }}', icon:'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'},
    {label:'VPS Accounts', url:'{{ route("vps.index") }}', icon:'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01'},
    {label:'VoIP', url:'{{ route("voip.index") }}', icon:'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'},
    {label:'Other Services', url:'{{ route("other-services.index") }}', icon:'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4'},
    {label:'Renewals', url:'{{ route("expiry-trackers.index") }}', icon:'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'},
    {label:'Assets', url:'{{ route("assets.index") }}', icon:'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'},
    {label:'My Credentials', url:'{{ route("vault.my") }}', icon:'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'},
    {label:'Shared Credentials', url:'{{ route("vault.index") }}', icon:'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'},
    {label:'My Profile', url:'{{ route("profile") }}', icon:'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'},
    {label:'My Permissions', url:'{{ route("my-permissions") }}', icon:'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'},
    {label:'Knowledge Base', url:'{{ route("guide") }}', icon:'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'},
    {label:'Notes', url:'{{ route("notes.index") }}', icon:'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'},
    @hasrole('super-admin')
    {label:'Users', url:'{{ route("users.index") }}', icon:'M12 4.354a4 4 0 110 5.292M7 20.75a7 7 0 0110 0'},
    {label:'Roles', url:'{{ route("roles.index") }}', icon:'M12 4.354a4 4 0 110 5.292M7 20.75a7 7 0 0110 0'},
    {label:'Role Templates', url:'{{ route("role-templates.index") }}', icon:'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'},
    {label:'Privileges', url:'{{ route("privileges.index") }}', icon:'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'},
    {label:'Modules', url:'{{ route("modules.index") }}', icon:'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'},
    {label:'Permissions', url:'{{ route("module-permissions.index") }}', icon:'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'},
    {label:'Features', url:'{{ route("features.index") }}', icon:'M13 10V3L4 14h7v7l9-11h-7z'},
    {label:'SMTP Profiles', url:'{{ route("smtp-profiles.index") }}', icon:'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'},
    {label:'Activity Logs', url:'{{ route("activity-logs.index") }}', icon:'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'},
    {label:'Login Audits', url:'{{ route("login-audits.index") }}', icon:'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'},
    {label:'Import', url:'{{ route("import.create") }}', icon:'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12'},
    {label:'Attachments', url:'{{ route("attachments.index") }}', icon:'M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13'},
    {label:'Webhooks', url:'{{ route("webhooks.index") }}', icon:'M13 10V3L4 14h7v7l9-11h-7z'},
    {label:'API Access', url:'{{ route("tokens.index") }}', icon:'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'},
    {label:'Reports', url:'{{ route("reports.index") }}', icon:'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'},
    @endhasrole
];
</script>
