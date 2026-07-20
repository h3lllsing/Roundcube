@extends('layouts.admin')

@section('title', 'Edit Domain')

@section('content')
<x-page-header title="Edit Domain" subtitle="{{ $domain->name }}" backUrl="{{ route('domains.show', $domain) }}" backLabel="Back to Domain" />

<div class="max-w-2xl">
    <x-card>
        <form method="POST" action="{{ route('domains.update', $domain) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $domain->updated_at }}">

            <div class="space-y-5">
                <x-form.input name="name" label="Domain Name" value="{{ $domain->name }}" required />

                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'suspended' => 'Suspended', 'expired' => 'Expired']" value="{{ $domain->status }}" required />

                <x-form.textarea name="notes" label="Notes" rows="3" value="{{ $domain->notes }}" />

                <div class="flex items-center gap-3 pt-2">
                    <x-button type="submit" variant="primary" x-on:click="startLoading($el)">Update Domain</x-button>
                    <x-button href="{{ route('domains.show', $domain) }}" variant="outline">Cancel</x-button>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection
