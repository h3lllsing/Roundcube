@extends('layouts.admin')
@section('title', 'Forbidden')
@section('content')
<div class="text-center py-5">
    <h1 class="display-1 text-danger">403</h1>
    <h4 class="mb-4">Access Denied</h4>
    <p class="text-muted mb-4">You do not have permission to access this resource.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Return Home</a>
</div>
@endsection
