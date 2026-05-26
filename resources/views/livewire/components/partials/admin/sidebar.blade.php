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

        <flux:sidebar.search placeholder="Search..." />

        <flux:sidebar.nav>
            <flux:sidebar.item
                icon="home"
                href="{{ route('admin.dashboard') }}"
                :current="request()->routeIs('admin.dashboard')"
            >
                Home
            </flux:sidebar.item>

            <flux:sidebar.item
                icon="calendar-days"
                href="{{ route('admin.room.monitoring') }}"
                :current="request()->routeIs('admin.room.monitoring')"
            >
                Booking room
            </flux:sidebar.item>

            <flux:sidebar.item
                icon="calendar-days"
                href="{{ route('admin.information') }}"
                :current="request()->routeIs('admin.information')" 
            >
                Information
            </flux:sidebar.item>

            <flux:sidebar.item
                icon="inbox"
                href="{{ route('admin.ticket') }}"
                :current="request()->routeIs('admin.ticket')"
            >
                Ticket
            </flux:sidebar.item>

            <flux:sidebar.item
                icon="users"
                href="{{ route('admin.usermanagement') }}"
                :current="request()->routeIs('admin.usermanagement')"
            >
                User Management
            </flux:sidebar.item>

            {{-- MENU KHUSUS IT: WiFi Management --}}
            @if(auth()->user()->department && auth()->user()->department->department_name === 'IT')
                <flux:sidebar.item
                    icon="wifi" 
                    href="{{ route('admin.wifimanagement') }}"
                    :current="request()->routeIs('admin.wifimanagement')"
                >
                    WiFi Management
                </flux:sidebar.item>
            @endif

            <flux:sidebar.item
                icon="chart-bar"
                href="{{ route('admin.agentreport') }}"
                :current="request()->routeIs('admin.agentreport')"
            >
                Agent Report
            </flux:sidebar.item>
        </flux:sidebar.nav>

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

        {{-- Profil + menu (desktop) --}}
        <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:sidebar.profile avatar="" name="{{ $fullName }}" />

            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio checked>{{ $fullName }}</flux:menu.radio>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.item
                    icon="arrow-right-start-on-rectangle"
                    as="button"
                    type="submit"
                    form="logout-form"
                >
                    Logout
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </div>
</flux:sidebar>

<form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
    @csrf
</form>