@extends('layouts.admin')

@section('title', 'Webmail')

@push('styles')
<style>
    .webmail-wrapper { position:fixed; inset:0; top:0; left:0; z-index:9999; background:#fff; transition:left .25s ease; }
    .webmail-wrapper iframe { width:100%; height:100%; border:none; }
    .webmail-switcher {
        position:fixed; top:0; left:0; right:0; z-index:10000;
        background:rgba(255,255,255,.95); backdrop-filter:blur(8px);
        border-bottom:1px solid #e5e7eb;
        display:flex; align-items:center; gap:8px;
        padding:6px 12px; font-size:13px;
        transition:left .25s ease;
    }
    .dark .webmail-switcher {
        background:rgba(17,24,39,.95);
        border-bottom:1px solid #374151;
    }
    .webmail-switcher select {
        flex:1; max-width:320px;
        padding:4px 8px; border-radius:6px;
        border:1px solid #d1d5db; font-size:13px;
        background:white; color:#111827;
    }
    .dark .webmail-switcher select {
        background:#1f2937; color:#f3f4f6;
        border-color:#4b5563;
    }
    .webmail-switcher .close-btn {
        padding:4px 8px; border-radius:6px;
        border:1px solid #d1d5db; font-size:13px;
        background:white; color:#374151; cursor:pointer;
    }
    .dark .webmail-switcher .close-btn {
        background:#1f2937; color:#d1d5db;
        border-color:#4b5563;
    }
    .webmail-switcher .close-btn:hover { background:#f3f4f6; }
    .dark .webmail-switcher .close-btn:hover { background:#374151; }
    .webmail-wrapper { top:36px; }
    .sidebar-toggle-btn {
        padding:4px 8px; border-radius:6px;
        border:1px solid #d1d5db; font-size:13px;
        background:white; color:#374151; cursor:pointer;
        line-height:1;
    }
    .dark .sidebar-toggle-btn {
        background:#1f2937; color:#d1d5db;
        border-color:#4b5563;
    }
    .sidebar-toggle-btn:hover { background:#f3f4f6; }
    .dark .sidebar-toggle-btn:hover { background:#374151; }
</style>
@endpush

@section('content')
<div class="webmail-switcher" id="webmailSwitcher">
    <button class="sidebar-toggle-btn" id="sidebarToggle" title="Toggle sidebar">☰</button>
    <span class="text-gray-500 dark:text-gray-400 text-xs font-medium">Switch:</span>
    <select id="accountSelect" onchange="switchAccount(this.value)">
        @foreach($accounts as $acc)
        <option value="{{ $acc->id }}" {{ $acc->id == $currentAccount->id ? 'selected' : '' }}>
            {{ $acc->email }}
        </option>
        @endforeach
    </select>
    <button class="close-btn" onclick="closeWebmail()">✕ Close</button>
</div>

<div class="webmail-wrapper" id="webmailWrapper">
    <iframe name="webmailIframe" src="about:blank" allow="fullscreen"></iframe>
</div>

<form id="webmailForm" action="{{ url('/') }}/webmail/plugins/roundcube_portal_auth/receive.php" method="POST" target="webmailIframe">
    <input type="hidden" name="t" value="{{ $token }}">
</form>

<script>
var wrapper = document.getElementById('webmailWrapper');
var switcher = document.getElementById('webmailSwitcher');

document.getElementById('webmailForm').submit();

document.getElementById('sidebarToggle').addEventListener('click', function() {
    var isFull = wrapper.style.left === '' || wrapper.style.left === '0px';
    if (isFull && window.innerWidth >= 1024) {
        wrapper.style.left = '16rem';
        switcher.style.left = '16rem';
    } else {
        wrapper.style.left = '0';
        switcher.style.left = '0';
    }
});

function switchAccount(accountId) {
    window.location.href = '{{ url('web-mail/open') }}/' + accountId;
}

function closeWebmail() {
    window.location.href = '{{ route('webmail.index') }}';
}
</script>
@endsection