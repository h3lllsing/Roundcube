@extends('layouts.admin')
@section('title', 'Server Error')
@section('content')
<div class="text-center py-5">
    <h1 class="display-1 text-danger">500</h1>
    <h4 class="mb-4">Server Error</h4>
    <p class="text-muted mb-4">Something went wrong. Please try again later or contact the administrator.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Return Home</a>
</div>
@endsection
