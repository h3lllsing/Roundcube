(function() {
    'use strict';

    let slideOver = null;
    let slideOverBackdrop = null;
    let slideOverContent = null;
    let slideOverTitle = null;
    let slideOverClose = null;
    let previousFocus = null;
    let searchTimeout = null;

    var baseUrl = (document.querySelector('meta[name="base-url"]')?.getAttribute('content')) || '';

    function init() {
        createSlideOver();
        bindGlobal();
    }

    function createSlideOver() {
        if (document.getElementById('helpSlideOver')) return;

        slideOver = document.createElement('div');
        slideOver.id = 'helpSlideOver';
        slideOver.className = 'fixed inset-0 z-[200] hidden';
        slideOver.setAttribute('role', 'dialog');
        slideOver.setAttribute('aria-modal', 'true');
        slideOver.setAttribute('aria-label', 'Help Center');
        slideOver.innerHTML =
            '<div id="helpBackdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity duration-300"></div>' +
            '<div id="helpPanel" class="absolute top-0 right-0 h-full w-full max-w-lg bg-white dark:bg-gray-900 shadow-2xl border-l border-gray-200 dark:border-gray-700 flex flex-col translate-x-full transition-transform duration-300 ease-out">' +
                '<div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">' +
                    '<h2 id="helpPanelTitle" class="text-lg font-semibold text-gray-900 dark:text-white truncate">Help Center</h2>' +
                    '<button id="helpCloseBtn" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/40" aria-label="Close help panel">' +
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
                    '</button>' +
                '</div>' +
                '<div id="helpPanelContent" class="flex-1 overflow-y-auto p-4 md:p-6 help-content"></div>' +
            '</div>';

        document.body.appendChild(slideOver);

        slideOverContent = document.getElementById('helpPanelContent');
        slideOverTitle = document.getElementById('helpPanelTitle');
        slideOverClose = document.getElementById('helpCloseBtn');
        slideOverBackdrop = document.getElementById('helpBackdrop');

        slideOverClose.addEventListener('click', closeSlideOver);
        slideOverBackdrop.addEventListener('click', closeSlideOver);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !slideOver.classList.contains('hidden')) {
                closeSlideOver();
            }
        });

        slideOver.addEventListener('keydown', function(e) {
            if (e.key !== 'Tab') return;
            var focusable = slideOver.querySelectorAll(
                'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
            );
            if (focusable.length === 0) return;
            var first = focusable[0];
            var last = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        });
    }

    function openSlideOver(slug, title) {
        previousFocus = document.activeElement;
        slideOver.classList.remove('hidden');
        slideOverTitle.textContent = title || 'Help Center';
        slideOverContent.innerHTML =
            '<div class="flex items-center justify-center py-16"><svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">' +
            '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>' +
            '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>' +
            '</svg></div>';

        var panel = document.getElementById('helpPanel');
        if (panel) {
            requestAnimationFrame(function() {
                panel.classList.remove('translate-x-full');
            });
        }

        var url = baseUrl + '/help/' + slug;
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.title) {
                    slideOverTitle.textContent = data.title;
                }
                slideOverContent.innerHTML = data.html || '<p class="text-gray-500">No content available.</p>';
                slideOverContent.querySelectorAll('a').forEach(function(a) {
                    if (a.getAttribute('href') && !a.getAttribute('href').startsWith('http') && !a.getAttribute('href').startsWith('#')) {
                        a.setAttribute('target', '_blank');
                        a.setAttribute('rel', 'noopener');
                    }
                });
            })
            .catch(function() {
                slideOverContent.innerHTML = '<p class="text-red-500">Failed to load help content. Please try again.</p>';
            });

        document.body.style.overflow = 'hidden';
        setTimeout(function() { slideOverClose.focus(); }, 100);
    }

    function openModuleHelp(module, label) {
        var title = label || module.charAt(0).toUpperCase() + module.slice(1) + ' Help';
        previousFocus = document.activeElement;
        slideOver.classList.remove('hidden');
        slideOverTitle.textContent = title;
        slideOverContent.innerHTML =
            '<div class="flex items-center justify-center py-16"><svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">' +
            '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>' +
            '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>' +
            '</svg></div>';

        var panel = document.getElementById('helpPanel');
        if (panel) {
            requestAnimationFrame(function() {
                panel.classList.remove('translate-x-full');
            });
        }

        var url = baseUrl + '/help/module/' + module;
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.title) {
                    slideOverTitle.textContent = data.title;
                }
                slideOverContent.innerHTML = data.html || '<p class="text-gray-500">No help available for this module.</p>';
                slideOverContent.querySelectorAll('a').forEach(function(a) {
                    if (a.getAttribute('href') && !a.getAttribute('href').startsWith('http') && !a.getAttribute('href').startsWith('#')) {
                        a.setAttribute('target', '_blank');
                        a.setAttribute('rel', 'noopener');
                    }
                });
            })
            .catch(function() {
                slideOverContent.innerHTML = '<p class="text-red-500">Failed to load help content. Please try again.</p>';
            });

        document.body.style.overflow = 'hidden';
        setTimeout(function() { slideOverClose.focus(); }, 100);
    }

    function closeSlideOver() {
        var panel = document.getElementById('helpPanel');
        if (panel) {
            panel.classList.add('translate-x-full');
        }
        setTimeout(function() {
            slideOver.classList.add('hidden');
            document.body.style.overflow = '';
            if (previousFocus) {
                previousFocus.focus();
                previousFocus = null;
            }
        }, 250);
    }

    function bindGlobal() {
        document.addEventListener('click', function(e) {
            var trigger = e.target.closest('.help-trigger[data-module]');
            if (trigger) {
                e.preventDefault();
                var module = trigger.getAttribute('data-module');
                var label = trigger.getAttribute('data-label') || '';
                openModuleHelp(module, label);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.HelpCenter = {
        open: openSlideOver,
        openModule: openModuleHelp,
        close: closeSlideOver
    };
})();
