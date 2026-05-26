{{-- Stat Card Component --}}
@props([
    'label',
    'value',
    'icon' => null,
    'description' => null,
])

<div class="rounded-lg border border-border bg-card p-6 shadow-sm text-card-foreground">
    <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-muted-foreground">{{ $label }}</span>
        @if ($icon)
            <div class="h-4 w-4 text-muted-foreground">
                <x-dynamic-component :component="$icon" class="h-full w-full" />
            </div>
        @endif
    </div>
    <div class="mt-2 flex items-baseline gap-1">
        <span class="text-2xl font-bold tracking-tight">{{ $value }}</span>
    </div>
    @if ($description)
        <p class="mt-1 text-xs text-muted-foreground">{{ $description }}</p>
    @endif
</div>
