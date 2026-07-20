@extends('layouts.admin')

@section('title', 'Create Domain')

@section('content')
<x-page-header title="Create Domain" subtitle="Add a new email domain." backUrl="{{ route('domains.index') }}" backLabel="Back to Domains" />

<div class="max-w-2xl">
    <x-card>
        <form method="POST" action="{{ route('domains.store') }}">
            @csrf

            <div class="space-y-5">
                <x-form.input name="name" label="Domain Name" placeholder="example.com" required />

                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'suspended' => 'Suspended', 'expired' => 'Expired']" value="active" required />

                <x-form.textarea name="notes" label="Notes" rows="3" />

                <div class="flex items-center gap-3 pt-2">
                    <x-button type="submit" variant="primary" x-on:click="startLoading($el)">Create Domain</x-button>
                    <x-button href="{{ route('domains.index') }}" variant="outline">Cancel</x-button>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection
