var sidebar = document.getElementById('sidebar');
var contents = document.getElementById('sidebarContents');
var title = document.getElementById('appTitle');
var overlay = document.getElementById('sidebarOverlay');

window.showMobileSidebar = function() {
    sidebar.classList.remove('-translate-x-full');
    if (overlay) overlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
};
window.hideMobileSidebar = function() {
    sidebar.classList.add('-translate-x-full');
    if (overlay) overlay.classList.add('hidden');
    document.body.style.overflow = '';
};
function toggleDesktopCollapse() {
    var collapsed = sidebar.classList.toggle('w-16');
    sidebar.classList.toggle('w-64');
    if (collapsed) {
        contents.classList.add('hidden');
        if (title) title.classList.add('hidden');
        localStorage.setItem('sidebarCollapsed', '1');
    } else {
        contents.classList.remove('hidden');
        if (title) title.classList.remove('hidden');
        localStorage.removeItem('sidebarCollapsed');
    }
}
document.getElementById('sidebarToggle')?.addEventListener('click', function (e) {
    e.stopPropagation();
    if (window.innerWidth < 1024) {
        window.showMobileSidebar();
    } else {
        toggleDesktopCollapse();
    }
});
document.getElementById('mobileMenuBtn')?.addEventListener('click', function (e) {
    e.stopPropagation();
    window.showMobileSidebar();
});
document.getElementById('sidebarDesktopToggle')?.addEventListener('click', toggleDesktopCollapse);
overlay?.addEventListener('click', window.hideMobileSidebar);
if (localStorage.getItem('sidebarCollapsed') === '1' && window.innerWidth >= 1024) {
    sidebar.classList.remove('w-64');
    sidebar.classList.add('w-16');
    contents.classList.add('hidden');
    if (title) title.classList.add('hidden');
}

window.showToast = function(msg, type) {
    var c = document.getElementById('toastContainer');
    if (!c) return;
    type = type || 'success';
    var icons = {success:'M5 13l4 4L19 7', error:'M6 18L18 6M6 6l12 12', info:'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', warning:'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'};
    var styles = {success:'border-l-emerald-500 shadow-emerald-500/10', error:'border-l-red-500 shadow-red-500/10', info:'border-l-sky-500 shadow-sky-500/10', warning:'border-l-amber-500 shadow-amber-500/10'};
    var bgColors = {success:'bg-emerald-100 text-emerald-600', error:'bg-red-100 text-red-600', info:'bg-sky-100 text-sky-600', warning:'bg-amber-100 text-amber-600'};
    var s = styles[type] || styles.success;
    var bg = bgColors[type] || bgColors.success;
    var icon = icons[type] || icons.success;
    var el = document.createElement('div');
    el.className = 'toast pointer-events-auto flex items-start gap-3.5 px-4 py-3.5 rounded-2xl glass-card border-l-4 ' + s + ' text-sm shadow-lg dark:shadow-indigo-500/5';
    el.innerHTML = '<div class="w-7 h-7 rounded-xl ' + bg + ' dark:bg-opacity-40 flex items-center justify-center shrink-0 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="' + icon + '"/></svg></div><span class="flex-1 pt-1 text-gray-800 dark:text-gray-200 font-medium">' + msg + '</span><button class="w-5 h-5 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 shrink-0 transition-all focus:outline-none focus:ring-2 focus:ring-gray-400/40">&times;</button>';
    el.querySelector('button')?.addEventListener('click', function() { el.remove(); });
    c.appendChild(el);
    setTimeout(function() { if (el.parentNode) { el.classList.add('hiding'); setTimeout(function() { if (el.parentNode) el.remove(); }, 300); } }, 5000);
};

window.startLoading = function(btn) {
    if (!btn || btn.classList.contains('loading')) return;
    btn.classList.add('loading', 'opacity-70', 'pointer-events-none');
    var s = document.createElement('span');
    s.className = 'inline-block w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin shrink-0';
    btn.prepend(s);
};
window.stopLoading = function(btn) {
    if (!btn) return;
    btn.classList.remove('loading', 'opacity-70', 'pointer-events-none');
    var spinners = btn.querySelectorAll('.animate-spin');
    spinners.forEach(function(sp) { sp.remove(); });
};

