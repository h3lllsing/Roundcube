@props([
    'model',
    'notableType',
])

@php
$user = Auth::user();
$module = method_exists($model, 'module') ? $model->module : null;
$notes = $model->notes()->with('user')->orderBy('is_pinned', 'desc')->orderBy('created_at', 'desc')->get();
@endphp

<div class="mt-6 bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <h3 class="text-md font-semibold mb-4">Notes & Updates ({{ $notes->count() }})</h3>

    <form action="{{ route('notes.store') }}" method="POST" class="mb-4">
        @csrf
        <input type="hidden" name="notable_type" value="{{ $notableType }}">
        <input type="hidden" name="notable_id" value="{{ $model->id }}">
        <textarea name="content" rows="2" placeholder="Write a note..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none" required></textarea>
        <div class="mt-2 flex justify-end">
            <x-button type="submit" variant="primary" size="sm">Post Note</x-button>
        </div>
    </form>

    @forelse ($notes as $note)
        <div class="py-3 border-b border-gray-200 dark:border-gray-700 last:border-b-0 {{ $note->is_pinned ? 'bg-amber-50 dark:bg-amber-900/10 -mx-6 px-6 rounded' : '' }}">
            @if($note->is_pinned)
                <div class="text-xs font-semibold text-amber-600 dark:text-amber-400 mb-1">
                    <svg class="w-3 h-3 inline -mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Pinned
                </div>
            @endif
            <div class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $note->content }}</div>
            <div class="mt-1 flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                <span>{{ $note->user->name ?? '—' }}</span>
                <span>{{ $note->created_at->format('d M Y, h:i A') }}</span>
                <x-permission-check :module="$module" action="update">
                    <form action="{{ route('notes.pin', $note->id) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-200">
                            {{ $note->is_pinned ? 'Unpin' : 'Pin' }}
                        </button>
                    </form>
                </x-permission-check>
                @if($note->user_id === $user->id || $user->hasRole('super-admin'))
                    <form action="{{ route('notes.destroy', $note->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" data-confirm="Delete this note?" data-confirm-button="Delete" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">Delete</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-400 dark:text-gray-500">No notes yet.</p>
    @endforelse
</div>
