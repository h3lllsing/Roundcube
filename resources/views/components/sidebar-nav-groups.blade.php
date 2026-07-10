@php $unreadCount = Auth::user()->unread_notification_count; @endphp
<nav id="sidebarNav" class="px-3 pb-2 space-y-1" aria-label="Main navigation">

    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        <span>Dashboard</span>
    </x-nav-link>

    @if($showMonitoring ?? false)
    <x-nav-link href="{{ route('monitoring.index') }}" :active="request()->routeIs('monitoring.*')">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
        <span>Monitoring</span>
    </x-nav-link>
    @endif

    <x-nav-link href="{{ route('notifications.index') }}" :active="request()->routeIs('notifications.*')">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <span>Notifications</span>
        @if($unreadCount > 0)
            <span class="ml-auto w-5 h-5 bg-gradient-to-br from-red-500 to-rose-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-sm shadow-red-500/30">{{ min($unreadCount, 9) }}</span>
        @endif
    </x-nav-link>

    <div class="nav-group" data-group="infrastructure">
        <button type="button" class="nav-group-header flex items-center gap-3 w-full px-3 py-1.5 rounded-lg text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors group" aria-expanded="true" data-nav-key="nav_infrastructure">
            <svg class="nav-chevron w-3.5 h-3.5 text-gray-400 dark:text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            <span>Infrastructure</span>
        </button>
        <div class="nav-group-content space-y-0.5 ml-1 overflow-hidden transition-all duration-200">
            @if($showProviders)
            <x-nav-link href="{{ route('service-providers.index') }}" :active="request()->routeIs('service-providers.*')">Vendors</x-nav-link>
            @endif
            @if($showHostings)
            <x-nav-link href="{{ route('hostings.index') }}" :active="request()->routeIs('hostings.*')">Hosting</x-nav-link>
            @endif
            @if($showDomains)
            <x-nav-link href="{{ route('domains.index') }}" :active="request()->routeIs('domains.*')">Domains</x-nav-link>
            @endif
            @if($showEmails)
            <x-nav-link href="{{ route('domain-emails.index') }}" :active="request()->routeIs('domain-emails.*')">Domain Emails</x-nav-link>
            @endif
            @if($showVps)
            <x-nav-link href="{{ route('vps.index') }}" :active="request()->routeIs('vps.*')">VPS Accounts</x-nav-link>
            @endif
            @if($showVoip)
            <x-nav-link href="{{ route('voip.index') }}" :active="request()->routeIs('voip.*')">VoIP</x-nav-link>
            @endif
            @if($showOtherServices)
            <x-nav-link href="{{ route('other-services.index') }}" :active="request()->routeIs('other-services.*')">SaaS Subscriptions</x-nav-link>
            @endif
            @if($showExpiryTrackers)
            <x-nav-link href="{{ route('expiry-trackers.index') }}" :active="request()->routeIs('expiry-trackers.*')">Renewals</x-nav-link>
            @endif
            @if($showAssets)
            <x-nav-link href="{{ route('assets.index') }}" :active="request()->routeIs('assets.*')">Hardware Assets</x-nav-link>
            @endif
            @if($showGMails)
            <x-nav-link href="{{ route('g-mails.index') }}" :active="request()->routeIs('g-mails.*')">G-Mails</x-nav-link>
            @endif
        </div>
    </div>

    @if($showVault || $showMyVault)
    <div class="nav-group" data-group="credentials">
        <button type="button" class="nav-group-header flex items-center gap-3 w-full px-3 py-1.5 rounded-lg text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors group" aria-expanded="false" data-nav-key="nav_credentials">
            <svg class="nav-chevron w-3.5 h-3.5 text-gray-400 dark:text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            <span>Credentials</span>
        </button>
        <div class="nav-group-content space-y-0.5 ml-1 overflow-hidden transition-all duration-200">
            @if($showMyVault)
            <x-nav-link href="{{ route('vault.my') }}" :active="request()->routeIs('vault.my')">My Credentials</x-nav-link>
            @endif
            @if($showVault)
            <x-nav-link href="{{ route('vault.index') }}" :active="request()->routeIs('vault.*') && !request()->routeIs('vault.my')">Shared Credentials</x-nav-link>
            @endif
        </div>
    </div>
    @endif

    <div class="nav-group" data-group="operations">
        <button type="button" class="nav-group-header flex items-center gap-3 w-full px-3 py-1.5 rounded-lg text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors group" aria-expanded="true" data-nav-key="nav_operations">
            <svg class="nav-chevron w-3.5 h-3.5 text-gray-400 dark:text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            <span>Operations</span>
        </button>
        <div class="nav-group-content space-y-0.5 ml-1 overflow-hidden transition-all duration-200">
            <x-nav-link href="{{ route('tasks.my') }}" :active="request()->routeIs('tasks.my')">My Tasks</x-nav-link>
            <x-nav-link href="{{ route('tasks.index') }}" :active="request()->routeIs('tasks.*') && !request()->routeIs('tasks.my')">Task Management</x-nav-link>
            <x-nav-link href="{{ route('calendar') }}" :active="request()->routeIs('calendar')">Calendar</x-nav-link>
            @if($showNotes ?? false)
            <x-nav-link href="{{ route('notes.index') }}" :active="request()->routeIs('notes.*')">Notes</x-nav-link>
            @endif
        </div>
    </div>

    @hasrole('super-admin')
    <div class="nav-group" data-group="administration">
        <button type="button" class="nav-group-header flex items-center gap-3 w-full px-3 py-1.5 rounded-lg text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors group" aria-expanded="true" data-nav-key="nav_administration">
            <svg class="nav-chevron w-3.5 h-3.5 text-gray-400 dark:text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            <span>Administration</span>
        </button>
        <div class="nav-group-content space-y-0.5 ml-1 overflow-hidden transition-all duration-200">
            <x-nav-link href="{{ route('users.index') }}" :active="request()->routeIs('users.*')">Users</x-nav-link>
            <x-nav-link href="{{ route('roles.index') }}" :active="request()->routeIs('roles.*')">Roles</x-nav-link>
            <x-nav-link href="{{ route('role-templates.index') }}" :active="request()->routeIs('role-templates.*')">Role Templates</x-nav-link>
            <x-nav-link href="{{ route('privileges.index') }}" :active="request()->routeIs('privileges.*')">Privileges</x-nav-link>
            <x-nav-link href="{{ route('modules.index') }}" :active="request()->routeIs('modules.*')">Modules</x-nav-link>
            <x-nav-link href="{{ route('module-permissions.index') }}" :active="request()->routeIs('module-permissions.*')">Permissions</x-nav-link>
            <x-nav-link href="{{ route('features.index') }}" :active="request()->routeIs('features.*')">Features</x-nav-link>
            <x-nav-link href="{{ route('smtp-profiles.index') }}" :active="request()->routeIs('smtp-profiles.*')">Mail Settings</x-nav-link>
            <x-nav-link href="{{ route('activity-logs.index') }}" :active="request()->routeIs('activity-logs.*')">Audit Trail</x-nav-link>
            <x-nav-link href="{{ route('login-audits.index') }}" :active="request()->routeIs('login-audits.*')">Login History</x-nav-link>
            <x-nav-link href="{{ route('import.create') }}" :active="request()->routeIs('import.*')">Import</x-nav-link>
            <x-nav-link href="{{ route('attachments.index') }}" :active="request()->routeIs('attachments.*')">Attachments</x-nav-link>
            <x-nav-link href="{{ route('webhooks.index') }}" :active="request()->routeIs('webhooks.*')">Integrations</x-nav-link>
            <x-nav-link href="{{ route('tokens.index') }}" :active="request()->routeIs('tokens.*')">API Access</x-nav-link>
        </div>
    </div>
    @endhasrole

    @hasrole('super-admin')
    <div class="nav-group" data-group="reports">
        <button type="button" class="nav-group-header flex items-center gap-3 w-full px-3 py-1.5 rounded-lg text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors group" aria-expanded="false" data-nav-key="nav_reports">
            <svg class="nav-chevron w-3.5 h-3.5 text-gray-400 dark:text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            <span>Reports</span>
        </button>
        <div class="nav-group-content space-y-0.5 ml-1 overflow-hidden transition-all duration-200">
            <x-nav-link href="{{ route('reports.index') }}" :active="request()->routeIs('reports.*')">Reports</x-nav-link>
        </div>
    </div>
    @endhasrole

    <div class="nav-group" data-group="account">
        <button type="button" class="nav-group-header flex items-center gap-3 w-full px-3 py-1.5 rounded-lg text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors group" aria-expanded="true" data-nav-key="nav_account">
            <svg class="nav-chevron w-3.5 h-3.5 text-gray-400 dark:text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            <span>Account</span>
        </button>
        <div class="nav-group-content space-y-0.5 ml-1 overflow-hidden transition-all duration-200">
            <x-nav-link href="{{ route('profile') }}" :active="request()->routeIs('profile')">My Profile</x-nav-link>
            <x-nav-link href="{{ route('my-permissions') }}" :active="request()->routeIs('my-permissions')">My Access</x-nav-link>
            <a href="{{ route('guide') }}" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200 transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"/></svg>
                <span class="flex-1">Help Center</span>
                <span class="text-[10px] font-medium px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400">Guide</span>
            </a>
        </div>
    </div>

</nav>