(function() {
    var groups = document.querySelectorAll('.nav-group');
    var defaults = { nav_infrastructure: true, nav_credentials: false, nav_operations: true, nav_administration: true, nav_reports: false, nav_account: true };

    groups.forEach(function(group) {
        var btn = group.querySelector('.nav-group-header');
        var content = group.querySelector('.nav-group-content');
        var key = btn.getAttribute('data-nav-key');
        var saved = localStorage.getItem(key);
        var expanded = saved !== null ? saved === 'true' : (defaults[key] !== false);
        var chevron = btn.querySelector('.nav-chevron');

        function apply(state) {
            expanded = state;
            btn.setAttribute('aria-expanded', state ? 'true' : 'false');
            content.style.maxHeight = state ? content.scrollHeight + 'px' : '0px';
            content.style.opacity = state ? '1' : '0';
            if (chevron) chevron.style.transform = state ? 'rotate(0deg)' : 'rotate(-90deg)';
        }

        apply(expanded);

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var newState = !expanded;
            localStorage.setItem(key, newState ? 'true' : 'false');
            apply(newState);
        });
    });
})();

(function() {
    var input = document.getElementById('sidebarSearch');
    if (!input) return;

    input.addEventListener('input', function() {
        var q = this.value.toLowerCase().trim();
        var groups = document.querySelectorAll('.nav-group');
        var items = document.querySelectorAll('#sidebarNav .nav-link');
        var hasResults = false;

        groups.forEach(function(group) {
            var btn = group.querySelector('.nav-group-header');
            var content = group.querySelector('.nav-group-content');
            var key = btn.getAttribute('data-nav-key');
            var groupItems = group.querySelectorAll('.nav-link');
            var groupMatch = false;

            groupItems.forEach(function(item) {
                var text = item.textContent.toLowerCase().trim();
                if (!q) {
                    item.style.display = '';
                    item.classList.remove('bg-indigo-50/50', 'dark:bg-indigo-900/10', 'rounded-lg');
                    groupMatch = true;
                } else if (text.includes(q)) {
                    item.style.display = '';
                    item.classList.add('bg-indigo-50/50', 'dark:bg-indigo-900/10', 'rounded-lg');
                    groupMatch = true;
                    hasResults = true;
                } else {
                    item.style.display = 'none';
                    item.classList.remove('bg-indigo-50/50', 'dark:bg-indigo-900/10', 'rounded-lg');
                }
            });

            if (q && groupMatch && key) {
                content.style.maxHeight = content.scrollHeight + 'px';
                content.style.opacity = '1';
                btn.setAttribute('aria-expanded', 'true');
            } else if (!q && key) {
                var saved = localStorage.getItem(key);
                var defaults = { nav_infrastructure: true, nav_credentials: false, nav_operations: true, nav_administration: true, nav_reports: false, nav_account: true };
                var expanded = saved !== null ? saved === 'true' : (defaults[key] !== false);
                content.style.maxHeight = expanded ? content.scrollHeight + 'px' : '0px';
                content.style.opacity = expanded ? '1' : '0';
                btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                var chevron = btn.querySelector('.nav-chevron');
                if (chevron) chevron.style.transform = expanded ? 'rotate(0deg)' : 'rotate(-90deg)';
            }
        });

        var directLinks = document.querySelectorAll('#sidebarNav > .nav-link');
        directLinks.forEach(function(link) {
            if (!q) {
                link.style.display = '';
            } else {
                var text = link.textContent.toLowerCase().trim();
                link.style.display = text.includes(q) ? '' : 'none';
                if (text.includes(q)) hasResults = true;
            }
        });
    });
})();

(function() {
    var sidebarContents = document.getElementById('sidebarContents');
    var saved = sessionStorage.getItem('sideScroll');
    if (saved && sidebarContents) {
        sidebarContents.scrollTop = parseInt(saved);
    }
    var nav = document.getElementById('sidebarNav');
    if (nav) {
        nav.addEventListener('click', function() {
            if (sidebarContents) sessionStorage.setItem('sideScroll', sidebarContents.scrollTop);
        });
    }
})();

