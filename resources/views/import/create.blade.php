@extends('layouts.admin')

@section('title', 'Import CSV')
@section('breadcrumbTitle', 'Import CSV')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-page-header title="Import CSV" subtitle="Import data from a CSV file" />

    <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data" class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        @csrf

        <div>
            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Resource Type</label>
            <select name="type" id="type" required
                class="w-full px-3 py-2.5 border rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none @error('type') border-red-400 dark:border-red-500 @else border-gray-300 dark:border-gray-600 @enderror">
                <option value="">Select type...</option>
                @foreach ($types as $type)
                    @if (in_array($type, $allowed))
                        <option value="{{ $type }}" @selected(old('type') === $type)>{{ ucwords(str_replace('-', ' ', $type)) }}</option>
                    @endif
                @endforeach
            </select>
            @error('type')
                <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CSV File</label>
            <input type="file" name="file" id="file" accept=".csv,.txt" required
                class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 transition-all cursor-pointer @error('file') border border-red-400 dark:border-red-500 rounded-xl @enderror">
            @error('file')
                <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $message }}
                </p>
            @enderror
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">CSV file with headers matching field names. Max 2MB.</p>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <x-button type="submit" variant="primary" size="sm">Import</x-button>
            <x-button href="{{ url()->previous() }}" variant="outline" size="sm">Cancel</x-button>
        </div>
    </form>
</div>
@endsection
