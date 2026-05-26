<flux:sidebar sticky collapsible="mobile" 
    @mouseenter="sidebarCollapsed = false"
    @mouseleave="sidebarCollapsed = true"
    class="fixed inset-y-0 left-0 z-40 bg-sidebar border-r border-sidebar-border lg:w-[var(--sbw)] w-full max-w-[19rem] overflow-y-auto overflow-x-hidden box-border shadow-2xl shadow-black/30">
    
    <!-- COLLAPSED STATE DOCK (shown when sidebarCollapsed is true) -->
    <div x-show="sidebarCollapsed" class="sidebar-collapsed-container flex flex-col h-full justify-between items-center py-2 px-1 w-full select-none">
        <!-- Logo Area -->
        <div class="h-10 flex items-center justify-center mb-3 relative group">
            <img src="{{ $brandLogo }}" alt="Brand Logo" class="h-7 w-7 object-contain rounded-lg img-white drop-shadow-[0_0_8px_rgba(205,222,167,0.4)] transition-transform duration-300 hover:rotate-12" style="{{ $invertStyle }}" />
            <div class="sidebar-tooltip">{{ $brandName }}</div>
        </div>

        <!-- Navigation Icons -->
        <nav class="flex-1 flex flex-col items-center gap-1.5 w-full">
            {{-- ===== CORE ===== --}}
            @php $dashActive = request()->routeIs('superadmin.dashboard'); @endphp
            <a href="{{ route('superadmin.dashboard') }}" class="sidebar-collapsed-item group {{ $dashActive ? 'active' : '' }}">
                @if($dashActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <div class="sidebar-tooltip">Dashboard</div>
            </a>

            <div class="sidebar-collapsed-divider"></div>

            {{-- ===== USER MANAGEMENT ===== --}}
            @php $recActive = request()->routeIs('superadmin.receptionists'); @endphp
            <a href="{{ route('superadmin.receptionists') }}" class="sidebar-collapsed-item group {{ $recActive ? 'active' : '' }}">
                @if($recActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <div class="sidebar-tooltip">Receptionists</div>
            </a>

            <div class="sidebar-collapsed-divider"></div>

            {{-- ===== ANALYTICS ===== --}}
            @php $roomActive = request()->routeIs('superadmin.room'); @endphp
            <a href="{{ route('superadmin.room') }}" class="sidebar-collapsed-item group {{ $roomActive ? 'active' : '' }}">
                @if($roomActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M10 21V11.5a1.5 1.5 0 0 1 3 0V21"/>
                    <line x1="8" y1="7" x2="16" y2="7"/>
                </svg>
                <div class="sidebar-tooltip">Room Bookings</div>
            </a>

            @php $vehActive = request()->routeIs('superadmin.vehicle'); @endphp
            <a href="{{ route('superadmin.vehicle') }}" class="sidebar-collapsed-item group {{ $vehActive ? 'active' : '' }}">
                @if($vehActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2" />
                    <circle cx="7" cy="17" r="2" fill="currentColor" fill-opacity="0.15"/>
                    <circle cx="17" cy="17" r="2" fill="currentColor" fill-opacity="0.15"/>
                </svg>
                <div class="sidebar-tooltip">Vehicle Bookings</div>
            </a>

            @php $delActive = request()->routeIs('superadmin.delivery'); @endphp
            <a href="{{ route('superadmin.delivery') }}" class="sidebar-collapsed-item group {{ $delActive ? 'active' : '' }}">
                @if($delActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z" fill="currentColor" fill-opacity="0.15"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
                <div class="sidebar-tooltip">Deliveries</div>
            </a>

            @php $guestActive = request()->routeIs('superadmin.guestbook'); @endphp
            <a href="{{ route('superadmin.guestbook') }}" class="sidebar-collapsed-item group {{ $guestActive ? 'active' : '' }}">
                @if($guestActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" fill="currentColor" fill-opacity="0.15"/>
                </svg>
                <div class="sidebar-tooltip">Guestbook</div>
            </a>

            <div class="sidebar-collapsed-divider"></div>

            {{-- ===== AI & SECURITY SYSTEM ===== --}}
            @php $lstmActive = request()->routeIs('superadmin.lstm-predictions'); @endphp
            <a href="{{ route('superadmin.lstm-predictions') }}" class="sidebar-collapsed-item group {{ $lstmActive ? 'active' : '' }}">
                @if($lstmActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="4" width="16" height="16" rx="2" fill="currentColor" fill-opacity="0.15"/>
                    <rect x="9" y="9" width="6" height="6" fill="currentColor" fill-opacity="0.2"/>
                    <line x1="9" y1="1" x2="9" y2="4"/>
                    <line x1="15" y1="1" x2="15" y2="4"/>
                    <line x1="9" y1="20" x2="9" y2="23"/>
                    <line x1="15" y1="20" x2="15" y2="23"/>
                    <line x1="20" y1="9" x2="23" y2="9"/>
                    <line x1="20" y1="15" x2="23" y2="15"/>
                    <line x1="1" y1="9" x2="4" y2="9"/>
                    <line x1="1" y1="15" x2="4" y2="15"/>
                </svg>
                <div class="sidebar-tooltip">Visitor Predictions</div>
            </a>

            @php $occActive = request()->routeIs('superadmin.occupancy'); @endphp
            <a href="{{ route('superadmin.occupancy') }}" class="sidebar-collapsed-item group {{ $occActive ? 'active' : '' }}">
                @if($occActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 3v18h18"/>
                    <path d="m19 9-5 5-4-4-3 3"/>
                </svg>
                <div class="sidebar-tooltip">Occupancy Forecast</div>
            </a>

            @php $secActive = request()->routeIs('superadmin.ai-security'); @endphp
            <a href="{{ route('superadmin.ai-security') }}" class="sidebar-collapsed-item group {{ $secActive ? 'active' : '' }}">
                @if($secActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="currentColor" fill-opacity="0.15"/>
                    <path d="m9 11 2 2 4-4"/>
                </svg>
                <div class="sidebar-tooltip">Security Reports</div>
            </a>
        </nav>

        <!-- Bottom Icons -->
        <div class="flex flex-col items-center gap-1.5 w-full mt-auto">
            @php $superSetActive = request()->routeIs('superadmin.settings'); @endphp
            <a href="{{ route('superadmin.settings') }}" class="sidebar-collapsed-item group {{ $superSetActive ? 'active' : '' }}">
                @if($superSetActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <div class="sidebar-tooltip">Settings</div>
            </a>

            @php $superHelpsActive = request()->routeIs('superadmin.help'); @endphp
            <a href="{{ route('superadmin.help') }}" class="sidebar-collapsed-item group {{ $superHelpsActive ? 'active' : '' }}">
                @if($superHelpsActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <div class="sidebar-tooltip">Help</div>
            </a>

            <div class="sidebar-collapsed-divider"></div>

            <!-- Profile Dropdown -->
            <flux:dropdown position="top" align="start" class="w-full flex justify-center">
                <button class="sidebar-collapsed-item group hover:bg-white/10 transition-colors focus:outline-none">
                    <flux:sidebar.profile avatar="" name="" class="p-0 pointer-events-none" />
                    <div class="sidebar-tooltip">{{ $fullName }}</div>
                </button>
                <flux:menu>
                    <flux:menu.radio.group>
                        <flux:menu.radio checked>{{ $fullName }}</flux:menu.radio>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.item icon="arrow-right-start-on-rectangle" as="button" type="submit" form="logout-form">
                        Logout
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    <!-- EXPANDED STATE SIDEBAR (shown when sidebarCollapsed is false) -->
    <div x-show="!sidebarCollapsed" class="flex flex-col h-full justify-between w-full">
        {{-- HEADER --}}
        <flux:sidebar.header>
            <flux:sidebar.brand 
                href="{{ route('superadmin.dashboard') }}" 
                logo="{{ $brandLogo }}" 
                name="{{ $brandName }}" 
                class="text-white"
                style="{{ $invertStyle }}" />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        {{-- NAVIGATION --}}
        <flux:sidebar.nav>
            {{-- ===== CORE ===== --}}
            <flux:sidebar.item icon="home" href="{{ route('superadmin.dashboard') }}"
                :current="request()->routeIs('superadmin.dashboard')">
                Dashboard
            </flux:sidebar.item>

            {{-- ===== USER MANAGEMENT ===== --}}
            <flux:sidebar.group expandable heading="User Management">
                <flux:sidebar.item icon="users" href="{{ route('superadmin.receptionists') }}"
                    :current="request()->routeIs('superadmin.receptionists')">
                    Receptionists
                </flux:sidebar.item>
            </flux:sidebar.group>

            {{-- ===== ANALYTICS ===== --}}
            <flux:sidebar.group expandable heading="Analytics">
                <flux:sidebar.item icon="chart-bar" href="{{ route('superadmin.room') }}"
                    :current="request()->routeIs('superadmin.room')">
                    Room Bookings
                </flux:sidebar.item>

                <flux:sidebar.item icon="truck" href="{{ route('superadmin.vehicle') }}"
                    :current="request()->routeIs('superadmin.vehicle')">
                    Vehicle Bookings
                </flux:sidebar.item>

                <flux:sidebar.item icon="cube" href="{{ route('superadmin.delivery') }}"
                    :current="request()->routeIs('superadmin.delivery')">
                    Deliveries
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open" href="{{ route('superadmin.guestbook') }}"
                    :current="request()->routeIs('superadmin.guestbook')">
                    Guestbook
                </flux:sidebar.item>
            </flux:sidebar.group>

            {{-- ===== AI & SECURITY SYSTEM ===== --}}
            <flux:sidebar.group expandable heading="AI & Security System">
                <flux:sidebar.item icon="cpu-chip" href="{{ route('superadmin.lstm-predictions') }}"
                    :current="request()->routeIs('superadmin.lstm-predictions')">
                    Visitor Predictions
                </flux:sidebar.item>

                <flux:sidebar.item icon="chart-bar-square" href="{{ route('superadmin.occupancy') }}"
                    :current="request()->routeIs('superadmin.occupancy')">
                    Occupancy Forecast
                </flux:sidebar.item>

                <flux:sidebar.item icon="shield-check" href="{{ route('superadmin.ai-security') }}"
                    :current="request()->routeIs('superadmin.ai-security')">
                    Security Reports
                </flux:sidebar.item>
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        {{-- SETTINGS + HELP --}}
        <flux:sidebar.nav>
            <flux:sidebar.item icon="cog-6-tooth" href="{{ route('superadmin.settings') }}"
                :current="request()->routeIs('superadmin.settings')">
                Settings
            </flux:sidebar.item>

            <flux:sidebar.item icon="information-circle" href="{{ route('superadmin.help') }}"
                :current="request()->routeIs('superadmin.help')">
                Help
            </flux:sidebar.item>

            {{-- Logout for MOBILE --}}
            <flux:sidebar.item
                class="lg:hidden"
                icon="arrow-right-start-on-rectangle"
                as="button"
                type="submit"
                form="logout-form">
                Logout
            </flux:sidebar.item>
        </flux:sidebar.nav>

        {{-- PROFILE --}}
        <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:sidebar.profile avatar="" name="{{ $fullName }}" />
            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio checked>{{ $fullName }}</flux:menu.radio>
                </flux:menu.radio.group>
                <flux:menu.separator />
                <flux:menu.item icon="arrow-right-start-on-rectangle" as="button" type="submit" form="logout-form">
                    Logout
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </div>
</flux:sidebar>

<form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
    @csrf
</form>
