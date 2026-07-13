@extends('layouts.admin')

@section('title', 'Create User')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-4">
        <ol class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
            <li class="flex items-center gap-1.5">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Dashboard</a>
            </li>
            <li class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('users.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Users</a>
            </li>
            <li class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-900 dark:text-gray-100 font-medium" aria-current="page">Create User</span>
            </li>
        </ol>
    </nav>

    <div class="hd">
        <h1 class="pt">Create User</h1>
        <p class="ps">Add a new user. Set basic info and role. Configure permissions after creation.</p>
    </div>

    <form action="{{ route('users.store') }}" method="POST" x-data="{ hasUnsaved: false }" @change="hasUnsaved = true">
        @csrf

        {{-- Basic Information --}}
        <div class="card">
            <div class="ch"><h2>Basic Information</h2></div>
            <div class="cb">
                <div class="fg">
                    <x-form.input name="name" label="Full Name" :value="old('name')" placeholder="e.g. Jane Smith" required />
                    <x-form.input type="email" name="email" label="Email Address" :value="old('email')" placeholder="e.g. jane@company.com" required />
                    <div class="f">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="w-full rounded-xl border bg-white dark:bg-black text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm input-focus outline-none border-gray-300 dark:border-gray-600">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending Invitation</option>
                            <option value="disabled" {{ old('status') === 'disabled' ? 'selected' : '' }}>Disabled</option>
                        </select>
                        <span class="ht">Default: Active. Use "Pending" for pre-creation before user joins.</span>
                    </div>
                    <div class="f">
                        <label>Authentication</label>
                        <div class="rg">
                            <label><input type="radio" name="auth_method" value="invite" checked> Send Invitation Email</label>
                            <label><input type="radio" name="auth_method" value="manual"> Set Password Manually</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Password (shown when manual selected) --}}
        <div class="card" id="password-fields" style="display:none">
            <div class="ch"><h2>Set Password</h2></div>
            <div class="cb">
                <div class="fg">
                    <x-form.input type="password" name="password" label="Password" autocomplete="new-password" />
                    <x-form.input type="password" name="password_confirmation" label="Confirm Password" autocomplete="new-password" />
                </div>
            </div>
        </div>

        {{-- Role Assignment --}}
        <div class="card">
            <div class="ch"><h2>Role Assignment</h2></div>
            <div class="cb">
                <div class="fg">
                    <div class="f">
                        <label for="role">Primary Role</label>
                        <select name="roles[]" id="role" class="w-full rounded-xl border bg-white dark:bg-black text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm input-focus outline-none border-gray-300 dark:border-gray-600">
                            <option value="">— Select a role —</option>
                            @foreach ($roles ?? [] as $role)
                                <option value="{{ $role->id }}" {{ in_array($role->id, old('roles', [])) ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <span class="ht">The role determines baseline module permissions. Customize after creation.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card">
            <div class="fa">
                <a href="{{ route('users.index') }}" class="btn btn-s" @click="if(hasUnsaved && !confirm('Unsaved changes will be lost. Continue?')) $event.preventDefault()">Cancel</a>
                <button type="submit" class="btn btn-p" @click="hasUnsaved = false">Create User</button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Toggle password fields based on auth method
    document.querySelectorAll('input[name="auth_method"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var pwFields = document.getElementById('password-fields');
            if (this.value === 'manual') {
                pwFields.style.display = '';
                pwFields.querySelectorAll('input').forEach(function(i) { i.setAttribute('required', ''); });
            } else {
                pwFields.style.display = 'none';
                pwFields.querySelectorAll('input').forEach(function(i) { i.removeAttribute('required'); });
            }
        });
    });

    // Disable role select when placeholder selected so empty value is not submitted
    document.querySelector('form').addEventListener('submit', function() {
        var roleSelect = document.getElementById('role');
        if (roleSelect && roleSelect.value === '') {
            roleSelect.disabled = true;
        }
    });
</script>
@endpush

@push('styles')
    @vite('resources/css/permissions.css')
@endpush
@endsection
