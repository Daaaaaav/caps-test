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

<body class="min-h-screen bg-background text-foreground flex font-sans"
    x-data="{ sidebarCollapsed: true }"
    :style="sidebarCollapsed ? '--sbw: 4.5rem' : '--sbw: 16rem'"
    :class="sidebarCollapsed ? 'sidebar-is-collapsed' : 'sidebar-is-expanded'"
>
    <flux:header class="lg:hidden fixed top-0 inset-x-0 z-50 bg-sidebar border-b border-sidebar-border">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <div class="font-semibold text-sidebar-foreground tracking-wide font-sans">Kebun Raya Bogor</div>

        <flux:spacer />

        <flux:dropdown position="top" align="start">
            <flux:profile avatar-text="{{ strtoupper($initials) }}" />
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

    {{-- Sidebar (always full height) --}}
    @include('livewire.components.partials.superadmin.sidebar')

    <main class="dark:bg-white flex-1 overflow-y-auto pt-14 lg:pt-0 lg:ml-[var(--sbw)] px-4 sm:px-6 lg:px-8
                [&_.container]:max-w-none [&_.container]:mx-0 [&_.container]:px-0 animate-fade-in-up">
        
        {{-- Premium Top Header Bar --}}
        <header class="hidden lg:flex items-center justify-between py-4 border-b border-border/80 mb-6 select-none">
            <div class="flex items-center gap-3">
                {{-- Breadcrumbs Component --}}
                @include('components.breadcrumbs')
            </div>

            {{-- Right side info badge --}}
            <div class="flex items-center gap-4">
                <span class="text-xs font-semibold text-muted-foreground/80 bg-secondary/80 border border-border px-3 py-1.5 rounded-xl shadow-xs">
                    {{ now()->format('l, d M Y') }}
                </span>
            </div>
        </header>

        {{ $slot }}
    </main>


    @livewire('components.ui.toast')

    @livewireScripts
    @vite('resources/js/app.js')
    @stack('scripts')
</body>

</html>