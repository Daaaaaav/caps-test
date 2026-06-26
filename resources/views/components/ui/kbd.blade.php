@props(['tag' => 'kbd'])

<{{ $tag }} {{ $attributes->class('inline-flex items-center rounded border border-current/20 bg-slate-100 px-1.5 py-0.5 text-[0.7rem] font-medium uppercase tracking-[0.18em] text-slate-700 dark:bg-slate-900 dark:text-slate-200') }}>
    {{ $slot }}
</{{ $tag }}>
