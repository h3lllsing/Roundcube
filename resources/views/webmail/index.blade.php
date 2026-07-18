@extends('layouts.admin')

@section('title', 'Webmail')

@section('content')
  <x-page-header title="Webmail" subtitle="Access your email accounts" />

  <x-card>
    <x-table>
      <thead>
        <tr>
          <th>Email</th>
          <th>Domain</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($accounts as $account)
          <tr>
            <td>{{ $account->email }}</td>
            <td>{{ $account->domain->name }}</td>
            <td><x-badge variant="{{ $account->status === 'active' ? 'success' : 'danger' }}">{{ $account->status }}</x-badge></td>
            <td>
              <x-button variant="primary" href="{{ route('webmail.open', $account) }}">
                Open Webmail
              </x-button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4">
              <x-empty-state message="No email accounts available." />
            </td>
          </tr>
        @endforelse
      </tbody>
    </x-table>
  </x-card>
@endsection
