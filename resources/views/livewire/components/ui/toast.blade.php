<div x-data="{
        toasts: [],
        addToast(t) {
            t.id = crypto.randomUUID ? crypto.randomUUID() : Date.now() + Math.random();
            t.type = t.type || 'info';
            t.message = t.message || '';
            t.title = t.title || '';
            t.duration = Number(t.duration ?? 3500);
            this.toasts.push(t);
            if (t.duration > 0) {
                setTimeout(() => this.removeToast(t.id), t.duration);
            }
        },
        removeToast(id) {
            this.toasts = this.toasts.filter(tt => tt.id !== id);
        },
        // Minimalist black/white card + gentle shadow
        getToastClasses(type) {
            const base =
            'relative overflow-hidden rounded-xl p-4 border border-black/15 bg-white/95 text-black shadow-[0_10px_30px_-12px_rgba(0,0,0,0.35)] backdrop-blur-sm transition-all duration-500 ease-out';
            // Small variations per type (all grayscale)
            const variants = {
                success: 'border-black/20',
                error:   'border-black/25',
                warning: 'border-black/20',
                info:    'border-black/15',
                neutral: 'border-black/15'
            };
            return base + ' ' + (variants[type] || variants.info);
        },
        // Thin top accent line in grayscale
        getAccentClasses(type) {
            const variants = {
                success: 'bg-gradient-to-r from-black/90 to-black/70',
                error:   'bg-gradient-to-r from-black/90 to-black/70',
                warning: 'bg-gradient-to-r from-black/90 to-black/70',
                info:    'bg-gradient-to-r from-black/90 to-black/70',
                neutral: 'bg-gradient-to-r from-black/90 to-black/70'
            };
            return variants[type] || variants.info;
        },
        // Solid monochrome icon puck
        getIconClasses(type) {
            const base =
            'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg';
            const variants = {
                success: 'bg-black text-white',
                error:   'bg-black text-white',
                warning: 'bg-black text-white',
                info:    'bg-black text-white',
                neutral: 'bg-black text-white'
            };
            return base + ' ' + (variants[type] || variants.info);
        },
        // Keep simple glyphs
        getIcon(type) {
            const icons = { success: '✓', error: '✕', warning: '⚠', info: 'ⓘ', neutral: '•' };
            return icons[type] || icons.info;
        }
    }" x-on:toast.window="addToast($event.detail)"
    class="fixed top-6 right-6 z-50 flex flex-col gap-4 w-[calc(100vw-3rem)] max-w-sm pointer-events-none"
    aria-live="polite">

    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-x-full opacity-0 scale-95"
            x-transition:enter-end="translate-x-0 opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0 opacity-100 scale-100"
            x-transition:leave-end="translate-x-full opacity-0 scale-95" class="pointer-events-auto"
            :class="getToastClasses(toast.type)">
            <div class="absolute top-0 left-0 right-0 h-1" :class="getAccentClasses(toast.type)"></div>
            <div class="absolute top-0 left-0 right-0 h-1 opacity-50 animate-pulse"
                :class="getAccentClasses(toast.type)"></div>
            <div class="flex items-start gap-4">
                <div :class="getIconClasses(toast.type)">
                    <span x-text="getIcon(toast.type)"></span>
                </div>
                <div class="flex-1 min-w-0 pt-1">
                    <h4 x-show="toast.title" x-text="toast.title"
                        class="font-semibold text-base mb-1 leading-tight tracking-tight"></h4>
                    <p x-show="toast.message" x-text="toast.message" class="text-sm leading-relaxed text-black/70"></p>
                </div>
                <button @click="removeToast(toast.id)"
                    class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-black/60 hover:text-black hover:bg-black/5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-black/20"
                    aria-label="Close notification">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>