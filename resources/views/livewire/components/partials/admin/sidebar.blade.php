<flux:sidebar
    sticky
    collapsible="mobile"
    @mouseenter="sidebarCollapsed = false"
    @mouseleave="sidebarCollapsed = true"
    class="
        fixed top-0 left-0 h-screen
        bg-sidebar
        border-r border-sidebar-border
        lg:w-[var(--sbw)] w-[85vw] max-w-sm
        z-40
        overflow-y-auto overflow-x-hidden
    "
>
    <!-- COLLAPSED STATE DOCK (shown when sidebarCollapsed is true) -->
    <div x-show="sidebarCollapsed" class="sidebar-collapsed-container flex flex-col h-full justify-between items-center py-2 px-1 w-full select-none">
        <!-- Logo Area -->
        <div class="h-10 flex items-center justify-center mb-3 relative group">
            <img src="{{ $brandLogo }}" alt="Brand Logo" class="h-7 w-7 object-contain rounded-lg img-white drop-shadow-[0_0_8px_rgba(205,222,167,0.4)] transition-transform duration-300 hover:rotate-12" style="{{ $invertStyle }}" />
            <div class="sidebar-tooltip">{{ $brandName }}</div>
        </div>

        <!-- Navigation Icons -->
        <nav class="flex-1 flex flex-col items-center gap-1.5 w-full">
            {{-- ------- Home ------- --}}
            @php $homeActive = request()->routeIs('admin.dashboard'); @endphp
            <a href="{{ route('admin.dashboard') }}" class="sidebar-collapsed-item group {{ $homeActive ? 'active' : '' }}">
                @if($homeActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <div class="sidebar-tooltip">Home</div>
            </a>

            <div class="sidebar-collapsed-divider"></div>

            {{-- ------- Booking room ------- --}}
            @php $roomActive = request()->routeIs('admin.room.monitoring'); @endphp
            <a href="{{ route('admin.room.monitoring') }}" class="sidebar-collapsed-item group {{ $roomActive ? 'active' : '' }}">
                @if($roomActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                    <path d="M9 14h6v4H9z" fill="currentColor" fill-opacity="0.15"/>
                </svg>
                <div class="sidebar-tooltip">Booking room</div>
            </a>

            {{-- ------- Information ------- --}}
            @php $infoActive = request()->routeIs('admin.information'); @endphp
            <a href="{{ route('admin.information') }}" class="sidebar-collapsed-item group {{ $infoActive ? 'active' : '' }}">
                @if($infoActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8a3 3 0 0 0-3-3H5a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V8z" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M22 6l-4 4v4l4 4V6z"/>
                </svg>
                <div class="sidebar-tooltip">Information</div>
            </a>

            {{-- ------- Ticket ------- --}}
            @php $ticketActive = request()->routeIs('admin.ticket'); @endphp
            <a href="{{ route('admin.ticket') }}" class="sidebar-collapsed-item group {{ $ticketActive ? 'active' : '' }}">
                @if($ticketActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2" fill="currentColor" fill-opacity="0.15"/>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
                <div class="sidebar-tooltip">Ticket</div>
            </a>

            {{-- ------- User Management ------- --}}
            @php $userActive = request()->routeIs('admin.usermanagement'); @endphp
            <a href="{{ route('admin.usermanagement') }}" class="sidebar-collapsed-item group {{ $userActive ? 'active' : '' }}">
                @if($userActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <div class="sidebar-tooltip">User Management</div>
            </a>

            {{-- ------- WiFi Management ------- --}}
            @if(auth()->user()->department && auth()->user()->department->department_name === 'IT')
                @php $wifiActive = request()->routeIs('admin.wifimanagement'); @endphp
                <a href="{{ route('admin.wifimanagement') }}" class="sidebar-collapsed-item group {{ $wifiActive ? 'active' : '' }}">
                    @if($wifiActive)<div class="active-dot-indicator"></div>@endif
                    <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12.55a11 11 0 0 1 14.08 0"/>
                        <path d="M1.42 9a16 16 0 0 1 21.16 0"/>
                        <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
                        <line x1="12" y1="20" x2="12.01" y2="20" stroke-width="3"/>
                    </svg>
                    <div class="sidebar-tooltip">WiFi Management</div>
                </a>
            @endif

            {{-- ------- Agent Report ------- --}}
            @php $reportActive = request()->routeIs('admin.agentreport'); @endphp
            <a href="{{ route('admin.agentreport') }}" class="sidebar-collapsed-item group {{ $reportActive ? 'active' : '' }}">
                @if($reportActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 3v18h18"/>
                    <path d="m19 9-5 5-4-4-3 3"/>
                </svg>
                <div class="sidebar-tooltip">Agent Report</div>
            </a>
        </nav>

        <!-- Bottom Icons -->
        <div class="flex flex-col items-center gap-1.5 w-full mt-auto">
            <a href="#" class="sidebar-collapsed-item group">
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <div class="sidebar-tooltip">Settings</div>
            </a>

            <a href="#" class="sidebar-collapsed-item group">
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <div class="sidebar-tooltip">Help</div>
            </a>

            <div class="sidebar-collapsed-divider"></div>

            <!-- Profile Dropdown (collapsed) -->
            <div x-data="{ open: false }" class="relative w-full flex justify-center">
                <button
                    @click.stop="open = !open"
                    class="sidebar-collapsed-item group hover:bg-white/10 transition-colors focus:outline-none"
                >
                    <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold text-white">
                        {{ strtoupper(substr($fullName, 0, 1)) }}
                    </div>
                    <div class="sidebar-tooltip">{{ $fullName }}</div>
                </button>

                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-1"
                    @click.outside="open = false"
                    class="absolute bottom-full left-0 mb-2 w-48 rounded-xl bg-[#2a1f1a] border border-white/10 shadow-2xl shadow-black/40 z-[9999] overflow-hidden"
                    style="display: none;"
                >
                    <div class="px-3 py-2.5 border-b border-white/10">
                        <p class="text-xs font-semibold text-white truncate">{{ $fullName }}</p>
                        <p class="text-[10px] text-white/40 mt-0.5">Admin</p>
                    </div>
                    <button
                        type="submit"
                        form="logout-form"
                        class="w-full flex items-center gap-2.5 px-3 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition-colors"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- EXPANDED STATE SIDEBAR (shown when sidebarCollapsed is false, or always on mobile) -->
    <div x-show="!sidebarCollapsed || window.innerWidth < 1024" class="flex flex-col h-full justify-between w-full">
        {{-- Logo + Brand + Collapse (mobile) --}}
        <flux:sidebar.header>
            <flux:sidebar.brand
                href="#"
                logo="{{ $brandLogo }}"
                name="{{ $brandName }}"
                class="text-white"
                style="{{ $invertStyle }}" />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        {{-- Search Input --}}
        <div x-data="{ search: '' }" class="px-3 pt-2 pb-1">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/40 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                    x-model="search"
                    type="text"
                    placeholder="Search..."
                    class="w-full h-9 pl-9 pr-3 rounded-lg bg-white/10 border border-white/10 text-sm text-white placeholder-white/40 focus:outline-none focus:ring-1 focus:ring-white/20"
                />
            </div>

            <flux:sidebar.nav>
                <flux:sidebar.item
                    icon="home"
                    href="{{ route('admin.dashboard') }}"
                    :current="request()->routeIs('admin.dashboard')"
                    x-show="!search || 'home'.includes(search.toLowerCase())"
                >
                    Home
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="calendar-days"
                    href="{{ route('admin.room.monitoring') }}"
                    :current="request()->routeIs('admin.room.monitoring')"
                    x-show="!search || 'booking room'.includes(search.toLowerCase())"
                >
                    Booking room
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="calendar-days"
                    href="{{ route('admin.information') }}"
                    :current="request()->routeIs('admin.information')"
                    x-show="!search || 'information'.includes(search.toLowerCase())"
                >
                    Information
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="inbox"
                    href="{{ route('admin.ticket') }}"
                    :current="request()->routeIs('admin.ticket')"
                    x-show="!search || 'ticket'.includes(search.toLowerCase())"
                >
                    Ticket
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="users"
                    href="{{ route('admin.usermanagement') }}"
                    :current="request()->routeIs('admin.usermanagement')"
                    x-show="!search || 'user management'.includes(search.toLowerCase())"
                >
                    User Management
                </flux:sidebar.item>

                {{-- MENU KHUSUS IT: WiFi Management --}}
                @if(auth()->user()->department && auth()->user()->department->department_name === 'IT')
                    <flux:sidebar.item
                        icon="wifi"
                        href="{{ route('admin.wifimanagement') }}"
                        :current="request()->routeIs('admin.wifimanagement')"
                        x-show="!search || 'wifi management'.includes(search.toLowerCase())"
                    >
                        WiFi Management
                    </flux:sidebar.item>
                @endif

                <flux:sidebar.item
                    icon="chart-bar"
                    href="{{ route('admin.agentreport') }}"
                    :current="request()->routeIs('admin.agentreport')"
                    x-show="!search || 'agent report'.includes(search.toLowerCase())"
                >
                    Agent Report
                </flux:sidebar.item>
            </flux:sidebar.nav>
        </div>

        <flux:sidebar.spacer />

        <flux:sidebar.nav>
            <flux:sidebar.item icon="cog-6-tooth" href="#">Settings</flux:sidebar.item>
            <flux:sidebar.item icon="information-circle" href="#">Help</flux:sidebar.item>

            <flux:sidebar.item
                class="lg:hidden"
                icon="arrow-right-start-on-rectangle"
                as="button"
                type="submit"
                form="logout-form"
            >
                Logout
            </flux:sidebar.item>
        </flux:sidebar.nav>

        {{-- Profil + menu (desktop, expanded) --}}
        <div x-data="{ open: false }" class="relative max-lg:hidden px-2 pb-2">
            <button
                @click.stop="open = !open"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/8 transition-colors group focus:outline-none"
            >
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-bold text-white shrink-0">
                    {{ strtoupper(substr($fullName, 0, 1)) }}
                </div>
                <div class="flex-1 text-left min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ $fullName }}</p>
                    <p class="text-xs text-white/40">Admin</p>
                </div>
                <svg class="w-4 h-4 text-white/40 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="18 15 12 9 6 15"/>
                </svg>
            </button>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-1"
                @click.outside="open = false"
                class="absolute bottom-full left-2 right-2 mb-1 rounded-xl bg-[#2a1f1a] border border-white/10 shadow-2xl shadow-black/40 z-[9999] overflow-hidden"
                style="display: none;"
            >
                <div class="px-3 py-2.5 border-b border-white/10">
                    <p class="text-xs font-semibold text-white truncate">{{ $fullName }}</p>
                    <p class="text-[10px] text-white/40 mt-0.5">Admin</p>
                </div>
                <button
                    type="submit"
                    form="logout-form"
                    class="w-full flex items-center gap-2.5 px-3 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition-colors"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Logout
                </button>
            </div>
        </div>
    </div>
</flux:sidebar>

{{-- logout-form is defined in the parent layout --}}