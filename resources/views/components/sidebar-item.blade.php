@props([
    'icon' => null,
    'href' => '#',
    'active' => false,
])

@php
    $classes = $active
        ? 'bg-sidebar-accent text-sidebar-accent-foreground font-medium'
        : 'text-sidebar-foreground hover:bg-sidebar-accent/50 hover:text-sidebar-foreground';
@endphp

<a href="{{ $href }}" class="flex items-center gap-3 px-3 py-2 text-sm rounded-md transition-colors {{ $classes }}">
    @if ($icon)
        <div class="w-4 h-4 shrink-0">
            <x-dynamic-component :component="$icon" class="w-full h-full" />
        </div>
    @endif
    <span>{{ $slot }}</span>
</a>
