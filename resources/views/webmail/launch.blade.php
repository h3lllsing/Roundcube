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
    .notif-bell-btn {
        position:relative; padding:4px 6px; border-radius:6px;
        border:1px solid #d1d5db; font-size:13px;
        background:white; color:#374151; cursor:pointer;
        line-height:1; display:flex; align-items:center;
    }
    .dark .notif-bell-btn {
        background:#1f2937; color:#d1d5db;
        border-color:#4b5563;
    }
    .notif-bell-btn:hover { background:#f3f4f6; }
    .dark .notif-bell-btn:hover { background:#374151; }
    .notif-bell-btn svg { width:16px; height:16px; }
    .notif-badge {
        position:absolute; top:-4px; right:-4px;
        min-width:16px; height:16px;
        background:linear-gradient(135deg,#ef4444,#e11d48);
        color:white; font-size:9px; font-weight:700;
        border-radius:8px; display:flex; align-items:center;
        justify-content:center; padding:0 3px;
        box-shadow:0 1px 2px rgba(0,0,0,.15);
        border:1.5px solid white;
    }
    .dark .notif-badge { border-color:#1f2937; }
    .notif-dropdown {
        position:fixed; top:36px; right:12px; z-index:10001;
        width:360px; max-height:420px; overflow-y:auto;
        background:white; border-radius:10px;
        border:1px solid #e5e7eb; box-shadow:0 10px 30px rgba(0,0,0,.12);
        display:none;
    }
    .dark .notif-dropdown {
        background:#1f2937; border-color:#374151;
        box-shadow:0 10px 30px rgba(0,0,0,.4);
    }
    .notif-dropdown.open { display:block; }
    .notif-dropdown-header {
        padding:10px 14px; border-bottom:1px solid #e5e7eb;
        display:flex; align-items:center; justify-content:space-between;
        font-size:12px; font-weight:600; color:#374151;
    }
    .dark .notif-dropdown-header {
        border-color:#374151; color:#d1d5db;
    }
    .notif-dropdown-header a {
        color:#6366f1; text-decoration:none; font-weight:500;
    }
    .notif-dropdown-header a:hover { text-decoration:underline; }
    .notif-item {
        display:block; padding:10px 14px;
        border-bottom:1px solid #f3f4f6;
        text-decoration:none; color:inherit;
        transition:background .15s;
    }
    .notif-item:hover { background:#f9fafb; }
    .dark .notif-item:hover { background:#374151; }
    .dark .notif-item { border-color:#374151; }
    .notif-item.unread { background:#eef2ff; }
    .dark .notif-item.unread { background:rgba(99,102,241,.1); }
    .notif-item-subject {
        font-size:13px; font-weight:500; color:#111827;
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }
    .dark .notif-item-subject { color:#f3f4f6; }
    .notif-item-from {
        font-size:11px; color:#6b7280; margin-top:1px;
    }
    .notif-item-time {
        font-size:10px; color:#9ca3af; margin-top:2px;
    }
    .notif-empty {
        padding:24px; text-align:center; font-size:12px; color:#9ca3af;
    }
    .notif-loading {
        padding:16px; text-align:center; font-size:11px; color:#9ca3af;
    }
</style>
@endpush

@section('content')
<div class="webmail-switcher" id="webmailSwitcher">
    <button class="sidebar-toggle-btn" id="sidebarToggle" title="Toggle sidebar" aria-label="Toggle sidebar">☰</button>
    <span class="text-gray-500 dark:text-gray-400 text-xs font-medium">Switch:</span>
    <select id="accountSelect" onchange="switchAccount(this.value)" aria-label="Switch email account">
        @foreach($accounts as $acc)
        <option value="{{ $acc->id }}" {{ $acc->id == $currentAccount->id ? 'selected' : '' }}>
            {{ $acc->email }}
        </option>
        @endforeach
    </select>
    <div style="margin-left:auto;display:flex;align-items:center;gap:6px">
        <button class="notif-bell-btn" id="notifBellBtn" onclick="toggleNotifDropdown()" title="Notifications" aria-label="Notifications">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="notif-badge" id="notifBadge" style="display:none">0</span>
        </button>
        <button class="close-btn" onclick="closeWebmail()" aria-label="Close webmail">✕ Close</button>
    </div>
</div>

<div class="notif-dropdown" id="notifDropdown">
    <div class="notif-dropdown-header">
        <span>Notifications</span>
        <a href="{{ route('notifications.index') }}" target="_blank">View all</a>
    </div>
    <div id="notifList">
        <div class="notif-loading">Loading...</div>
    </div>
</div>

<div class="webmail-wrapper" id="webmailWrapper">
    <iframe name="webmailIframe" src="about:blank" allow="fullscreen" title="Webmail"></iframe>
</div>

<form id="webmailForm" action="{{ url('/') }}/webmail/plugins/roundcube-portal-auth/receive.php" method="POST" target="webmailIframe">
    <input type="hidden" name="t" value="{{ $token }}">
</form>

<script>
var wrapper = document.getElementById('webmailWrapper');
var switcher = document.getElementById('webmailSwitcher');
var notifBadge = document.getElementById('notifBadge');
var notifList = document.getElementById('notifList');
var notifDropdown = document.getElementById('notifDropdown');
var pollInterval = null;
var dropdownOpen = false;

document.getElementById('webmailForm').submit();

document.getElementById('sidebarToggle').addEventListener('click', function() {
    var isFull = wrapper.style.left === '' || wrapper.style.left === '0px';
    if (isFull && window.innerWidth >= 1024) {
        wrapper.style.left = '16rem';
        switcher.style.left = '16rem';
        notifDropdown.style.left = 'calc(16rem + 12px)';
    } else {
        wrapper.style.left = '0';
        switcher.style.left = '0';
        notifDropdown.style.left = '12px';
    }
});

function switchAccount(accountId) {
    window.location.href = '{{ url('web-mail/open') }}/' + accountId;
}

function closeWebmail() {
    window.location.href = '{{ route('webmail.index') }}';
}

function toggleNotifDropdown() {
    dropdownOpen = !dropdownOpen;
    notifDropdown.classList.toggle('open', dropdownOpen);
    if (dropdownOpen) fetchNotifications();
}

document.addEventListener('click', function(e) {
    if (dropdownOpen && !e.target.closest('#notifDropdown') && !e.target.closest('#notifBellBtn')) {
        dropdownOpen = false;
        notifDropdown.classList.remove('open');
    }
});

function fetchNotifications() {
    fetch('{{ route('notifications.poll') }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var count = data.unread_count || 0;
        if (count > 0) {
            notifBadge.style.display = 'flex';
            notifBadge.textContent = count > 9 ? '9+' : count;
        } else {
            notifBadge.style.display = 'none';
        }

        if (!dropdownOpen) return;

        var html = '';
        if (data.notifications.length === 0) {
            html = '<div class="notif-empty">No notifications yet.</div>';
        } else {
            data.notifications.forEach(function(n) {
                var subject = n.subject || '(no subject)';
                var from = n.from || 'Unknown';
                var time = n.created_at || '';
                var cls = n.read ? '' : ' unread';
                var accountId = n.account_id || '';
                var onclick = accountId ? 'switchAccount(' + accountId + ')' : '';
                var href = accountId ? '{{ url('web-mail/open') }}/' + accountId : '#';
                html += '<a href="' + href + '" class="notif-item' + cls + '">';
                html += '<div class="notif-item-subject">' + escapeHtml(subject) + '</div>';
                html += '<div class="notif-item-from">' + escapeHtml(from) + ' &middot; ' + escapeHtml(n.email) + '</div>';
                html += '<div class="notif-item-time">' + escapeHtml(time) + '</div>';
                html += '</a>';
            });
        }
        notifList.innerHTML = html;
    })
    .catch(function() {
        if (dropdownOpen) {
            notifList.innerHTML = '<div class="notif-empty">Failed to load.</div>';
        }
    });
}

function escapeHtml(str) {
    if (!str) return '';
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

function startPolling() {
    fetchNotifications();
    pollInterval = setInterval(fetchNotifications, 30000);
}

function stopPolling() {
    if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
}

startPolling();
</script>
@endsection