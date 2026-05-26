{{-- Page Header Component --}}
@props([
    'title',
    'subtitle' => null,
])

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2">
    <div>
        <h1 class="text-xl font-semibold tracking-tight text-foreground">{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-sm text-muted-foreground mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @if (isset($actions))
        <div class="flex items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
