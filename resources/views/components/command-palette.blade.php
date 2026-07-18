<div id="cmdPalette" class="hidden fixed inset-0 z-[200] flex items-start justify-center pt-[12vh]" role="dialog" aria-modal="true" aria-label="Command palette">
    <div id="cmdOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="cmdk-modal relative w-full max-w-lg bg-white dark:bg-black rounded-2xl shadow-2xl shadow-indigo-500/10 border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="flex items-center gap-3 px-4 border-b border-gray-200 dark:border-gray-700">
            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input id="cmdInput" type="text" placeholder="Search pages..." autocomplete="off" aria-label="Search pages"
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
var cmdPages = [
    {label:'Dashboard', url:'{{ route("dashboard") }}', icon:'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'},
    {label:'Notifications', url:'{{ route("notifications.index") }}', icon:'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'},
    {label:'Monitoring', url:'{{ route("monitoring.index") }}', icon:'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'},
    {label:'My Profile', url:'{{ route("profile") }}', icon:'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'},
    {label:'My Permissions', url:'{{ route("my-permissions") }}', icon:'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'},
    @hasrole('super-admin')
    {label:'Users', url:'{{ route("users.index") }}', icon:'M12 4.354a4 4 0 110 5.292M7 20.75a7 7 0 0110 0'},
    {label:'Roles', url:'{{ route("roles.index") }}', icon:'M12 4.354a4 4 0 110 5.292M7 20.75a7 7 0 0110 0'},
    {label:'Privileges', url:'{{ route("privileges.index") }}', icon:'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'},
    {label:'Modules', url:'{{ route("modules.index") }}', icon:'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'},
    {label:'Permissions', url:'{{ route("module-permissions.index") }}', icon:'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'},
    {label:'Features', url:'{{ route("features.index") }}', icon:'M13 10V3L4 14h7v7l9-11h-7z'},
    {label:'Activity Logs', url:'{{ route("activity-logs.index") }}', icon:'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'},
    {label:'Login Audits', url:'{{ route("login-audits.index") }}', icon:'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'},
    @endhasrole
];
</script>
