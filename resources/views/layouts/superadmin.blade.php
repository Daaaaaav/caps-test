@php
use Illuminate\Support\Facades\Auth;

$user = Auth::user();

$fullName = trim($user->full_name ?? 'User');
$parts = preg_split('/\s+/', $fullName);
$firstInitial = strtoupper(substr($parts[0] ?? 'U', 0, 1));
$lastInitial = strtoupper(substr($parts[count($parts)-1] ?? '', 0, 1));
$initials = $firstInitial . $lastInitial;

//sidebar brand
$authUser = Auth::user()?->loadMissing('company');
$brandName = $authUser?->company?->company_name ?? 'Kebun Raya Bogor';
$brandLogo = $authUser?->company?->image ?: asset('images/logo/kebun-raya-bogor.png');

$invertStyle = 'filter: brightness(0) invert(1);';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'App' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/kebun-raya-bogor.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
    @livewireStyles
</head>

<body class="min-h-screen bg-background text-foreground font-sans"
    x-data="{
        sidebarCollapsed: false,
        isMobile: window.innerWidth < 1024,
        init() {
            const handler = () => { this.isMobile = window.innerWidth < 1024; };
            window.addEventListener('resize', handler);
            this.$cleanup = () => window.removeEventListener('resize', handler);
        }
    }"
    :style="sidebarCollapsed ? '--sbw: 4.5rem' : '--sbw: 16rem'"
    :class="sidebarCollapsed ? 'sidebar-is-collapsed' : 'sidebar-is-expanded'"
>
    <flux:header class="lg:hidden bg-sidebar border-b border-sidebar-border">
        <flux:sidebar.toggle class="lg:hidden shrink-0" icon="bars-2" inset="left" />

        <div class="font-semibold text-sidebar-foreground tracking-wide font-sans truncate min-w-0 px-2">Kebun Raya Bogor</div>

        <flux:spacer />

        <flux:dropdown position="top" align="start">
            <flux:profile avatar-text="{{ strtoupper($initials) }}" class="shrink-0" />
            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio checked>{{ $fullName }}</flux:menu.radio>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.item
                    icon="arrow-right-start-on-rectangle"
                    as="button"
                    type="submit"
                    form="logout-form">
                    Logout
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{-- Form logout tersembunyi (di luar dropdown) --}}
    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>

    <div class="flex min-h-screen lg:min-h-0">
        {{-- Sidebar (always full height) --}}
        @include('livewire.components.partials.superadmin.sidebar')

        <main class="bg-background flex-1 min-w-0 overflow-y-auto animate-fade-in-up sidebar-main">
            <div class="w-full max-w-screen-xl mx-auto px-3 sm:px-5 lg:px-8
                        [&_.container]:max-w-none [&_.container]:mx-0 [&_.container]:px-0">

                {{-- Premium Top Header Bar --}}
                <header class="hidden lg:flex items-center justify-between py-4 border-b border-border/80 mb-6 select-none">
                    <div class="flex items-center gap-3">
                        {{-- Breadcrumbs Component --}}
                        @include('components.breadcrumbs')
                    </div>

                    {{-- Right side: language toggle + date badge --}}
                    <div class="flex items-center gap-3">
                        {{-- Language Toggle --}}
                        <div class="relative" x-data="{ open: false }">
                            @php $isEn = app()->getLocale() === 'en'; @endphp
                            <button @click.stop="open = !open" type="button"
                                class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 bg-secondary/80 border border-border text-foreground hover:bg-accent">
                                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                                </svg>
                                <span>{{ $isEn ? 'EN' : 'ID' }}</span>
                                <svg class="w-3 h-3 transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div x-show="open" x-transition @click.outside="open = false"
                                class="absolute right-0 top-full mt-1.5 w-36 rounded-xl bg-card border border-border shadow-xl z-[9999] overflow-hidden"
                                style="display:none;">
                                <a href="{{ route('lang.switch', 'en') }}" class="flex items-center gap-2.5 px-3 py-2.5 text-sm transition-colors {{ $isEn ? 'bg-accent font-semibold text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-foreground' }}">
                                    <span>🇬🇧</span><span>{{ __('app.english') }}</span>
                                    @if($isEn)<svg class="w-3.5 h-3.5 ml-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>@endif
                                </a>
                                <a href="{{ route('lang.switch', 'id') }}" class="flex items-center gap-2.5 px-3 py-2.5 text-sm transition-colors {{ !$isEn ? 'bg-accent font-semibold text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-foreground' }}">
                                    <span>🇮🇩</span><span>{{ __('app.indonesian') }}</span>
                                    @if(!$isEn)<svg class="w-3.5 h-3.5 ml-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>@endif
                                </a>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-muted-foreground/80 bg-secondary/80 border border-border px-3 py-1.5 rounded-xl shadow-xs">
                            {{ now()->locale(app()->getLocale())->translatedFormat('l, d M Y') }}
                        </span>
                    </div>
                </header>

                {{ $slot }}

            </div>
        </main>
    </div>


    @livewire('components.ui.toast')

    @livewireScripts
    @fluxScripts
    @vite('resources/js/app.js')
    @stack('scripts')
</body>

</html>