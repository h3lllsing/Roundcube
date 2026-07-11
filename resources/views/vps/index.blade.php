@extends('layouts.admin')

@section('title', 'VPS')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="VPS" subtitle="Track virtual private servers.">
        <x-slot:actions>
            @if($canExport)
            <x-button href="{{ route('export', 'vps') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            @if($canCreate)
            <x-button href="{{ route('vps.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <x-filter-input name="search" value="{{ request('search') }}" placeholder="Search VPS..." />
        <x-filter-select name="status" placeholder="All statuses" :options="['active' => 'Active', 'expired' => 'Expired', 'suspended' => 'Suspended']" />
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'status']))
            <x-button href="{{ route('vps.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="vps">
        <x-bulk-actions type="vps" colspan="8" :actions="$bulkActions" />
    </form>

        <x-table>
            <x-slot:head>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">VPS</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Vendor</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">VPS IP</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Expiry</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Associate</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
            </x-slot:head>
                    @forelse ($vpsList as $vps)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $vps->id }}" aria-label="Select {{ $vps->name }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                            <td class="px-6 py-3 font-medium"><a href="{{ route('vps.show', $vps->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $vps->name }}</a></td>
                            <td class="px-6 py-3">
                                <x-badge variant="{{ $vps->status }}" size="sm">{{ ucfirst($vps->status) }}</x-badge>
                            </td>
                            <td class="px-6 py-3 text-gray-500">{{ $vps->serviceProvider->name ?? '—' }}</td>
                            <td class="px-6 py-3">
                                @if($vps->ip_address)
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm">{{ $vps->ip_address }}</span>
                                        <x-copy-button :text="$vps->ip_address" title="Copy IP" />
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-gray-500 dark:text-gray-400">{{ $vps->expiry_date?->format('Y-m-d') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $vps->user->name ?? '—' }}</td>
                            <td class="px-6 py-3 whitespace-nowrap">
                            @php
                                $_canReveal = auth()->user()->hasRole('super-admin') || ($vaultModule && auth()->user()->canOnModule($vaultModule, 'reveal'));
                                $_hasPassword = (bool)$vps->password;
                            @endphp
                            <div x-data="{ open: false, style: '' }" @click.away="open = false" class="relative inline-block">
                                <button type="button" @click="
                                    open = !open;
                                    if (open) {
                                        $nextTick(() => {
                                            const r = $el.getBoundingClientRect();
                                            style = 'position:fixed;left:' + r.left + 'px;top:' + (r.bottom + 4) + 'px;z-index:50';
                                        });
                                    }
                                " @keydown.escape.prevent="open = false" class="inline-flex items-center justify-center w-9 h-9 rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/50" aria-haspopup="true" :aria-expanded="open.toString()" aria-label="VPS actions" title="VPS actions">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                </button>
                                <div x-show="open" :style="style" x-cloak role="menu" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-gray-50 dark:bg-black rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 w-48">
                                    <a href="{{ route('vps.show', $vps->id) }}" class="block px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40" role="menuitem">View Details</a>

                                    @if($_hasPassword && $_canReveal)
                                    <div class="border-t border-gray-100 dark:border-gray-700/50 my-1"></div>
                                    <div class="flex items-center px-3 py-1.5 gap-2" role="menuitem">
                                        <span class="flex-1 min-w-0 text-sm text-gray-700 dark:text-white truncate" title="Password">Password</span>
                                        <x-copy-button password-route="{{ url('vps') }}/{{ $vps->id }}/password" class="shrink-0 w-6 h-6 !p-0 inline-flex items-center justify-center dark:text-gray-300" title="Copy Password" />
                                    </div>
                                    <div class="border-t border-gray-100 dark:border-gray-700/50 my-1"></div>
                                    @endif

                                    <x-permission-check :module="$vps->module" action="update">
                                    <a href="{{ route('vps.edit', $vps->id) }}" class="block px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40" role="menuitem">Edit</a>
                                    </x-permission-check>

                                    <x-permission-check :module="$vps->module" action="delete">
                                    <form method="POST" action="{{ route('vps.destroy', $vps->id) }}" class="block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" data-confirm="Are you sure?" data-confirm-button="Delete" x-on:click="startLoading($el)" class="w-full text-left px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500/40" role="menuitem">Delete</button>
                                    </form>
                                    </x-permission-check>
                                </div>
                            </div>
                            </td>
                        </tr>
                @empty
                    <tr><x-empty-state :colspan="8" icon="server" title="No VPS found." message="Add VPS servers to monitor them." /></tr>
                @endforelse
            </tbody>
        </x-table>

        <div class="mt-4">{{ $vpsList->links() }}</div>
</div>


@endsection
