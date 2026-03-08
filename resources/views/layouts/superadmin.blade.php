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
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'App' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/kebun-raya-bogor.png') }}" />
    @vite('resources/css/app.css')
    @livewireStyles
    @fluxAppearance
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen bg-white flex">
    <flux:header class="lg:hidden fixed top-0 inset-x-0 z-50 bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <div class="font-medium text-white">Kebun Raya Bogor</div>

        <flux:spacer />

        <flux:dropdown position="top" align="start">
            <flux:profile avatar-text="{{ strtoupper($initials) }}" />
            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio checked>{{ $fullName }}</flux:menu.radio>
                    <flux:sidebar.item
                        icon="user"
                        href="{{ route('user.home') }}"
                        class="cursor-pointer">
                        User Page
                    </flux:sidebar.item>
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
                [&_.container]:max-w-none [&_.container]:mx-0 [&_.container]:px-0">
        {{ $slot }}
    </main>


    @livewire('components.ui.toast')

    @livewireScripts
    @vite('resources/js/app.js')
    @fluxScripts
</body>

</html>