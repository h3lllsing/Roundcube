@extends('layouts.admin')

@section('title', $user->name)
@section('breadcrumbTitle', $user->name)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="{{ $user->name }}" subtitle="Enterprise Permission Inspector" back-url="{{ route('users.index') }}">
            <x-slot:actions>
                <span class="text-xs text-gray-400 dark:text-gray-500 bg-white/70 dark:bg-black/70 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700">
                    {{ now()->format('Y-m-d H:i') }}
                </span>
            </x-slot:actions>
        </x-page-header>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">ID</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Roles</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    @forelse ($user->roles as $role)
                        <a href="{{ route('roles.show', $role->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $role->name }}</a>@if(!$loop->last), @endif
                    @empty
                        <span class="text-gray-400">—</span>
                    @endforelse
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                <p class="mt-1 text-sm">
                    @if ($user->suspended_at)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Suspended</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>
                    @endif
                </p>
            </div>
            @if ($lastLogin)
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Last Login</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $lastLogin->created_at->format('Y-m-d H:i') }}</p>
            </div>
            @endif
        </div>
        <div class="flex items-center gap-3 pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
            <x-button href="{{ route('users.permissions.edit', $user->id) }}" variant="outline" size="sm">Edit Permissions</x-button>
            <x-button href="{{ route('users.clone', $user->id) }}" variant="outline" size="sm">Clone User</x-button>
            <x-button href="{{ route('users.edit', $user->id) }}" variant="primary" size="sm">Edit</x-button>
            <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
        </div>
    </div>

    @if ($inspectedIsSuperAdmin)
    <div class="mt-6 p-4 rounded-xl bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-sm text-green-700 dark:text-green-300">
        This user has unrestricted access through the <strong>Super Admin</strong> role.
    </div>
    @endif

    <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <x-stat-card label="Roles" :value="$summary['roles_count']" icon="users" color="indigo" />
        <x-stat-card label="Accessible Modules" :value="$summary['accessible_modules']" icon="shield" color="emerald" />
        <x-stat-card label="Denied Modules" :value="$summary['denied_modules']" icon="shield" color="rose" />
        <x-stat-card label="Overrides" :value="$summary['overrides_count']" icon="document" color="violet" />
        <x-stat-card label="Allowed Permissions" :value="$summary['allowed_permissions']" icon="features" color="emerald" />
        <x-stat-card label="Denied Permissions" :value="$summary['denied_permissions']" icon="features" color="rose" />
    </div>

    <div class="mt-6 bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-md font-semibold mb-1">Permission Matrix</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Shows all modules with effective permissions. Source indicates where each permission originates.</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th scope="col" class="text-left px-3 py-2 font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">Module</th>
                        <th scope="col" class="text-left px-3 py-2 font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">Feature</th>
                        <th scope="col" class="text-center px-2 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">Read</th>
                        <th scope="col" class="text-center px-2 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">Create</th>
                        <th scope="col" class="text-center px-2 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">Update</th>
                        <th scope="col" class="text-center px-2 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">Delete</th>
                        <th scope="col" class="text-center px-2 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">Approve</th>
                        <th scope="col" class="text-center px-2 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">Export</th>
                        <th scope="col" class="text-center px-2 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">Reveal</th>
                        <th scope="col" class="text-center px-2 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">Import</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($modulePermissions as $mp)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-3 py-2.5 font-medium whitespace-nowrap">{{ $mp->module_name }}</td>
                        <td class="px-3 py-2.5 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">{{ $mp->feature ?? '—' }}</td>
                        @foreach (config('permissions.keys') as $perm)
                        <td class="px-2 py-2.5 text-center">
                            @php
                                $p = $mp->permissions[$perm] ?? ['effective' => false, 'source' => 'None', 'user_override' => null, 'role' => null];
                                $allowed = $p['effective'];
                                $source = $p['source'];
                            @endphp
                            @if ($allowed)
                                <span class="text-green-600 dark:text-green-400 font-bold text-sm">&#10003;</span>
                            @else
                                <span class="text-red-400 dark:text-red-300 font-bold text-sm">&#10005;</span>
                            @endif
                            <span class="block text-[9px] leading-tight mt-0.5
                                @if ($source === 'Role')
                                    text-blue-600 dark:text-blue-400
                                @elseif ($source === 'User Override' && $allowed)
                                    text-purple-600 dark:text-purple-400
                                @elseif ($source === 'User Override' && !$allowed)
                                    text-purple-600 dark:text-purple-400
                                @elseif ($inspectedIsSuperAdmin)
                                    text-green-600 dark:text-green-400
                                @else
                                    text-gray-400 dark:text-gray-500
                                @endif
                            ">
                                @if ($inspectedIsSuperAdmin)
                                    Super Admin
                                @elseif ($source === 'Role')
                                    Role
                                @elseif ($source === 'User Override' && $allowed)
                                    Override Allow
                                @elseif ($source === 'User Override' && !$allowed)
                                    Override Deny
                                @else
                                    None
                                @endif
                            </span>
                        </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-3 py-8 text-center text-sm text-gray-400 dark:text-gray-500">No modules found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-md font-semibold">Offboarding Checklist</h3>
            @if($offboardingChecklist['suspended_at'])
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Suspended</span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>
            @endif
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <p class="text-xs text-gray-500 dark:text-gray-400">Vault Entries</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $offboardingChecklist['vault_entries_count'] }}</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <p class="text-xs text-gray-500 dark:text-gray-400">Assigned Tasks</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $offboardingChecklist['assigned_tasks_count'] }}</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <p class="text-xs text-gray-500 dark:text-gray-400">Assigned Assets</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $offboardingChecklist['assigned_assets_count'] }}</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <p class="text-xs text-gray-500 dark:text-gray-400">Activities (30d)</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $offboardingChecklist['activities_30d_count'] }}</p>
            </div>
        </div>
        <div class="mt-4 flex items-center gap-3">
            @if($offboardingChecklist['can_suspend'])
                <form action="{{ route('users.suspend', $user->id) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="reason" placeholder="Suspension reason..." class="px-3 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded-lg input-focus outline-none bg-white dark:bg-black text-gray-900 dark:text-gray-100" required>
                    <x-button type="submit" variant="danger" size="sm" data-confirm="Suspend this user? They will lose access immediately.">Suspend</x-button>
                </form>
            @elseif($offboardingChecklist['can_unsuspend'])
                <form action="{{ route('users.unsuspend', $user->id) }}" method="POST" class="inline">
                    @csrf
                    @if($user->suspension_reason)
                        <span class="text-xs text-gray-500 dark:text-gray-400">Reason: {{ $user->suspension_reason }}</span>
                    @endif
                    <x-button type="submit" variant="success" size="sm" data-confirm="Unsuspend this user? They will regain access.">Unsuspend</x-button>
                </form>
            @endif
        </div>
    </div>

    <x-activity-timeline subjectType="App\Models\User" :subjectId="$user->id" />
</div>
@endsection