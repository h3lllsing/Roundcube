@extends('layouts.admin')

@section('title', 'Edit Note')
@section('breadcrumbTitle', 'Note #' . $note->id)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="Edit Note" subtitle="Update note content" />
        <form action="{{ route('notes.update', $note->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $note->updated_at->format('Y-m-d H:i:s') }}">
            <x-form.textarea name="content" label="Content" :value="old('content', $note->content)" required />
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Attached To</label>
                    <select name="notable_type" id="notable_type"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
                        <option value="">None (standalone)</option>
                        <option value="feature" @selected(old('notable_type', $note->notable_type) === 'feature')>Feature</option>
                        <option value="module" @selected(old('notable_type', $note->notable_type) === 'module')>Module</option>
                    </select>
                </div>
                <div id="notable_id_group" @class(['hidden' => !old('notable_type', $note->notable_type)])>
                    <label for="notable_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Entity</label>
                    <select name="notable_id" id="notable_id"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
                        <option value="">Select...</option>
                        @if (old('notable_type', $note->notable_type) === 'feature')
                            @foreach ($features as $id => $name)
                                <option value="{{ $id }}" @selected(old('notable_id', $note->notable_id) == $id)>{{ $name }}</option>
                            @endforeach
                        @elseif (old('notable_type', $note->notable_type) === 'module')
                            @foreach ($modules as $id => $name)
                                <option value="{{ $id }}" @selected(old('notable_id', $note->notable_id) == $id)>{{ $name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-4">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('notes.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('notable_type')?.addEventListener('change', function() {
    const group = document.getElementById('notable_id_group');
    const select = document.getElementById('notable_id');
    group.style.display = this.value ? '' : 'none';
    if (this.value === 'feature') {
        select.innerHTML = '<option value="">Select...</option>@foreach ($features as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach';
    } else if (this.value === 'module') {
        select.innerHTML = '<option value="">Select...</option>@foreach ($modules as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach';
    } else {
        select.innerHTML = '<option value="">Select...</option>';
    }
});
</script>
@endsection
