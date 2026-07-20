@extends('layouts.admin')
@section('title', 'Queue Monitor')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Queue Monitor</h1>
    <div>
        <span class="badge bg-info fs-6 me-2">Pending: {{ $pending }}</span>
        <span class="badge bg-danger fs-6">Failed: {{ $failed->total() }}</span>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Failed Jobs</span>
        @if($failed->total() > 0)
            <form method="POST" action="{{ route('queue-monitor.retry-all') }}" class="d-inline">
                @csrf
                <button class="btn btn-warning btn-sm" onclick="return confirm('Retry all failed jobs?')">Retry All</button>
            </form>
        @endif
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <input type="text" name="search" class="form-control" placeholder="Search failed job payload..." value="{{ request('search') }}">
        </form>

        @if($failed->isEmpty())
            <p class="text-muted mb-0">No failed jobs.</p>
        @else
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Connection</th>
                            <th>Queue</th>
                            <th>Failed At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($failed as $job)
                            <tr>
                                <td>{{ $job->id }}</td>
                                <td>{{ $job->connection }}</td>
                                <td>{{ $job->queue }}</td>
                                <td>{{ $job->failed_at }}</td>
                                <td>
                                    <form method="POST" action="{{ route('queue-monitor.retry', $job->id) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success" onclick="return confirm('Retry this job?')">Retry</button>
                                    </form>
                                    <form method="POST" action="{{ route('queue-monitor.destroy', $job->id) }}" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Remove this failed job?')">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $failed->links() }}
        @endif
    </div>
</div>
@endsection
