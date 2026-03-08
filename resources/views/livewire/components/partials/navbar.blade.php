<div>
    <style>
        .dropdown-menu { display: none; opacity: 0; transform: translateY(-10px); transition: opacity .2s ease, transform .2s ease; }
        .dropdown-menu.show { display: block; opacity: 1; transform: translateY(0); }
        .mobile-menu { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-in-out; }
        .mobile-menu.open { max-height: 100vh; }
        .mobile-dropdown-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
        .mobile-dropdown-content.open { max-height: 500px; }
        .profile-icon-bnw { filter: grayscale(100%); }
        .logo-full-white { filter: brightness(0) invert(1); }
        
        /* A simple comment like an actual programmer's simple documentation */
        /* HIDES THE BADGE IF ITS TEXT CONTENT IS EXACTLY 0 */
        span.bg-red-600:is([data-count="0"]) {
            display: none !important;
        }
    </style>

    {{-- A simple comment like an actual programmer's simple documentation --}}
    {{-- FIXED NAVBAR --}}
    <nav class="bg-black border-b border-gray-800 fixed inset-x-0 top-0 z-50 shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                {{-- Logo --}}
                <div class="flex-shrink-0">
                    @php
                    $company = auth()->user()?->company;
                    $rawLogo = $company?->image;
                    $fallback = asset('images/logo/kebun-raya-bogor.png');
                    $logoUrl = $fallback;

                    if (!empty($rawLogo)) {
                        if (preg_match('#^https?://#i', $rawLogo)) {
                            $logoUrl = $rawLogo;
                        } else {
                            $paths = [public_path($rawLogo), public_path('storage/'.$rawLogo), public_path('images/'.$rawLogo)];
                            foreach ($paths as $path) {
                                if (file_exists($path)) {
                                    $logoUrl = asset(str_replace(public_path() . '/', '', $path));
                                    break;
                                }
                            }
                        }
                    }
                    @endphp
                    <a href="{{ route('home') }}" class="transition-transform hover:scale-105">
                        <img src="{{ $logoUrl }}" alt="{{ $company?->company_name ?? 'KRBS' }} Logo" class="h-10 w-auto logo-full-white">
                    </a>
                </div>

                {{-- Desktop Navigation --}}
                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('user.home') }}" class="px-3 py-2 text-sm font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 {{ request()->routeIs('home') ? 'bg-gray-800 text-white' : '' }}">Home</a>
                    <a href="{{ route('create-ticket') }}" class="px-3 py-2 text-sm font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 {{ request()->routeIs('create-ticket') ? 'bg-gray-800 text-white' : '' }}">Create Ticket</a>
                    <a href="{{ route('book-room') }}" class="px-3 py-2 text-sm font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 {{ request()->routeIs('book-room') ? 'bg-gray-800 text-white' : '' }}">Book Room</a>
                    <a href="{{ route('book-vehicle') }}" class="px-3 py-2 text-sm font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 {{ request()->routeIs('book-vehicle') ? 'bg-gray-800 text-white' : '' }}">Book Vehicle</a>

                    @if(auth()->user() && auth()->user()->is_agent == 'yes')
                    <a href="{{ route('user.ticket.queue') }}" class="px-3 py-2 text-sm font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 {{ request()->routeIs('user.ticket.queue') ? 'bg-gray-800 text-white' : '' }} flex items-center gap-1">
                        <x-heroicon-o-queue-list class="w-4 h-4" /> 
                        Queue
                        {{-- COUNT FOR UNCLAIMED TICKETS --}}
                        @if ($unclaimedTicketCount > 0)
                            <span class="ml-1 px-1.5 py-0.5 text-xs font-bold text-white bg-red-600 rounded-full leading-none">{{ $unclaimedTicketCount }}</span>
                        @endif
                    </a>
                    @endif

                    {{-- Status Dropdown (with Unread Comment Count) --}}
                    @if(Auth::check())
                    
                    <div class="relative" data-exclusive-dropdown>
                        <button type="button" data-dropdown-toggle class="px-3 py-2 text-sm font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 flex items-center gap-1" aria-haspopup="true" aria-expanded="false">
                            <x-heroicon-o-chart-bar class="w-4 h-4" /> 
                            Status
                            {{-- COUNT FOR UNREAD COMMENTS (Total) - FORCED RENDER --}}
                            <span class="ml-1 px-1.5 py-0.5 text-xs font-bold text-white bg-red-600 rounded-full leading-none" data-count="{{ $totalUnreadCount }}">{{ $totalUnreadCount }}</span>
                            <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform" data-dropdown-arrow />
                        </button>
                        <div data-dropdown-menu class="dropdown-menu absolute right-0 mt-2 w-52 bg-gray-900 rounded-lg shadow-xl border border-gray-700 py-1 z-50">
                            <a href="{{ route('ticketstatus') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 transition-colors">
                                <x-heroicon-o-ticket class="w-4 h-4" /> Ticket Status
                                {{-- COUNT FOR UNREAD COMMENTS (Inside Dropdown) - FORCED RENDER --}}
                                <span class="ml-auto px-1.5 py-0.5 text-xs font-bold text-white bg-red-600 rounded-full leading-none " data-count="{{ $totalUnreadCount }}">{{ $totalUnreadCount }}</span>
                            </a>
                            <a href="{{ route('bookingstatus') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 transition-colors"><x-heroicon-o-calendar class="w-4 h-4" /> Meeting Status</a>
                            <a href="{{ route('vehiclestatus') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 transition-colors"><x-heroicon-o-truck class="w-4 h-4" /> Vehicle Status</a>
                        </div>
                    </div>
                    @else
                    <a href="{{ route('ticketstatus') }}" class="px-3 py-2 text-sm font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 {{ request()->routeIs('ticketstatus') ? 'bg-gray-800 text-white' : '' }}">Status</a>
                    @endif

                    @guest
                    <a href="{{ route('login') }}" class="ml-2 bg-white text-black px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-200 transition-colors flex items-center gap-2">
                        <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" /> Login
                    </a>
                    @endguest

                    @auth
                    {{-- Profile Dropdown --}}
                    <div class="relative ml-2" data-exclusive-dropdown>
                        <button type="button" data-dropdown-toggle class="flex items-center gap-2 px-3 py-1 text-sm font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 transition-colors" aria-haspopup="true" aria-expanded="false">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-xs profile-icon-bnw">
                                {{ strtoupper(substr(auth()->user()->full_name ?? auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <span class="hidden lg:block">{{ explode(' ', auth()->user()->full_name ?? auth()->user()->name ?? 'User')[0] }}</span>
                            <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform" data-dropdown-arrow />
                        </button>
                        <div data-dropdown-menu class="dropdown-menu absolute right-0 mt-2 w-64 bg-gray-900 rounded-lg shadow-xl border border-gray-700 py-2 z-50">
                            <div class="px-4 py-3 border-b border-gray-800">
                                <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->full_name ?? auth()->user()->name ?? 'User' }}</p>
                                <p class="text-xs text-gray-400 mt-0.5 truncate">{{ auth()->user()->email }}</p>
                                @if(auth()->user()->is_agent == 'yes')
                                <span class="inline-flex items-center gap-1.5 mt-2 px-2 py-0.5 bg-blue-600 rounded-full text-xs font-medium text-white">
                                    <x-heroicon-s-check-badge class="w-3 h-3" /> Agent
                                </span>
                                @endif
                            </div>
                            <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-gray-800 transition-colors">
                                <x-heroicon-o-user-circle class="w-5 h-5" /> My Profile
                            </a>
                            
                            {{-- MODIFIED DASHBOARD LINKS LOGIC --}}
                            @php $role = auth()->user()->role->name; @endphp

                            {{-- 1. Superadmin Dashboard (Only Superadmin) --}}
                            @if($role === 'Superadmin')
                            <a href="{{ route('superadmin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-gray-800 transition-colors">
                                <x-heroicon-o-shield-check class="w-5 h-5" /> Superadmin DB
                            </a>
                            @endif

                            {{-- 2. Admin Dashboard (Admin OR Superadmin) --}}
                            @if(in_array($role, ['Superadmin', 'Admin']))
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-gray-800 transition-colors">
                                <x-heroicon-o-computer-desktop class="w-5 h-5" /> Admin DB
                            </a>
                            @endif

                            {{-- 3. Receptionist Dashboard (Receptionist OR Superadmin) --}}
                            @if(in_array($role, ['Superadmin', 'Receptionist']))
                            <a href="{{ route('receptionist.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-300 hover:text-white hover:bg-gray-800 transition-colors">
                                <x-heroicon-o-clipboard-document-list class="w-5 h-5" /> Receptionist DB
                            </a>
                            @endif
                            {{-- END MODIFIED LOGIC --}}

                            <div class="border-t border-gray-800 my-2"></div>
                            <form method="POST" action="{{ route('logout') }}" class="px-2">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-2 py-2.5 text-sm text-white hover:text-red-300 hover:bg-gray-800 rounded-md transition-colors">
                                    <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5" /> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                    @endauth
                </div>

                {{-- Mobile Hamburger --}}
                <button id="hamburger" class="md:hidden p-2 rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 transition-all focus:outline-none" aria-label="Toggle navigation" aria-expanded="false" aria-controls="mobile-menu">
                    <div class="w-6 h-6 flex flex-col justify-center space-y-1.5 transition-transform duration-300" data-hamburger-icon>
                        <span class="block w-6 h-0.5 bg-current rounded-full transition-transform duration-300 ease-in-out origin-center"></span>
                        <span class="block w-6 h-0.5 bg-current rounded-full transition-opacity duration-300 ease-in-out"></span>
                        <span class="block w-6 h-0.5 bg-current rounded-full transition-transform duration-300 ease-in-out origin-center"></span>
                    </div>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="md:hidden mobile-menu border-t border-gray-800 bg-black">
            <div class="px-4 py-4 space-y-1">
                @auth
                <div class="mb-4 p-3 bg-gray-900 rounded-lg border border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold profile-icon-bnw">
                            {{ strtoupper(substr(auth()->user()->full_name ?? auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->full_name ?? auth()->user()->name ?? 'User' }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    @if(auth()->user()->is_agent == 'yes')
                    <div class="mt-2">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-blue-600 rounded-full text-xs font-medium text-white">
                            <x-heroicon-s-check-badge class="w-3 h-3" /> Agent
                        </span>
                    </div>
                    @endif
                </div>
                @endauth

                {{-- Mobile Nav Links --}}
                <a href="{{ route('user.home') }}" class="flex items-center gap-3 px-4 py-3 text-base font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 {{ request()->routeIs('home') ? 'bg-gray-800 text-white' : '' }}"><x-heroicon-o-home class="w-5 h-5" /> Home</a>
                <a href="{{ route('create-ticket') }}" class="flex items-center gap-3 px-4 py-3 text-base font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 {{ request()->routeIs('create-ticket') ? 'bg-gray-800 text-white' : '' }}"><x-heroicon-o-ticket class="w-5 h-5" /> Create Ticket</a>
                <a href="{{ route('book-room') }}" class="flex items-center gap-3 px-4 py-3 text-base font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 {{ request()->routeIs('book-room') ? 'bg-gray-800 text-white' : '' }}"><x-heroicon-o-building-office class="w-5 h-5" /> Book Room</a>
                <a href="{{ route('book-vehicle') }}" class="flex items-center gap-3 px-4 py-3 text-base font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 {{ request()->routeIs('book-vehicle') ? 'bg-gray-800 text-white' : '' }}"><x-heroicon-o-truck class="w-5 h-5" /> Book Vehicle</a>

                @if(auth()->user() && auth()->user()->is_agent == 'yes')
                <a href="{{ route('user.ticket.queue') }}" class="flex items-center gap-3 px-4 py-3 text-base font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 {{ request()->routeIs('user.ticket.queue') ? 'bg-gray-800 text-white' : '' }}">
                    <x-heroicon-o-queue-list class="w-5 h-5" /> 
                    Ticket Queue
                    {{-- COUNT FOR UNCLAIMED TICKETS (Mobile) --}}
                    @if ($unclaimedTicketCount > 0)
                        <span class="ml-auto px-1.5 py-0.5 text-xs font-bold text-white bg-red-600 rounded-full leading-none ">{{ $unclaimedTicketCount }}</span>
                    @endif
                </a>
                @endif

                {{-- Mobile Status Dropdown (with Unread Count) --}}
                @if(Auth::check())
                
                <div data-mobile-dropdown>
                    <button type="button" data-mobile-toggle class="w-full flex items-center justify-between px-4 py-3 text-base font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 transition-colors">
                        <span class="flex items-center gap-3">
                            <x-heroicon-o-chart-bar class="w-5 h-5" /> Status
                            {{-- COUNT FOR UNREAD COMMENTS (Mobile) - FORCED RENDER --}}
                            <span class="ml-1 px-1.5 py-0.5 text-xs font-bold text-white bg-red-600 rounded-full leading-none" data-count="{{ $totalUnreadCount }}">{{ $totalUnreadCount }}</span>
                        </span>
                        <x-heroicon-o-chevron-down data-mobile-arrow class="w-5 h-5 transition-transform" />
                    </button>
                    <div data-mobile-content class="mobile-dropdown-content pl-4">
                        <a href="{{ route('ticketstatus') }}" class="flex items-center justify-between px-4 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-gray-800/50 rounded-lg transition-colors">
                            <span class="flex items-center gap-3"><x-heroicon-o-ticket class="w-4 h-4" /> Ticket Status</span>
                            {{-- COUNT FOR UNREAD COMMENTS (Inside Dropdown Mobile) - FORCED RENDER --}}
                            <span class="px-1.5 py-0.5 text-xs font-bold text-white bg-red-600 rounded-full leading-none" data-count="{{ $totalUnreadCount }}">{{ $totalUnreadCount }}</span>
                        </a>
                        <a href="{{ route('bookingstatus') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-gray-800/50 rounded-lg transition-colors"><x-heroicon-o-calendar class="w-4 h-4" /> Meeting Status</a>
                        <a href="{{ route('vehiclestatus') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-gray-800/50 rounded-lg transition-colors"><x-heroicon-o-truck class="w-4 h-4" /> Vehicle Status</a>
                    </div>
                </div>
                @else
                <a href="{{ route('ticketstatus') }}" class="flex items-center gap-3 px-4 py-3 text-base font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50"><x-heroicon-o-chart-bar class="w-5 h-5" /> Status</a>
                @endif

                @auth
                <div class="border-t border-gray-800 pt-3 mt-3 space-y-1">
                    <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-3 text-base font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 transition-colors"><x-heroicon-o-user-circle class="w-5 h-5" /> My Profile</a>
                    
                    {{-- MODIFIED MOBILE DASHBOARD LINKS LOGIC --}}
                    @php $role = auth()->user()->role->name; @endphp

                    @if($role === 'Superadmin')
                    <a href="{{ route('superadmin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-base font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 transition-colors">
                        <x-heroicon-o-shield-check class="w-5 h-5" /> Superadmin DB
                    </a>
                    @endif

                    @if(in_array($role, ['Superadmin', 'Admin']))
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-base font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 transition-colors">
                        <x-heroicon-o-computer-desktop class="w-5 h-5" /> Admin DB
                    </a>
                    @endif

                    @if(in_array($role, ['Superadmin', 'Receptionist']))
                    <a href="{{ route('receptionist.dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-base font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 transition-colors">
                        <x-heroicon-o-clipboard-document-list class="w-5 h-5" /> Receptionist DB
                    </a>
                    @endif
                    {{-- END MODIFIED MOBILE LOGIC --}}

                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-base font-medium rounded-lg text-white hover:text-red-300 hover:bg-gray-800/50 transition-colors"><x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5" /> Logout</button>
                    </form>
                </div>
                @endauth

                @guest
                <div class="border-t border-gray-800 pt-3 mt-3">
                    <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 w-full px-4 py-3 text-base font-semibold text-black bg-white rounded-lg hover:bg-gray-200 transition-all"><x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" /> Login / Register</a>
                </div>
                @endguest
            </div>
        </div>
    </nav>

    {{-- Spacer --}}
    <div class="h-16"></div>

    {{-- Script remains unchanged --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            console.log('Livewire Navbar Component Initialized/Updated.');
        });
        
        document.addEventListener('DOMContentLoaded', () => {
            const exclusiveDropdowns = document.querySelectorAll('[data-exclusive-dropdown]');
            const mobileDropdown = document.querySelector('[data-mobile-dropdown]');
            const mobileContent = mobileDropdown ? mobileDropdown.querySelector('[data-mobile-content]') : null;
            const mobileArrow = mobileDropdown ? mobileDropdown.querySelector('[data-mobile-arrow]') : null;
            
            const mobileMenu = document.getElementById('mobile-menu');
            const hamburger = document.getElementById('hamburger');
            const hamburgerIcon = document.querySelector('[data-hamburger-icon]');

            function toggleExclusiveDropdown(targetDropdown) {
                const menu = targetDropdown.querySelector('[data-dropdown-menu]');
                const arrow = targetDropdown.querySelector('[data-dropdown-arrow]');
                const isOpen = menu.classList.contains('show');
                
                // --- CONSOLE DEBUGGING: Check badge presence on click ---
                const toggle = targetDropdown.querySelector('[data-dropdown-toggle]');
                if (toggle.innerText.includes('Status')) {
                    console.log('--- Status Dropdown Clicked ---');
                    const linkCount = targetDropdown.querySelector('a[href*="ticketstatus"] span.bg-red-600');
                    // Check the rendered HTML value directly
                    console.log('Ticket Status Link Badge Value:', linkCount ? linkCount.innerText.trim() : 'N/A (Badge not rendered)');
                }
                // --------------------------------------------------------

                exclusiveDropdowns.forEach(dropdown => {
                    if (dropdown !== targetDropdown) {
                        dropdown.querySelector('[data-dropdown-menu]').classList.remove('show');
                        dropdown.querySelector('[data-dropdown-toggle]').setAttribute('aria-expanded', 'false');
                        const otherArrow = dropdown.querySelector('[data-dropdown-arrow]');
                        if (otherArrow) otherArrow.style.transform = 'rotate(0deg)';
                    }
                });

                if (isOpen) {
                    menu.classList.remove('show');
                    targetDropdown.querySelector('[data-dropdown-toggle]').setAttribute('aria-expanded', 'false');
                    if (arrow) arrow.style.transform = 'rotate(0deg)';
                } else {
                    menu.classList.add('show');
                    targetDropdown.querySelector('[data-dropdown-toggle]').setAttribute('aria-expanded', 'true');
                    if (arrow) arrow.style.transform = 'rotate(180deg)';
                }
            }

            exclusiveDropdowns.forEach(dropdown => {
                dropdown.querySelector('[data-dropdown-toggle]').addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleExclusiveDropdown(dropdown);
                });
            });

            document.addEventListener('click', (e) => {
                exclusiveDropdowns.forEach(dropdown => {
                    const menu = dropdown.querySelector('[data-dropdown-menu]');
                    const toggle = dropdown.querySelector('[data-dropdown-toggle]');
                    if (menu.classList.contains('show') && !toggle.contains(e.target) && !menu.contains(e.target)) {
                        toggleExclusiveDropdown(dropdown);
                    }
                });
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    exclusiveDropdowns.forEach(dropdown => {
                        if (dropdown.querySelector('[data-dropdown-menu]').classList.contains('show')) {
                            toggleExclusiveDropdown(dropdown);
                        }
                    });
                }
            });

            hamburger.addEventListener('click', () => {
                const isOpen = mobileMenu.classList.contains('open');
                mobileMenu.classList.toggle('open');
                
                const lines = hamburgerIcon.children;
                if (!isOpen) {
                    lines[0].style.transform = 'rotate(45deg) translate(6px, 6px)';
                    lines[1].style.opacity = '0';
                    lines[2].style.transform = 'rotate(-45deg) translate(6px, -6px)';
                    hamburger.setAttribute('aria-expanded', 'true');
                } else {
                    lines[0].style.transform = 'none';
                    lines[1].style.opacity = '1';
                    lines[2].style.transform = 'none';
                    hamburger.setAttribute('aria-expanded', 'false');
                }
            });

            if (mobileDropdown) {
                mobileDropdown.querySelector('[data-mobile-toggle]').addEventListener('click', () => {
                    const isOpen = mobileContent.classList.contains('open');
                    mobileContent.classList.toggle('open');
                    if (mobileArrow) {
                        mobileArrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
                    }
                });
            }

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) {
                    if (mobileMenu.classList.contains('open')) {
                        mobileMenu.classList.remove('open');
                        hamburger.click(); 
                    }
                    exclusiveDropdowns.forEach(dropdown => {
                        if (dropdown.querySelector('[data-dropdown-menu]').classList.contains('show')) {
                            toggleExclusiveDropdown(dropdown);
                        }
                    });
                }
            });
        });
    </script>
</div>