document.querySelectorAll('a[href*="/export/"], a[href$="/export"]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        var overlay = document.getElementById('loadingOverlay');
        overlay.classList.remove('hidden');

        fetch(this.href)
            .then(function(r) {
                if (!r.ok) throw new Error('Export failed');
                var disposition = r.headers.get('Content-Disposition') || '';
                var match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                var filename = match ? match[1].replace(/['"]/g, '') : 'export.csv';
                return r.blob().then(function(blob) { return { blob: blob, filename: filename }; });
            })
            .then(function(data) {
                var url = URL.createObjectURL(data.blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = data.filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            })
            .catch(function(err) {
                console.error('Export error:', err);
            })
            .finally(function() {
                overlay.classList.add('hidden');
            });
    });
});

document.querySelectorAll('form').forEach(function(form) {
    var exclude = form.closest('[data-no-loading]') || form.querySelector('[name="q"]');
    if (exclude) return;
    form.addEventListener('submit', function() {
        var btn = this.querySelector('button[type="submit"]');
        if (btn && !btn.classList.contains('loading')) {
            window.startLoading(btn);
        }
    });
});

(function() {
    var countEls = document.querySelectorAll('[data-count]');
    if (countEls.length && 'IntersectionObserver' in window) {
        var obs = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                obs.unobserve(el);
                var target = parseFloat(el.getAttribute('data-count'));
                var duration = Math.min(1200, Math.max(400, target * 8));
                var start = performance.now();
                function tick(now) {
                    var pct = Math.min(1, (now - start) / duration);
                    var eased = 1 - Math.pow(1 - pct, 3);
                    el.textContent = Math.floor(eased * target);
                    if (pct < 1) requestAnimationFrame(tick);
                    else el.textContent = target;
                }
                requestAnimationFrame(tick);
            });
        }, {threshold:0.3});
        countEls.forEach(function(el) { obs.observe(el); });
    } else {
        countEls.forEach(function(el) { el.textContent = el.getAttribute('data-count'); });
    }
})();

document.addEventListener('change', function(e) {
    var target = e.target;
    if (target.matches('[data-bulk-select-all]')) {
        var selector = target.getAttribute('data-bulk-selector') || '.bulk-item';
        var items;
        if (selector === '.bulk-item') {
            var table = target.closest('table');
            items = table ? table.querySelectorAll(selector) : [];
        } else {
            items = document.querySelectorAll(selector);
        }
        for (var i = 0; i < items.length; i++) { items[i].checked = target.checked; }
    }
    if (target.matches('select[name="status"]') && target.form && target.form.method.toLowerCase() === 'get') {
        target.form.submit();
    }
});

document.addEventListener('click', function(e) {
    if (e.target.matches('[data-bulk-action]')) {
        document.getElementById('bulkForm').action = e.target.getAttribute('data-bulk-action');
    }
});

document.addEventListener('click', function(e) {
    var target = e.target.closest('[data-vault-action]');
    if (!target) return;
    var action = target.getAttribute('data-vault-action');
    if (action === 'hide') {
        document.getElementById('passwordDisplay').textContent = '\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022';
        target.style.display = 'none';
        var revealForm = document.querySelector('.reveal-form');
        if (revealForm) revealForm.style.display = 'inline';
        var copyBtn = document.getElementById('copyBtn');
        if (copyBtn) copyBtn.style.display = 'none';
    } else if (action === 'copy') {
        navigator.clipboard.writeText(document.getElementById('passwordDisplay').textContent).then(function() {
            target.textContent = 'Copied!';
            setTimeout(function() { target.textContent = 'Copy'; }, 1500);
        });
    }
});

document.addEventListener('click', function(e) {
    if (e.target.matches('[data-close-preview]') || e.target.closest('[data-close-preview]')) {
        var wrapper = document.getElementById('previewModalWrapper');
        if (wrapper) { wrapper.remove(); }
        document.body.style.overflow = '';
    }
});

document.getElementById('sidebarNav')?.addEventListener('click', function(e) {
    var link = e.target.closest('a.nav-link');
    if (link && window.innerWidth < 1024) {
        window.hideMobileSidebar();
    }
});
