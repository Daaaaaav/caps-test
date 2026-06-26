@props([
    'as' => 'h3',
])

<{{ $as }} {{ $attributes->class('text-lg font-semibold leading-6') }}>
    {{ $slot }}
</{{ $as }}>
