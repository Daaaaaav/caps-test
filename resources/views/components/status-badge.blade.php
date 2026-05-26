{{-- Status Badge Component --}}
@props([
    'status',
])

@php
    $class = match($status) {
        'active', 'approved', 'success' => 'bg-success/10 text-success border-success/20',
        'pending', 'warning' => 'bg-warning/10 text-warning border-warning/20',
        'rejected', 'deleted', 'destructive' => 'bg-destructive/10 text-destructive border-destructive/20',
        default => 'bg-muted text-muted-foreground border-border',
    };
@endphp

<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-md border {{ $class }}">
    {{ ucfirst($status) }}
</span>
