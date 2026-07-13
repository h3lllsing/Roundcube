@extends('layouts.admin')

@section('title', 'Email Credentials')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Email Credentials" subtitle="Store and manage domain email passwords.">
        <x-slot:actions>
            @if(isset($canExport) && $canExport)
            <x-button href="{{ route('export', 'domain-emails') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            @if($canCreate)
            <x-button href="{{ route('domain-emails.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Email
            </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <x-filter-input name="search" value="{{ request('search') }}" placeholder="Search email or domain..." />
        <x-filter-select name="domain_id" placeholder="All domains" :options="$domains" />
        <x-filter-select name="status" placeholder="All statuses" :options="['active' => 'Active', 'expired' => 'Expired', 'suspended' => 'Suspended']" />
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'domain_id', 'status']))
            <x-button href="{{ route('domain-emails.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <x-table :bulk="false">
        <x-slot:head>
            <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Email</th>
            <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Domain</th>
            <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
            <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Provider</th>
            <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
        </x-slot:head>
                @forelse ($emails as $email)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3">
                            <a href="{{ route('domain-emails.show', $email->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">{{ $email->email }}</a>
                            <x-copy-button :text="$email->email" title="Copy email" />
                        </td>
                        <td class="px-6 py-3">
                            @if($email->domain)
                            <a href="{{ route('domain-emails.index', ['domain_id' => $email->domain_id]) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                {{ $email->domain->name }}
                            </a>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            @if($email->status)
                            <x-badge :variant="['active' => 'success', 'expired' => 'danger', 'suspended' => 'warning'][$email->status] ?? 'default'">{{ ucfirst($email->status) }}</x-badge>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400">{{ $email->serviceProvider->name ?? '—' }}</td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            @php
                                $_canEdit = auth()->user()->hasRole('super-admin') || ($email->module && auth()->user()->canOnModule($email->module, 'update'));
                                $_canDelete = auth()->user()->hasRole('super-admin') || ($email->module && auth()->user()->canOnModule($email->module, 'delete'));
                                $_canReveal = auth()->user()->hasRole('super-admin') || ($vaultModule && auth()->user()->canOnModule($vaultModule, 'reveal'));
                                $_hasPassword = (bool)$email->password;
                            @endphp
                            <div x-data="{ open: false, style: '' }" @click.away="open = false" class="relative inline-block">
                                <button type="button" @click="
                                    open = !open;
                                    if (open) { $nextTick(() => { const r = $el.getBoundingClientRect(); style = 'position:fixed;left:' + r.left + 'px;top:' + (r.bottom + 4) + 'px;z-index:50'; }); }
                                " @keydown.escape.prevent="open = false" class="inline-flex items-center justify-center w-9 h-9 rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/50" aria-haspopup="true" :aria-expanded="open.toString()" aria-label="Email actions" title="Email actions">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                </button>
                                <div x-show="open" :style="style" x-cloak role="menu" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-gray-50 dark:bg-black rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 w-48">
                                    <a href="{{ route('domain-emails.show', $email->id) }}" class="block px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40" role="menuitem">View Details</a>

                                    @if($_hasPassword && $_canReveal)
                                    <div class="border-t border-gray-100 dark:border-gray-700/50 my-1"></div>
                                    <div class="flex items-center px-3 py-1.5 gap-2" role="menuitem">
                                        <span class="flex-1 min-w-0 text-sm text-gray-700 dark:text-white truncate" title="Password">Password</span>
                                        <x-copy-button :passwordRoute="route('domain-emails.password', $email->id)" class="shrink-0 w-6 h-6 !p-0 inline-flex items-center justify-center dark:text-gray-300" title="Copy Password" />
                                    </div>
                                    <div class="border-t border-gray-100 dark:border-gray-700/50 my-1"></div>
                                    @endif

                                    @if($_canEdit)
                                    <a href="{{ route('domain-emails.edit', $email->id) }}" class="block px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40" role="menuitem">Edit</a>
                                    @endif
                                    @if($_canDelete)
                                    <form method="POST" action="{{ route('domain-emails.destroy', $email->id) }}" class="block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" data-confirm="Are you sure?" x-on:click="startLoading($el)" class="w-full text-left px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500/40" role="menuitem">Delete</button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="5" icon="box" title="No email credentials yet." message="Add your first email account to store its password." /></tr>
                @endforelse
            </tbody>
    </x-table>

    <div class="mt-4">{{ $emails->links() }}</div>
</div>


@endsection