var cmdEl = document.getElementById('cmdPalette');
var cmdInput = document.getElementById('cmdInput');
var cmdResults = document.getElementById('cmdResults');
var cmdOverlay = document.getElementById('cmdOverlay');
var cmdIdx = -1;
var cmdFiltered = [];
var cmdAbort = null;
var cmdSearchData = [];

function openCmd() {
    cmdEl.classList.remove('hidden');
    cmdInput.value = '';
    cmdInput.focus();
    cmdIdx = -1;
    cmdSearchData = [];
    filterCmd('');
}
window.openCmd = openCmd;

function closeCmd() {
    cmdEl.classList.add('hidden');
    if (cmdAbort) { cmdAbort.abort(); cmdAbort = null; }
}
window.closeCmd = closeCmd;
document.getElementById('cmd-palette-trigger')?.addEventListener('click', openCmd);

function filterCmd(q) {
    q = q.toLowerCase().trim();
    cmdFiltered = cmdPages.filter(function(p) { return p.label.toLowerCase().includes(q); });
    cmdIdx = -1;

    if (q.length >= 2) {
        if (cmdAbort) { cmdAbort.abort(); }
        cmdAbort = new AbortController();
        fetch(window.cmdSearchUrl + '?q=' + encodeURIComponent(q) + '&limit=3', { signal: cmdAbort.signal })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                cmdAbort = null;
                cmdSearchData = data.data || [];
                renderCmd();
            })
            .catch(function(err) {
                if (err.name !== 'AbortError') {
                    cmdSearchData = [];
                    renderCmd();
                }
            });
    } else {
        cmdSearchData = [];
    }
    renderCmd();
}

function renderCmd() {
    var html = '';

    if (cmdSearchData.length > 0) {
        html += '<div class="px-3 py-2 text-[10px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Records</div>';
        cmdSearchData.forEach(function(group, gi) {
            group.items.forEach(function(item, ii) {
                var idx = cmdFiltered.length + html.split('data-cmd-idx').length - 1;
                html += '<a href="' + item.url + '" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors" data-cmd-idx="' + idx + '" role="option">' +
                    '<span class="text-[10px] font-medium px-1.5 py-0.5 rounded ' + {
                        'domains': 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300',
                        'hostings': 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300',
                        'vps': 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300',
                        'voip': 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300',
                        'assets': 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                        'tasks': 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                        'vault': 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                        'expiry_trackers': 'bg-lime-100 text-lime-700 dark:bg-lime-900/30 dark:text-lime-300',
                        'domain_emails': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                        'other_services': 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                        'service_providers': 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                        'users': 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300',
                        'notes': 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                    }[group.key] || 'bg-gray-100 text-gray-700 dark:bg-gray-800/50 dark:text-gray-300' + '">' + group.label + '</span>' +
                    '<span class="flex-1 truncate">' + item.title_highlighted + '</span>' +
                    (item.subtitle ? '<span class="text-xs text-gray-400 dark:text-gray-500 truncate ml-1">' + item.subtitle_highlighted + '</span>' : '') +
                '</a>';
            });
        });
    }

    if (cmdFiltered.length > 0) {
        if (html) {
            html += '<div class="border-t border-gray-100 dark:border-gray-700/50 my-1"></div>';
        }
        html += '<div class="px-3 py-2 text-[10px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Pages</div>';
        html += cmdFiltered.map(function(p, i) {
            return '<a href="'+p.url+'" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors" data-cmd-idx="' + (cmdFiltered.length + i) + '" role="option">'+
                '<svg class="w-4 h-4 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="'+p.icon+'"/></svg>'+
                '<span>'+p.label+'</span>'+
            '</a>';
        }).join('');
    }

    if (!html) {
        html = '<div class="px-3 py-8 text-center text-sm text-gray-400 dark:text-gray-500">' + (cmdInput.value.trim().length >= 2 ? 'No pages or records found' : 'Type to search pages and records') + '</div>';
    }

    cmdResults.innerHTML = html;
}

function getCmdItems() {
    return cmdResults.querySelectorAll('a');
}

document.addEventListener('keydown', function(e) {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        if (cmdEl.classList.contains('hidden')) openCmd(); else closeCmd();
    }
    if (e.key === 'Escape') closeCmd();
    if (!cmdEl.classList.contains('hidden') && cmdInput === document.activeElement) {
        var items = getCmdItems();
        if (e.key === 'ArrowDown') { e.preventDefault(); if (cmdIdx < items.length - 1) cmdIdx++; highlightCmd(items); }
        if (e.key === 'ArrowUp') { e.preventDefault(); if (cmdIdx > 0) cmdIdx--; else cmdIdx = -1; highlightCmd(items); }
        if (e.key === 'Enter' && cmdIdx > -1 && items[cmdIdx]) {
            window.location.href = items[cmdIdx].getAttribute('href');
        }
        if (e.key === 'Tab') {
            e.preventDefault();
            if (e.shiftKey) { if (cmdIdx > 0) cmdIdx--; else cmdIdx = items.length - 1; }
            else { if (cmdIdx < items.length - 1) cmdIdx++; else cmdIdx = 0; }
            highlightCmd(items);
        }
    }
});

function highlightCmd(items) {
    items.forEach(function(el, i) {
        el.classList.toggle('bg-indigo-50', i === cmdIdx);
        el.classList.toggle('dark:bg-indigo-900/20', i === cmdIdx);
        if (i === cmdIdx) el.focus();
    });
}

cmdInput?.addEventListener('input', function() { filterCmd(this.value); });
cmdOverlay?.addEventListener('click', closeCmd);
cmdResults?.addEventListener('click', function(e) {
    var link = e.target.closest('a');
    if (link) closeCmd();
});
cmdResults?.addEventListener('mouseover', function(e) {
    var link = e.target.closest('a');
    if (link) {
        var items = getCmdItems();
        items.forEach(function(el, i) {
            if (el === link) { cmdIdx = i; highlightCmd(items); }
        });
    }
});
