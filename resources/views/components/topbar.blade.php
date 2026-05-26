@props([
    'title' => 'KRB',
])

<header class="lg:hidden h-14 bg-background border-b border-border flex items-center justify-between px-4 sm:px-6 shrink-0">
    <div class="flex items-center gap-3">
        <button type="button" @click="$dispatch('toggle-sidebar')" class="p-1.5 rounded-md hover:bg-muted text-foreground transition-colors" aria-label="Toggle Sidebar">
            <x-heroicon-o-bars-3 class="w-5 h-5" />
        </button>
        <span class="font-semibold text-sm tracking-tight text-foreground">{{ $title }}</span>
    </div>
</header>
