@extends('layouts.admin')

@section('title', 'Help Center')

@push('styles')
@vite('resources/css/help-center.css')
@endpush

@section('content')
<div class="max-w-7xl mx-auto help-center" x-data="helpCenter({{ json_encode($guideToc) }})">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Help Center</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Documentation and guides for using the OpsPilot portal.</p>
        </div>
        <div class="relative w-64 max-w-full">
            <input type="text" x-model="searchQuery" @input="doSearch"
                placeholder="Search guides..." aria-label="Search guides"
                class="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 outline-none transition-shadow">
            <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <div x-show="searchResults.length > 0" x-cloak
                class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50 max-h-80 overflow-y-auto">
                <template x-for="r in searchResults" :key="r.file + ':' + r.line">
                    <a href="#" @click.prevent="openDoc(r.slug || r.file)"
                        class="block px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700/50 last:border-0">
                        <span class="font-medium text-gray-900 dark:text-white" x-text="r.label + ' — ' + r.heading"></span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="r.snippet"></span>
                    </a>
                </template>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <aside class="lg:col-span-1">
            <nav class="lg:sticky lg:top-24 space-y-0.5 help-sidenav" aria-label="Help navigation">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2 px-3">Guides</p>
                @foreach($sidebarLinks as $slug => $link)
                <a href="#"
                    class="help-nav-link flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-200"
                    data-slug="{{ $slug }}"
                    @click.prevent="openDoc('{{ $slug }}')"
                    :class="activeDoc === '{{ $slug }}' ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200'">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"/>
                    </svg>
                    {{ $link['label'] }}
                </a>
                @endforeach

                @if($showDeveloperDocs)
                <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2 px-3">Module Reference</p>
                    <a href="#" class="help-nav-link flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200" data-slug="calendar" @click.prevent="openDoc('calendar')">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Calendar
                    </a>
                    <a href="#" class="help-nav-link flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200" data-slug="search" @click.prevent="openDoc('search')">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Global Search
                    </a>
                    <a href="#" class="help-nav-link flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200" data-slug="profile" @click.prevent="openDoc('profile')">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        My Profile & Access
                    </a>
                </div>
                @endif

                @if($showDeveloperDocs)
                <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2 px-3">Developer Docs</p>
                    <a href="#" class="help-nav-link flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200" data-slug="architecture" @click.prevent="openDoc('architecture')">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        Architecture Overview
                    </a>
                    <a href="#" class="help-nav-link flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200" data-slug="developer-rbac" @click.prevent="openDoc('developer-rbac')">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Developer RBAC Reference
                    </a>
                    <a href="#" class="help-nav-link flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200" data-slug="disaster-recovery" @click.prevent="openDoc('disaster-recovery')">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Disaster Recovery
                    </a>
                </div>
                @endif
            </nav>
        </aside>

        <main class="lg:col-span-3 min-h-[60vh]">
            <div id="helpWelcome" x-show="!activeDoc" class="space-y-6">
                <div class="bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
                    <div class="flex items-center gap-4 mb-5">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-lg font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Welcome back, {{ auth()->user()->name }}</h2>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                @if($roleSlug === 'super-admin') bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300
                                @elseif($roleSlug === 'admin') bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300
                                @elseif($roleSlug === 'it-support') bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300
                                @else bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 @endif">
                                {{ $roleLabel }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Today's Workflow</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($todayWorkflow as $item)
                            <div class="flex items-center gap-2.5 text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/30 rounded-lg px-3 py-2">
                                <svg class="w-3.5 h-3.5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                @if($item['route'])
                                <a href="{{ route($item['route']) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 font-medium">{{ $item['label'] }}</a>
                                @else
                                <span>{{ $item['label'] }}</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Quick Links</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($quickLinks as $link)
                            <a href="{{ $link['route'] ? route($link['route']) : '#' }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors">
                                {{ $link['label'] }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if($guideHtml)
                <div class="help-content">
                    <div class="flex items-center gap-3 mb-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $roleLabel }} Guide</h2>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold
                            @if($roleSlug === 'super-admin') bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300
                            @elseif($roleSlug === 'admin') bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300
                            @elseif($roleSlug === 'it-support') bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300
                            @else bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 @endif">
                            {{ $roleLabel }}
                        </span>
                    </div>
                    {!! $guideHtml !!}
                </div>
                @endif
            </div>

            <div id="helpDocView" x-show="activeDoc" x-cloak class="space-y-4">
                <button @click="activeDoc = ''; currentToc = _initialToc.slice(); if (_tocObserver) { _tocObserver.disconnect(); _tocObserver = null; } activeTocId = null; tocCollapsed = true" class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Help Center
                </button>
                <div class="bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <div id="helpDocHeader" class="px-6 pt-6 pb-2 border-b border-gray-100 dark:border-gray-700/50 hidden">
                        <h2 id="helpDocTitle" class="text-xl font-bold text-gray-900 dark:text-white"></h2>
                    </div>
                    <div id="helpDocContent" class="help-content p-6">
                        <div class="flex items-center justify-center py-16">
                            <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <aside class="lg:col-span-1">
            <div x-show="currentToc.length > 0" x-cloak class="lg:sticky lg:top-28 toc-sidebar">
                <div class="flex items-center justify-between mb-3 px-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">On this page</p>
                    <button x-show="currentToc.length > 8" @click="tocCollapsed = !tocCollapsed"
                        class="text-[11px] font-medium text-indigo-500 hover:text-indigo-600 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/40 rounded px-1.5 py-0.5"
                        x-text="tocCollapsed ? 'Show all' : 'Collapse'"
                        :aria-expanded="!tocCollapsed" aria-label="Toggle table of contents"></button>
                </div>
                <nav aria-label="Table of contents" role="navigation">
                    <template x-for="(h, i) in tocCollapsed ? currentToc.slice(0, 8) : currentToc" :key="h.id">
                        <a :href="'#' + h.id"
                            :style="'padding-left:' + ((h.level - 1) * 12 + 12) + 'px'"
                            :class="'toc-link block text-sm rounded transition-all duration-200 ' + (activeTocId === h.id ? 'toc-link-active' : 'toc-link-inactive')"
                            :aria-current="activeTocId === h.id ? 'true' : undefined"
                            tabindex="0"
                            role="link"
                            x-text="h.text"
                            @click.prevent="scrollToHeading(h.id)"
                            @keydown.enter.prevent="scrollToHeading(h.id)">
                        </a>
                    </template>
                </nav>
                <div x-show="tocCollapsed && currentToc.length > 8" class="mt-2 px-1">
                    <button @click="tocCollapsed = false"
                        class="text-xs font-medium text-indigo-500 hover:text-indigo-600 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/40 rounded px-1.5 py-0.5">
                        + <span x-text="currentToc.length - 8"></span> more
                    </button>
                </div>
            </div>
            <div x-show="currentToc.length === 0" x-cloak class="lg:sticky lg:top-28 toc-sidebar">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 px-1">On this page</p>
                <p class="text-xs text-gray-400 dark:text-gray-600 mt-3 px-1">No sections</p>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
function helpCenter(initialToc) {
    initialToc = initialToc || [];
    console.log('[TOC AUDIT] Initial page load — Backend TOC count: ' + initialToc.length + ' | Frontend TOC count: 0 (computed at render)');
    return {
        activeDoc: '',
        currentToc: initialToc,
        _initialToc: initialToc.slice(),
        activeTocId: null,
        tocCollapsed: true,
        searchQuery: '',
        searchResults: [],
        _searchTimer: null,
        _tocObserver: null,

        init() {
            var self = this;
            this.$nextTick(function() { self.initScrollSpy(); });
        },

        openDoc(slug) {
            this.activeDoc = slug;
            this.activeTocId = null;
            this.tocCollapsed = true;
            if (this._tocObserver) { this._tocObserver.disconnect(); this._tocObserver = null; }
            var self = this;
            var content = document.getElementById('helpDocContent');
            var header = document.getElementById('helpDocHeader');
            var title = document.getElementById('helpDocTitle');
            if (content) content.innerHTML = '<div class="flex items-center justify-center py-16"><svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
            fetch((window.HelpCenterConfig?.baseUrl || '') + '/help/' + slug)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (content) content.innerHTML = data.html || '<p class="text-gray-500">No content available.</p>';
                    if (title) title.textContent = data.title || '';
                    if (header) header.classList.remove('hidden');
                    self.currentToc = data.toc || [];
                    console.log('[TOC AUDIT] Backend TOC count: ' + (data.toc ? data.toc.length : 0) + ' | Frontend TOC count: ' + self.currentToc.length + ' | Displayed TOC count: ' + (self.tocCollapsed ? Math.min(self.currentToc.length, 8) : self.currentToc.length));
                    if (content) {
                        content.querySelectorAll('a[href^="#"]').forEach(function(a) {
                            a.addEventListener('click', function(e) {
                                e.preventDefault();
                                var id = this.getAttribute('href').slice(1);
                                var el = document.getElementById(id);
                                if (el) {
                                    self.activeTocId = id;
                                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                }
                            });
                        });
                    }
                    self.$nextTick(function() { self.initScrollSpy(); });
                })
                .catch(function() {
                    if (content) content.innerHTML = '<p class="text-red-500">Failed to load document.</p>';
                    self.currentToc = [];
                    self.activeTocId = null;
                });
        },

        initScrollSpy() {
            if (this._tocObserver) this._tocObserver.disconnect();
            var self = this;
            var headings = document.querySelectorAll('#helpDocContent h2[id], #helpDocContent h3[id], #helpWelcome .help-content h2[id], #helpWelcome .help-content h3[id]');
            if (headings.length === 0) return;
            this._tocObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        self.activeTocId = entry.target.getAttribute('id');
                    }
                });
            }, { rootMargin: '-88px 0px -65% 0px', threshold: 0 });
            headings.forEach(function(h) { self._tocObserver.observe(h); });
            // Set initial active to first heading
            this.activeTocId = headings[0].getAttribute('id');
        },

        doSearch() {
            var self = this;
            if (self._searchTimer) clearTimeout(self._searchTimer);
            self._searchTimer = setTimeout(function() {
                var q = self.searchQuery.trim();
                if (q.length < 2) { self.searchResults = []; return; }
                fetch((window.HelpCenterConfig?.baseUrl || '') + '/help/search?q=' + encodeURIComponent(q))
                    .then(function(r) { return r.json(); })
                    .then(function(data) { self.searchResults = data.results || []; })
                    .catch(function() { self.searchResults = []; });
            }, 300);
        },

        scrollToHeading(id) {
            var el = document.getElementById(id);
            if (el) {
                this.activeTocId = id;
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }
}
</script>
@endpush
