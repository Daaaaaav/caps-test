{{-- Empty State Component --}}
@props([
    'icon' => null,
    'title',
    'description' => null,
])

<div class="flex flex-col items-center justify-center text-center p-8 border border-dashed border-border rounded-lg bg-card text-card-foreground">
    @if ($icon)
        <div class="w-10 h-10 text-muted-foreground mb-4">
            <x-dynamic-component :component="$icon" class="h-full w-full" />
        </div>
    @endif
    <h3 class="text-sm font-semibold text-foreground">{{ $title }}</h3>
    @if ($description)
        <p class="text-xs text-muted-foreground mt-1 max-w-sm">{{ $description }}</p>
    @endif
</div>
