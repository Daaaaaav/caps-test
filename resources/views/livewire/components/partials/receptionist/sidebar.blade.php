<flux:sidebar
    sticky
    collapsible="mobile"
    @mouseenter="sidebarCollapsed = false"
    @mouseleave="sidebarCollapsed = true"
    class="bg-sidebar border-r border-sidebar-border lg:w-[var(--sbw)] overflow-y-auto overflow-x-hidden box-border shadow-2xl shadow-black/30 z-50"
>
    <!-- COLLAPSED STATE DOCK (shown when sidebarCollapsed is true, desktop only) -->
    <div x-show="sidebarCollapsed" class="sidebar-collapsed-container max-lg:hidden flex flex-col h-full justify-between items-center py-2 px-1 w-full select-none">
        <!-- Logo Area -->
        <div class="h-10 flex items-center justify-center mb-3 relative group">
            <img src="{{ $brandLogo }}" alt="Brand Logo" class="h-7 w-7 object-contain rounded-lg img-white drop-shadow-[0_0_8px_rgba(205,222,167,0.4)] transition-transform duration-300 hover:rotate-12" style="{{ $invertStyle }}" />
            <div class="sidebar-tooltip">{{ $brandName }}</div>
        </div>

        <!-- Navigation Icons -->
        <nav class="flex-1 flex flex-col items-center gap-1.5 w-full">
            {{-- ------- Home ------- --}}
            @php $homeActive = request()->routeIs('receptionist.dashboard'); @endphp
            <a href="{{ route('receptionist.dashboard') }}" class="sidebar-collapsed-item group {{ $homeActive ? 'active' : '' }}">
                @if($homeActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <div class="sidebar-tooltip">Home</div>
            </a>

            <div class="sidebar-collapsed-divider"></div>

            {{-- ------- Room Management ------- --}}
            @php $schedActive = request()->routeIs('receptionist.schedule'); @endphp
            <a href="{{ route('receptionist.schedule') }}" class="sidebar-collapsed-item group {{ $schedActive ? 'active' : '' }}">
                @if($schedActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                    <path d="M9 14h6v4H9z" fill="currentColor" fill-opacity="0.15"/>
                </svg>
                <div class="sidebar-tooltip">Booking Room</div>
            </a>

            @php $bookActive = request()->routeIs('receptionist.bookings'); @endphp
            <a href="{{ route('receptionist.bookings') }}" class="sidebar-collapsed-item group {{ $bookActive ? 'active' : '' }}">
                @if($bookActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <div class="sidebar-tooltip">Room Book Approval</div>
            </a>

            @php $histActive = request()->routeIs('receptionist.bookinghistory'); @endphp
            <a href="{{ route('receptionist.bookinghistory') }}" class="sidebar-collapsed-item group {{ $histActive ? 'active' : '' }}">
                @if($histActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <polyline points="3 3 3 8 8 8"/>
                    <line x1="12" y1="7" x2="12" y2="12"/>
                    <line x1="12" y1="12" x2="16" y2="14"/>
                </svg>
                <div class="sidebar-tooltip"> Room Book History</div>
            </a>

            <div class="sidebar-collapsed-divider"></div>

            {{-- ------- Vehicle Management ------- --}}
            @php $vbookActive = request()->routeIs('receptionist.bookingvehicle'); @endphp
            <a href="{{ route('receptionist.bookingvehicle') }}" class="sidebar-collapsed-item group {{ $vbookActive ? 'active' : '' }}">
                @if($vbookActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2" />
                    <circle cx="7" cy="17" r="2" fill="currentColor" fill-opacity="0.15"/>
                    <circle cx="17" cy="17" r="2" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M18 5h4" stroke-width="2.5"/>
                    <path d="M20 3v4" stroke-width="2.5"/>
                </svg>
                <div class="sidebar-tooltip">Book Vehicle</div>
            </a>

            @php $vstatActive = request()->routeIs('receptionist.vehiclestatus'); @endphp
            <a href="{{ route('receptionist.vehiclestatus') }}" class="sidebar-collapsed-item group {{ $vstatActive ? 'active' : '' }}">
                @if($vstatActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2" />
                    <circle cx="7" cy="17" r="2"/>
                    <circle cx="17" cy="17" r="2"/>
                    <path d="M12 2a4 4 0 0 1 4 4v1H8V6a4 4 0 0 1 4-4z" fill="currentColor" fill-opacity="0.15"/>
                </svg>
                <div class="sidebar-tooltip">Vehicle Status</div>
            </a>

            @php $vhistActive = request()->routeIs('receptionist.vehicleshistory'); @endphp
            <a href="{{ route('receptionist.vehicleshistory') }}" class="sidebar-collapsed-item group {{ $vhistActive ? 'active' : '' }}">
                @if($vhistActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2" />
                    <circle cx="7" cy="17" r="2"/>
                    <circle cx="17" cy="17" r="2"/>
                    <path d="M12 6c-2 0-3.5 1.5-3.5 3.5H7l2.5 3 2.5-3h-1.5c0-1 1-2 2-2s2 1 2 2-1 2-2 2v1.5c2 0 3.5-1.5 3.5-3.5S14 6 12 6z"/>
                </svg>
                <div class="sidebar-tooltip">Vehicle History</div>
            </a>

            <div class="sidebar-collapsed-divider"></div>

            {{-- ------- Guest Management ------- --}}
            @php $gbookActive = request()->routeIs('receptionist.guestbook'); @endphp
            <a href="{{ route('receptionist.guestbook') }}" class="sidebar-collapsed-item group {{ $gbookActive ? 'active' : '' }}">
                @if($gbookActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" fill="currentColor" fill-opacity="0.15"/>
                </svg>
                <div class="sidebar-tooltip">GuestBook</div>
            </a>

            @php $ghistActive = request()->routeIs('receptionist.guestbookhistory'); @endphp
            <a href="{{ route('receptionist.guestbookhistory') }}" class="sidebar-collapsed-item group {{ $ghistActive ? 'active' : '' }}">
                @if($ghistActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M12 6v4l2.5 2.5"/>
                </svg>
                <div class="sidebar-tooltip">GuestBook History</div>
            </a>

            @php $gstatActive = request()->routeIs('receptionist.guestbookstatus'); @endphp
            <a href="{{ route('receptionist.guestbookstatus') }}" class="sidebar-collapsed-item group {{ $gstatActive ? 'active' : '' }}">
                @if($gstatActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1" fill="currentColor" fill-opacity="0.15"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <path d="M14 14h3v3m0 4v-4h4" stroke-linecap="round"/>
                </svg>
                <div class="sidebar-tooltip">GuestBook Status</div>
            </a>

            <div class="sidebar-collapsed-divider"></div>

            {{-- ------- DocPac Management ------- --}}
            @php $dformActive = request()->routeIs('receptionist.docpackform'); @endphp
            <a href="{{ route('receptionist.docpackform') }}" class="sidebar-collapsed-item group {{ $dformActive ? 'active' : '' }}">
                @if($dformActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z" fill="currentColor" fill-opacity="0.15"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
                <div class="sidebar-tooltip">DocPac Form</div>
            </a>

            @php $dstatActive = request()->routeIs('receptionist.docpackstatus'); @endphp
            <a href="{{ route('receptionist.docpackstatus') }}" class="sidebar-collapsed-item group {{ $dstatActive ? 'active' : '' }}">
                @if($dstatActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                    <circle cx="12" cy="7" r="2.5" fill="#CDDEA7"/>
                </svg>
                <div class="sidebar-tooltip">DocPac Status</div>
            </a>

            @php $dhistActive = request()->routeIs('receptionist.docpackhistory'); @endphp
            <a href="{{ route('receptionist.docpackhistory') }}" class="sidebar-collapsed-item group {{ $dhistActive ? 'active' : '' }}">
                @if($dhistActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                    <path d="M16 19a4 4 0 0 1-8 0" stroke="#CDDEA7"/>
                </svg>
                <div class="sidebar-tooltip">DocPac History</div>
            </a>
        </nav>

        <!-- Bottom Icons -->
        <div class="flex flex-col items-center gap-1.5 w-full mt-auto">
            @php $setActive = request()->routeIs('receptionist.settings'); @endphp
            <a href="{{ route('receptionist.settings') }}" class="sidebar-collapsed-item group {{ $setActive ? 'active' : '' }}">
                @if($setActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <div class="sidebar-tooltip">Settings</div>
            </a>

            @php $helpActive = request()->routeIs('receptionist.help'); @endphp
            <a href="{{ route('receptionist.help') }}" class="sidebar-collapsed-item group {{ $helpActive ? 'active' : '' }}">
                @if($helpActive)<div class="active-dot-indicator"></div>@endif
                <svg class="w-5.5 h-5.5 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" fill="currentColor" fill-opacity="0.15"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <div class="sidebar-tooltip">Help</div>
            </a>

            <div class="sidebar-collapsed-divider"></div>

            <!-- Profile / Logout Dropdown (collapsed) -->
            <div x-data="{ open: false }" class="relative w-full flex justify-center">
                <button
                    @click.stop="open = !open"
                    class="sidebar-collapsed-item group hover:bg-white/10 transition-colors focus:outline-none"
                >
                    <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold text-white">
                        {{ strtoupper(substr($fullName ?? 'U', 0, 1)) }}
                    </div>
                    <div class="sidebar-tooltip">{{ $fullName ?? 'User' }}</div>
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
                        <p class="text-xs font-semibold text-white truncate">{{ $fullName ?? 'User' }}</p>
                        <p class="text-[10px] text-white/40 mt-0.5">Receptionist</p>
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
                        {{ __('app.logout') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- EXPANDED STATE SIDEBAR (shown when sidebarCollapsed is false, or always on mobile) -->
    <div x-show="!sidebarCollapsed || window.innerWidth < 1024" class="flex flex-col h-full justify-between w-full">
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
                    placeholder="{{ __('app.search_modules') }}"
                    class="w-full h-9 pl-9 pr-3 rounded-lg bg-white/10 border border-white/10 text-sm text-white placeholder-white/40 focus:outline-none focus:ring-1 focus:ring-white/20"
                />
            </div>

            <flux:sidebar.nav>
                {{-- ------- Home ------- --}}
                <flux:sidebar.item
                    icon="home"
                    href="{{ route('receptionist.dashboard') }}"
                    :current="request()->routeIs('receptionist.dashboard')"
                    x-show="!search || 'home'.includes(search.toLowerCase())"
                >
                    {{ __('app.home') }}
                </flux:sidebar.item>

                {{-- ------- Room Management ------- --}}
                <flux:sidebar.group expandable heading="{{ __('app.room_management') }}" class="grid"
                    x-show="!search || ['room management','booking room','room book approval','booking history'].some(s => s.includes(search.toLowerCase()))">
                    <flux:sidebar.item
                        icon="calendar-days"
                        href="{{ route('receptionist.schedule') }}"
                        :current="request()->routeIs('receptionist.schedule')"
                        x-show="!search || 'booking room'.includes(search.toLowerCase())"
                    >
                        {{ __('app.booking_room') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="check-circle"
                        href="{{ route('receptionist.bookings') }}"
                        :current="request()->routeIs('receptionist.bookings')"
                        x-show="!search || 'room book approval'.includes(search.toLowerCase())"
                    >
                        {{ __('app.room_book_approval') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="clock"
                        href="{{ route('receptionist.bookinghistory') }}"
                        :current="request()->routeIs('receptionist.bookinghistory')"
                        x-show="!search || 'booking history'.includes(search.toLowerCase())"
                    >
                        {{ __('app.booking_history') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- ------- Vehicle Management ------- --}}
                <flux:sidebar.group expandable heading="{{ __('app.vehicle_management') }}" class="grid"
                    x-show="!search || ['vehicle management','book vehicle','vehicle status','vehicle history'].some(s => s.includes(search.toLowerCase()))">
                    <flux:sidebar.item
                        icon="truck"
                        href="{{ route('receptionist.bookingvehicle') }}"
                        :current="request()->routeIs('receptionist.bookingvehicle')"
                        x-show="!search || 'book vehicle'.includes(search.toLowerCase())"
                    >
                        {{ __('app.vehicle_book') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="truck"
                        href="{{ route('receptionist.vehiclestatus') }}"
                        :current="request()->routeIs('receptionist.vehiclestatus')"
                        x-show="!search || 'vehicle status'.includes(search.toLowerCase())"
                    >
                        {{ __('app.vehicle_status_menu') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="clock"
                        href="{{ route('receptionist.vehicleshistory') }}"
                        :current="request()->routeIs('receptionist.vehicleshistory')"
                        x-show="!search || 'vehicle history'.includes(search.toLowerCase())"
                    >
                        {{ __('app.vehicle_history') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- ------- Guest Management ------- --}}
                <flux:sidebar.group expandable heading="{{ __('app.guest_management') }}" class="grid"
                    x-show="!search || ['guest management','guestbook','guestbook status','guestbook history'].some(s => s.includes(search.toLowerCase()))">
                    <flux:sidebar.item
                        icon="inbox"
                        href="{{ route('receptionist.guestbook') }}"
                        :current="request()->routeIs('receptionist.guestbook')"
                        x-show="!search || 'guestbook'.includes(search.toLowerCase())"
                    >
                        {{ __('app.guestbook') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="qr-code"
                        href="{{ route('receptionist.guestbookstatus') }}"
                        :current="request()->routeIs('receptionist.guestbookstatus')"
                        x-show="!search || 'guestbook status'.includes(search.toLowerCase())"
                    >
                        {{ __('app.guestbook_status') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="clock"
                        href="{{ route('receptionist.guestbookhistory') }}"
                        :current="request()->routeIs('receptionist.guestbookhistory')"
                        x-show="!search || 'guestbook history'.includes(search.toLowerCase())"
                    >
                        {{ __('app.guestbook_history') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- ------- DocPac Management ------- --}}
                <flux:sidebar.group expandable heading="{{ __('app.docpac_management') }}" class="grid"
                    x-show="!search || ['docpac management','docpac form','docpac status','docpac history'].some(s => s.includes(search.toLowerCase()))">
                    <flux:sidebar.item
                        icon="gift"
                        href="{{ route('receptionist.docpackform') }}"
                        :current="request()->routeIs('receptionist.docpackform')"
                        x-show="!search || 'docpac form'.includes(search.toLowerCase())"
                    >
                        {{ __('app.docpac_form') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="clock"
                        href="{{ route('receptionist.docpackstatus') }}"
                        :current="request()->routeIs('receptionist.docpackstatus')"
                        x-show="!search || 'docpac status'.includes(search.toLowerCase())"
                    >
                        {{ __('app.docpac_status') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="clock"
                        href="{{ route('receptionist.docpackhistory') }}"
                        :current="request()->routeIs('receptionist.docpackhistory')"
                        x-show="!search || 'docpac history'.includes(search.toLowerCase())"
                    >
                        {{ __('app.docpac_history') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>
        </div>

        <flux:sidebar.spacer />

        {{-- SETTINGS + MOBILE LOGOUT --}}
        <flux:sidebar.nav>
            <flux:sidebar.item
                icon="cog-6-tooth"
                href="{{ route('receptionist.settings') }}"
                :current="request()->routeIs('receptionist.settings')"
            >
                {{ __('app.settings') }}
            </flux:sidebar.item>

            <flux:sidebar.item
                icon="information-circle"
                href="{{ route('receptionist.help') }}"
                :current="request()->routeIs('receptionist.help')"
            >
                {{ __('app.help') }}
            </flux:sidebar.item>

            {{-- Logout for MOBILE uses shared form="logout-form" --}}
            <flux:sidebar.item
                class="lg:hidden"
                icon="arrow-right-start-on-rectangle"
                as="button"
                type="submit"
                form="logout-form"
            >
                {{ __('app.logout') }}
            </flux:sidebar.item>
        </flux:sidebar.nav>

        {{-- DESKTOP DROPDOWN (expanded) --}}
        <div x-data="{ open: false }" class="relative max-lg:hidden px-2 pb-2">
            <button
                @click.stop="open = !open"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/8 transition-colors group focus:outline-none"
            >
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-bold text-white shrink-0">
                    {{ strtoupper(substr($fullName ?? 'U', 0, 1)) }}
                </div>
                <div class="flex-1 text-left min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ $fullName ?? 'User' }}</p>
                    <p class="text-xs text-white/40">Receptionist</p>
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
                    <p class="text-xs font-semibold text-white truncate">{{ $fullName ?? 'User' }}</p>
                    <p class="text-[10px] text-white/40 mt-0.5">Receptionist</p>
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
                    {{ __('app.logout') }}
                </button>
            </div>
        </div>
    </div>
</flux:sidebar>

<style>
    .img-white {
        filter: brightness(0) invert(1);
    }
</style>
