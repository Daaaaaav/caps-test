@props([
    'label',
])

<div class="space-y-1">
    @if ($label)
        <div class="px-3 text-xs font-semibold text-sidebar-foreground/50 tracking-wider uppercase mb-2">
            {{ $label }}
        </div>
    @endif
    <div class="space-y-1">
        {{ $slot }}
    </div>
</div>
