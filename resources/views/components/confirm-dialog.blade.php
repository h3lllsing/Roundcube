@props(['id' => 'confirmModal'])

<div x-data="confirmDialog()"
     x-init="init()"
     id="{{ $id }}"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     role="alertdialog"
     aria-modal="true"
     aria-labelledby="confirmTitle"
     aria-describedby="confirmMsg">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="cancel()"></div>
    <div class="scale-in relative bg-white dark:bg-black rounded-2xl shadow-2xl max-w-sm w-full p-6">
        <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 id="confirmTitle" class="text-base font-semibold mb-1" x-text="title"></h3>
        <p id="confirmMsg" class="text-sm text-gray-500 dark:text-gray-400 mb-6" x-text="message"></p>
        <div class="flex justify-end gap-3">
            <button type="button" @click="cancel()" x-ref="cancelBtn" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-400/40">Cancel</button>
            <button type="button" @click="confirm()" x-ref="confirmBtn" class="px-4 py-2 text-sm font-medium text-white rounded-xl bg-gradient-to-br from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 transition-all shadow-sm shadow-red-500/20 focus:outline-none focus:ring-2 focus:ring-red-500/50" x-text="confirmLabel"></button>
        </div>
    </div>
</div>

@once
<script>
    function confirmDialog() {
        return {
            open: false,
            title: 'Confirm Action',
            message: 'Are you sure?',
            confirmLabel: 'Delete',
            form: null,
            prevFocus: null,
            init() {
                document.addEventListener('click', (e) => {
                    const btn = e.target.closest('[data-confirm]');
                    if (!btn) return;
                    e.preventDefault();
                    const form = btn.closest('form');
                    if (!form) return;
                    this.message = btn.getAttribute('data-confirm') || 'Are you sure?';
                    this.confirmLabel = btn.getAttribute('data-confirm-button') || 'Delete';
                    this.form = form;
                    this.prevFocus = document.activeElement;
                    this.open = true;
                    this.$nextTick(() => {
                        const cancelBtn = this.$refs?.cancelBtn;
                        if (cancelBtn) setTimeout(() => cancelBtn.focus(), 100);
                    });
                });
                document.addEventListener('keydown', (e) => {
                    if (!this.open) return;
                    if (e.key === 'Escape') {
                        this.cancel();
                    }
                    if (e.key === 'Enter') {
                        this.confirm();
                    }
                    if (e.key === 'Tab') {
                        const focusable = this.$el.querySelectorAll('button');
                        const first = focusable[0];
                        const last = focusable[focusable.length - 1];
                        if (e.shiftKey && document.activeElement === first) {
                            e.preventDefault();
                            last.focus();
                        } else if (!e.shiftKey && document.activeElement === last) {
                            e.preventDefault();
                            first.focus();
                        }
                    }
                });
            },
            confirm() {
                if (this.form) {
                    const btn = this.prevFocus;
                    this.form.submit();
                }
                this.close();
            },
            cancel() {
                const btn = this.prevFocus;
                if (btn && typeof stopLoading === 'function') {
                    stopLoading(btn);
                }
                if (btn) btn.focus();
                this.close();
            },
            close() {
                this.open = false;
                this.form = null;
                document.body.style.overflow = '';
            }
        };
    }
</script>
@endonce
