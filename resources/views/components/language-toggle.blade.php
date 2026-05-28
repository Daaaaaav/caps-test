@php
    $currentLocale = app()->getLocale();
    $isEn = $currentLocale === 'en';
@endphp

{{-- Language Toggle Button --}}
<div class="relative" x-data="{ open: false }">
    <button
        @click.stop="open = !open"
        type="button"
        class="{{ $class ?? 'flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:bg-white/10 border border-white/20 text-white/90 hover:text-white' }}"
        aria-label="{{ __('app.language') }}"
        title="{{ __('app.language') }}"
    >
        {{-- Globe icon --}}
        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
        </svg>
        <span>{{ $isEn ? 'EN' : 'ID' }}</span>
        <svg class="w-3 h-3 transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="6 9 12 15 18 9"/>
        </svg>
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
        @click.outside="open = false"
        class="absolute {{ $dropdownPosition ?? 'right-0 top-full mt-1.5' }} w-36 rounded-xl bg-[#2a1f1a] border border-white/10 shadow-2xl shadow-black/40 z-[9999] overflow-hidden"
        style="display: none;"
    >
        <a href="{{ route('lang.switch', 'en') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 text-sm transition-colors {{ $isEn ? 'text-white bg-white/10 font-semibold' : 'text-white/70 hover:text-white hover:bg-white/5' }}">
            <span class="text-base leading-none">🇬🇧</span>
            <span>{{ __('app.english') }}</span>
            @if($isEn)
                <svg class="w-3.5 h-3.5 ml-auto text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            @endif
        </a>
        <a href="{{ route('lang.switch', 'id') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 text-sm transition-colors {{ !$isEn ? 'text-white bg-white/10 font-semibold' : 'text-white/70 hover:text-white hover:bg-white/5' }}">
            <span class="text-base leading-none">🇮🇩</span>
            <span>{{ __('app.indonesian') }}</span>
            @if(!$isEn)
                <svg class="w-3.5 h-3.5 ml-auto text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            @endif
        </a>
    </div>
</div>
