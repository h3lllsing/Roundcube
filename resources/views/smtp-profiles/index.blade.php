@extends('layouts.admin')

@section('title', 'SMTP Profiles')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="SMTP Profiles" subtitle="Manage email sender profiles">
        <x-slot:actions>
            <x-button href="{{ route('smtp-profiles.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('smtp-profiles.index') }}" class="mb-6">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search profiles..."
                    class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none w-48">
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <select name="status"
                    class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <x-button type="submit" variant="primary" size="sm">Filter</x-button>
                @if(request()->anyFilled(['search', 'status']))
                    <x-button href="{{ route('smtp-profiles.index') }}" variant="outline" size="sm">Clear</x-button>
                @endif
            </div>
        </div>
    </form>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Name</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Sender Email</th>
                    <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Default</th>
                    <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                    <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profiles as $profile)
                <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-6 py-3">
                        <a href="{{ route('smtp-profiles.show', $profile) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ $profile->name }}
                        </a>
                    </td>
                    <td class="px-6 py-3 text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <span>{{ $profile->sender_name }} <{{ $profile->sender_email }}></span>
                            <x-copy-button :text="$profile->sender_email" title="Copy sender email" />
                        </div>
                    </td>
                    <td class="px-6 py-3 text-center">
                        @if($profile->is_default)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">Yes</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-center">
                        @if($profile->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">Active</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-900/30 text-gray-600 dark:text-gray-400">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-center whitespace-nowrap">
                        @php $_canEdit = auth()->user()->hasRole('super-admin'); @endphp
                        <div class="flex items-center justify-center gap-1">
                            @if(!$profile->is_default && $_canEdit)
                            <form action="{{ route('smtp-profiles.set-default', $profile) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <x-action color="sky" label="Set Default" />
                            </form>
                            @endif
                            <div x-data="{ open: false, style: '' }" @click.away="open = false" class="relative inline-block">
                                <button type="button" @click="
                                    open = !open;
                                    if (open) { $nextTick(() => { const r = $el.getBoundingClientRect(); style = 'position:fixed;left:' + r.left + 'px;top:' + (r.bottom + 4) + 'px;z-index:50'; }); }
                                " @keydown.escape.prevent="open = false" class="inline-flex items-center justify-center w-9 h-9 rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/50" aria-haspopup="true" :aria-expanded="open.toString()" aria-label="SMTP actions" title="SMTP actions">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                </button>
                                <div x-show="open" :style="style" x-cloak role="menu" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-gray-50 dark:bg-black rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 w-44">
                                    <a href="{{ route('smtp-profiles.show', $profile) }}" class="block px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40" role="menuitem">View Details</a>
                                    @if($_canEdit)
                                    <a href="{{ route('smtp-profiles.edit', $profile) }}" class="block px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40" role="menuitem">Edit</a>
                                    <form action="{{ route('smtp-profiles.toggle-active', $profile) }}" method="POST" class="block">
                                        @csrf @method('PATCH')
                                        <button type="submit" data-confirm="{{ $profile->is_active ? 'Deactivate this profile?' : 'Activate this profile?' }}" x-on:click="startLoading($el)" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40" role="menuitem">{{ $profile->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    </form>
                                    <form action="{{ route('smtp-profiles.duplicate', $profile) }}" method="POST" class="block">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40" role="menuitem">Duplicate</button>
                                    </form>
                                    @if(!$profile->isInUse())
                                    <form method="POST" action="{{ route('smtp-profiles.destroy', $profile) }}" class="block">
                                        @csrf @method('DELETE')
                                        <button type="submit" data-confirm="Delete this profile?" x-on:click="startLoading($el)" class="w-full text-left px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500/40" role="menuitem">Delete</button>
                                    </form>
                                    @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <x-empty-state icon="server" title="No SMTP profiles yet" message="Create your first email sender profile to start sending emails." />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $profiles->links() }}
    </div>
</div>
@endsection