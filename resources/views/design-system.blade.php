@extends('layouts.admin')

@section('title', 'Design System')

@section('content')
<div class="max-w-5xl mx-auto space-y-12">

    <x-page-header title="Design System" subtitle="Component showcase — Developer preview only." />

    {{-- Buttons --}}
    <x-card>
        <x-slot:header><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Buttons</h2></x-slot:header>
        <div class="space-y-6">
            <div class="flex flex-wrap gap-2 items-center">
                <x-button variant="primary" size="sm">Primary</x-button>
                <x-button variant="danger" size="sm">Danger</x-button>
                <x-button variant="success" size="sm">Success</x-button>
                <x-button variant="outline" size="sm">Outline</x-button>
                <x-button variant="ghost" size="sm">Ghost</x-button>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                <x-button variant="primary">Primary</x-button>
                <x-button variant="danger">Danger</x-button>
                <x-button variant="success">Success</x-button>
                <x-button variant="outline">Outline</x-button>
                <x-button variant="ghost">Ghost</x-button>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                <x-button variant="primary" size="lg">Primary</x-button>
                <x-button variant="danger" size="lg">Danger</x-button>
                <x-button variant="success" size="lg">Success</x-button>
                <x-button variant="outline" size="lg">Outline</x-button>
                <x-button variant="ghost" size="lg">Ghost</x-button>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                <x-button disabled variant="primary">Disabled</x-button>
                <x-button variant="outline">Link</x-button>
            </div>
        </div>
    </x-card>

    {{-- Cards --}}
    <x-card>
        <x-slot:header><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Cards</h2></x-slot:header>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-card variant="default">
                <p class="text-sm text-gray-600 dark:text-gray-400">Default card with soft shadow and border.</p>
            </x-card>
            <x-card variant="glass">
                <p class="text-sm text-gray-600 dark:text-gray-400">Glass card with backdrop blur.</p>
            </x-card>
            <x-card variant="bordered">
                <p class="text-sm text-gray-600 dark:text-gray-400">Bordered card, transparent background.</p>
            </x-card>
        </div>
        <div class="mt-4">
            <x-card variant="default" hover>
                <p class="text-sm text-gray-600 dark:text-gray-400">Hover card with lift effect.</p>
            </x-card>
        </div>
        <div class="mt-4">
            <x-card>
                <x-slot:header><span class="font-medium">With Header</span></x-slot:header>
                <p class="text-sm text-gray-600 dark:text-gray-400">Body content.</p>
                <x-slot:footer><span class="text-xs text-gray-400">Footer</span></x-slot:footer>
            </x-card>
        </div>
    </x-card>

    {{-- Badges --}}
    <x-card>
        <x-slot:header><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Badges</h2></x-slot:header>
        <div class="space-y-3">
            <div class="flex flex-wrap gap-2 items-center">
                <x-badge variant="default">Default</x-badge>
                <x-badge variant="primary">Primary</x-badge>
                <x-badge variant="success">Success</x-badge>
                <x-badge variant="warning">Warning</x-badge>
                <x-badge variant="danger">Danger</x-badge>
                <x-badge variant="info">Info</x-badge>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                <x-badge variant="active">Active</x-badge>
                <x-badge variant="expired">Expired</x-badge>
                <x-badge variant="suspended">Suspended</x-badge>
                <x-badge variant="enabled">Enabled</x-badge>
                <x-badge variant="disabled">Disabled</x-badge>
                <x-badge variant="unknown">Unknown</x-badge>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                <x-badge variant="success" dot>Dot</x-badge>
                <x-badge variant="primary" size="sm">Small</x-badge>
                <x-badge variant="success" size="lg">Large</x-badge>
            </div>
        </div>
    </x-card>

    {{-- Fields --}}
    <x-card>
        <x-slot:header><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Fields</h2></x-slot:header>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-field label="Domain Name" value="example.com" />
            <x-field label="Registration Date">
                <x-date value="2025-01-15" />
            </x-field>
            <x-field label="Monthly Cost">
                <x-money value="29.99" />
            </x-field>
            <x-field label="Status">
                <x-badge variant="active">Active</x-badge>
            </x-field>
        </div>
        <div class="mt-6 space-y-3">
            <x-field label="Inline field" value="Shows on same line" inline />
            <x-field label="Another inline" value="Label and value side by side" inline />
        </div>
        <div class="mt-6 space-y-3">
            <x-field label="DNS Servers">
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 dark:bg-black text-sm font-mono">ns1.example.com</span>
                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 dark:bg-black text-sm font-mono">ns2.example.com</span>
                </div>
            </x-field>
            <x-field label="Notes">
                <p class="text-sm whitespace-pre-wrap">Multi-line content<br>can be rendered inside a field slot.</p>
            </x-field>
        </div>
    </x-card>

    {{-- Dates & Money --}}
    <x-card>
        <x-slot:header><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Dates & Money</h2></x-slot:header>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div><p class="text-xs text-gray-400 mb-1">Date</p><x-date value="2026-07-01" /></div>
            <div><p class="text-xs text-gray-400 mb-1">Date with custom format</p><x-date value="2026-07-01" format="d/m/Y" /></div>
            <div><p class="text-xs text-gray-400 mb-1">Empty date</p><x-date :value="null" /></div>
            <div><p class="text-xs text-gray-400 mb-1">Money</p><x-money value="99.99" /></div>
            <div><p class="text-xs text-gray-400 mb-1">Zero money</p><x-money value="0" /></div>
            <div><p class="text-xs text-gray-400 mb-1">Empty money</p><x-money :value="null" /></div>
        </div>
    </x-card>

    {{-- Alerts --}}
    <x-card>
        <x-slot:header><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Alerts</h2></x-slot:header>
        <div class="space-y-3">
            <x-alert variant="info">This is an informational message.</x-alert>
            <x-alert variant="success">Operation completed successfully.</x-alert>
            <x-alert variant="warning">Please review your input before submitting.</x-alert>
            <x-alert variant="danger">An error occurred while processing your request.</x-alert>
        </div>
        <div class="mt-4">
            <x-alert variant="info" dismissible>This alert can be dismissed.</x-alert>
        </div>
    </x-card>

    {{-- Empty States --}}
    <x-card>
        <x-slot:header><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Empty States</h2></x-slot:header>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <x-empty-state icon="globe" title="No domains found" message="Register or add domains to track them." tag="div" />
            </div>
            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                <x-empty-state icon="search" title="No results match" message="Try adjusting your search or filters." tag="div" />
            </div>
        </div>
    </x-card>

    {{-- Form Inputs --}}
    <x-card>
        <x-slot:header><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Form Inputs</h2></x-slot:header>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-xl">
            <x-form.input name="demo-name" label="Name" value="Example" />
            <x-form.input name="demo-email" label="Email" type="email" value="user@example.com" />
            <x-form.select name="demo-status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired']" value="active" />
            <x-form.textarea name="demo-notes" label="Notes" value="Sample content" rows="3" />
            <x-form.checkbox name="demo-agree" label="I agree to the terms" checked />
            <x-form.input name="demo-required" label="Required Field" required value="" />
        </div>
    </x-card>

    {{-- Password --}}
    <x-card>
        <x-slot:header><h2 class="text-lg font-semibold text-gray-900 dark:text-white">Password Field</h2></x-slot:header>
        <div class="max-w-sm">
            <x-form.password name="demo-password" label="Password" />
        </div>
    </x-card>

    {{-- Notification: This is a developer preview --}}
    <x-alert variant="info">
        <strong>Developer Preview.</strong> These components are the Design System foundation.
        Not all pages use them yet. Migration is ongoing.
    </x-alert>
</div>
@endsection
