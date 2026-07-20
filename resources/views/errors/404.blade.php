@extends('layouts.admin')
@section('title', 'Page Not Found')
@section('content')
<div class="text-center py-5">
    <h1 class="display-1 text-warning">404</h1>
    <h4 class="mb-4">Page Not Found</h4>
    <p class="text-muted mb-4">The page you are looking for does not exist or has been moved.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Return Home</a>
</div>
@endsection
