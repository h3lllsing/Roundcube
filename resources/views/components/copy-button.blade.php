@props([
    'text' => null,
    'passwordRoute' => null,
    'title' => 'Copy',
    'class' => '',
])

@php
$attrs = '';
if ($text !== null) {
    $attrs = 'data-copy-text="' . e($text) . '"';
} elseif ($passwordRoute !== null) {
    $attrs = 'data-copy-pwd="' . e($passwordRoute) . '"';
}
@endphp

<button type="button" {!! $attrs !!} class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors {{ $class }}" title="{{ $title }}">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
</button>

@once('copy-button-js')
@push('scripts')
<script>
(function() {
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text);
        var orig = btn.innerHTML;
        btn.innerHTML = '<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
        btn.classList.add('pointer-events-none');
        setTimeout(function() {
            btn.innerHTML = orig;
            btn.classList.remove('pointer-events-none');
        }, 2000);
    }

    document.querySelectorAll('[data-copy-text]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            copyToClipboard(this.dataset.copyText, this);
        });
    });

    document.querySelectorAll('[data-copy-pwd]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var el = this;
            var url = el.dataset.copyPwd;
            var orig = el.innerHTML;
            fetch(url).then(function(r) { return r.json(); }).then(function(d) {
                var pwd = d.password || d.extension_password;
                if (pwd) {
                    navigator.clipboard.writeText(pwd);
                    el.innerHTML = '<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                    el.classList.add('pointer-events-none');
                    setTimeout(function() {
                        el.innerHTML = orig;
                        el.classList.remove('pointer-events-none');
                    }, 2000);
                }
            }).catch(function() { alert('Failed to fetch password.'); });
        });
    });
})();
</script>
@endpush
@endonce
