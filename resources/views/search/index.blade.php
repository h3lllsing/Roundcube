@extends('layouts.admin')
@section('title', 'Search Results')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Search</h1>
</div>

<form method="GET" action="{{ route('search.index') }}" class="mb-4">
    <div class="input-group">
        <input type="text" name="q" class="form-control form-control-lg" placeholder="Search domains, email accounts, users..." minlength="2" value="{{ $query }}" autofocus>
        <button class="btn btn-primary" type="submit">Search</button>
    </div>
</form>

@if(strlen($query) < 2)
    <p class="text-muted">Enter at least 2 characters to search.</p>
@elseif($results->isEmpty())
    <p class="text-muted">No results found for "{{ $query }}".</p>
@else
    <div class="list-group">
        @foreach($results as $result)
            <a href="{{ $result['url'] }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <span>
                    <span class="badge bg-secondary me-2">{{ $result['type'] }}</span>
                    {{ $result['label'] }}
                </span>
                <span>&rarr;</span>
            </a>
        @endforeach
    </div>
@endif
@endsection
